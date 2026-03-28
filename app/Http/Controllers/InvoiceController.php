<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Services\InvoiceService;
// use App\Http\Requests\StoreSalesInvoiceRequest; // ✅ Form Request منفصل
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    /**
     * عرض صفحة إنشاء فاتورة - محسّن
     */
    public function create()
    {
        // ✅ جلب البيانات الضرورية فقط
        $products = Product::with(['sellingUnits' => function($query) {
                $query->where('is_active', true)
                      ->select('id', 'product_id', 'unit_name', 'unit_code', 
                              'conversion_factor', 'is_default', 'display_order')
                      ->orderBy('is_default', 'desc');
            }])
            ->where('is_active', true)
            ->select('id', 'name', 'sku', 'base_unit_label', 'purchase_price')
            ->orderBy('name')
            ->get();

        $customers = Customer::where('is_active', true)
            ->select('id', 'name', 'phone', 'balance', 'credit_limit')
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return view('invoices.sales.create', compact('products', 'customers', 'warehouses'));
    }

    /**
     * حفظ الفاتورة - كل اللوجيك في الـ Service
     */
    public function store( $request)
    {
        try {
            $invoice = $this->invoiceService->createSalesInvoice(
                $request->validated()
            );

            return redirect()
                ->route('invoices.sales.show', $invoice->id)
                ->with('success', '✅ تم إنشاء الفاتورة بنجاح - ' . $invoice->invoice_number);

        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '❌ ' . $e->getMessage());
                
        } catch (\Exception $e) {
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '❌ حدث خطأ غير متوقع، يرجى المحاولة مرة أخرى');
        }
    }

    /**
     * عرض الفاتورة
     */
    public function show($id)
    {
        $invoice = SalesInvoice::with([
            'customer:id,name,phone',
            'warehouse:id,name',
            'items.product:id,name,sku',
            'items.sellingUnit:id,unit_name,unit_code',
        ])->findOrFail($id);

        $details = $this->invoiceService->calculateInvoiceDetails($invoice);

        return view('invoices.sales.show', compact('invoice', 'details'));
    }

    /**
     * قائمة الفواتير - محسّنة
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $invoices = $this->invoiceService->getSalesInvoicesWithFilters($request);
        $stats = $this->invoiceService->getSalesStatisticsWithFilters($request);

        return view('invoices.sales.index', compact('invoices', 'stats'));
    }
}