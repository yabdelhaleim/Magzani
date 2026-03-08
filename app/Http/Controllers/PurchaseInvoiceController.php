<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseInvoiceRequest;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\PurchaseInvoiceService;
use Illuminate\Http\Request;
use App\Exports\PurchaseInvoiceDetailsExport;
use App\Exports\PurchaseInvoicesExport;

class PurchaseInvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(PurchaseInvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * عرض قائمة فواتير الشراء
     */
    public function index(Request $request)
    {
        $invoices = $this->invoiceService->getInvoices($request->all());
        $statistics = $this->invoiceService->getStatistics();

        return view('invoices.purchases.index', compact('invoices', 'statistics'));
    }

    /**
     * عرض صفحة إنشاء فاتورة جديدة
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::where('is_active', 1)->get();
        
        // ✅ جلب المنتجات مع البيانات الكاملة (مثل المبيعات)
        $products = Product::active()
            ->with([
                'activeSellingUnits' => function($q) {
                    $q->ordered();
                },
                'warehouses' => function($q) {
                    $q->where('warehouses.is_active', true);
                },
                'baseunit'
            ])
            ->get();

        return view('invoices.purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    /**
     * حفظ فاتورة شراء جديدة
     */
    public function store(PurchaseInvoiceRequest $request)
    {
        try {
            Log::info('Purchase invoice store: validation passed', ['items_count' => count($request->input('items', []))]);
            $invoice = $this->invoiceService->create($request->validated());

            return redirect()
                ->route('invoices.purchases.show', $invoice->id)
                ->with('success', 'تم إنشاء فاتورة الشراء بنجاح');

        } catch (\Exception $e) {
            Log::error('Purchase invoice store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل فاتورة شراء
     */
    public function show(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'warehouse', 'items.product']);

        return view('invoices.purchases.show', compact('invoice'));
    }

    /**
     * عرض صفحة تعديل فاتورة شراء
     */
    public function edit(PurchaseInvoice $invoice)
    {
        $invoice->load('items');
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $products = Product::all();

        return view('invoices.purchases.edit', compact('invoice', 'suppliers', 'warehouses', 'products'));
    }

    /**
     * تحديث فاتورة شراء
     */
    public function update(PurchaseInvoiceRequest $request, PurchaseInvoice $invoice)
    {
        try {
            $this->invoiceService->update($invoice, $request->validated());

            return redirect()
                ->route('invoices.purchases.show', $invoice->id)
                ->with('success', 'تم تحديث فاتورة الشراء بنجاح');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * حذف فاتورة شراء
     */
    public function destroy(PurchaseInvoice $invoice)
    {
        try {
            $this->invoiceService->delete($invoice);

            return redirect()
                ->route('invoices.purchases.index')
                ->with('success', 'تم حذف فاتورة الشراء بنجاح');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'حدث خطأ أثناء حذف الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * طباعة فاتورة شراء
     */
    public function print(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'warehouse', 'items.product']);
        
        return view('invoices.Print', compact('invoice'));
    }

    /**
     * تصدير كل الفواتير لـ Excel
     */
    public function export(Request $request)
    {
        return \Excel::download(
            new \App\Exports\PurchaseInvoicesExport($request->all()),
            'purchase-invoices-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * تصدير فاتورة واحدة بالتفاصيل لـ Excel
     */
    public function exportSingle(PurchaseInvoice $invoice)
    {
        return \Excel::download(
            new \App\Exports\PurchaseInvoiceDetailsExport($invoice),
            'invoice-' . $invoice->invoice_number . '.xlsx'
        );
    }
}