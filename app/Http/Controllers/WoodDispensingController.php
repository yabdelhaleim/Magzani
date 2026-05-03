<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWoodDispensingRequest;
use App\Models\WoodDispensing;
use App\Models\WoodStock;
use App\Models\Customer;
use App\Models\ManufacturingOrder;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\WoodStockService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WoodDispensingController extends Controller
{
    public function __construct(
        private WoodStockService $woodStockService
    ) {}

    public function index()
    {
        $dispensings = WoodDispensing::with(['user', 'client', 'manufacturingOrder', 'woodStock', 'salesInvoice'])
            ->latest()
            ->paginate(20);

        return view('manufacturing.wood-dispensings.index', compact('dispensings'));
    }

    public function create(WoodStock $woodStock)
    {
        $customers = Customer::where('is_active', true)->get();
        $orders = ManufacturingOrder::latest()->limit(50)->get();

        return view('manufacturing.wood-dispensings.create', compact('woodStock', 'customers', 'orders'));
    }

    public function store(StoreWoodDispensingRequest $request)
    {
        try {
            $woodStock = WoodStock::findOrFail($request->wood_stock_id);
            $this->woodStockService->dispense($woodStock, $request->validated());

            return redirect()
                ->route('manufacturing.wood-dispensings.index')
                ->with('success', 'تم صرف الكمية بنجاح');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'خطأ أثناء الصرف: ' . $e->getMessage());
        }
    }

    /**
     * Create invoice from wood dispensing
     */
    public function createInvoice(WoodDispensing $dispensing)
    {
        if (!$dispensing->client_id) {
            return back()->with('error', 'لا يمكن إنشاء فاتورة: الصرف غير مرتبط بعميل');
        }

        if ($dispensing->sales_invoice_id) {
            return back()->with('error', 'تم إنشاء فاتورة لهذا الصرف مسبقاً');
        }

        // Create invoice
        $invoice = SalesInvoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_id' => $dispensing->client_id,
            'warehouse_id' => $dispensing->woodStock->warehouse_id ?? null,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'subtotal' => $dispensing->woodStock->unit_cost * ($dispensing->volume_cm3_taken / 1000000),
            'total' => $dispensing->woodStock->unit_cost * ($dispensing->volume_cm3_taken / 1000000),
            'created_by' => auth()->id(),
        ]);

        // Create invoice item
        $volumeM3 = $dispensing->volume_cm3_taken / 1000000;
        SalesInvoiceItem::create([
            'sales_invoice_id' => $invoice->id,
            'product_id' => $dispensing->woodStock->product_id,
            'quantity' => $volumeM3,
            'unit_price' => $dispensing->woodStock->unit_cost,
            'total' => $volumeM3 * $dispensing->woodStock->unit_cost,
            'description' => "خشب خام - {$dispensing->woodStock->length_cm}×{$dispensing->woodStock->width_cm}×{$dispensing->woodStock->thickness_cm} سم - {$volumeM3} م³",
        ]);

        // Link dispensing to invoice
        $dispensing->update(['sales_invoice_id' => $invoice->id]);

        return redirect()
            ->route('invoices.sales.show', $invoice->id)
            ->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $lastInvoice = SalesInvoice::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/INV-' . $year . '-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('INV-%s-%04d', $year, $newNumber);
    }
}
