<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseReturnRequest;
use App\Models\PurchaseReturn;
use App\Models\PurchaseInvoice;
use App\Services\PurchaseReturnService;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
{
    protected $returnService;

    public function __construct(PurchaseReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * عرض قائمة مرتجعات الشراء
     */
    public function index(Request $request)
    {
        $returns = $this->returnService->getReturns($request->all());
        
        $statistics = [
            'total_returns' => \App\Models\PurchaseReturn::count(),
            'total_amount' => \App\Models\PurchaseReturn::sum('total'),
            'pending_returns' => \App\Models\PurchaseReturn::where('status', 'pending')->count(),
            'today_returns' => \App\Models\PurchaseReturn::whereDate('return_date', today())->count(),
        ];

        return view('invoices.purchase-returns.index', compact('returns', 'statistics'));
    }

    /**
     * عرض صفحة إنشاء مرتجع جديد
     */
    public function create(Request $request)
    {
        // إذا تم تمرير invoice_id في الـ URL
        $invoiceId = $request->get('invoice_id');
        $invoice = null;
        $availableItems = [];

        if ($invoiceId) {
            $invoice = PurchaseInvoice::with(['supplier', 'warehouse'])->findOrFail($invoiceId);
            $availableItems = $this->returnService->getAvailableItemsForReturn($invoiceId);
        }

        // قائمة الفواتير المتاحة للإرجاع
        $invoices = PurchaseInvoice::with('supplier')->get();

        return view('invoices.purchase-returns.index', compact('invoices', 'invoice', 'availableItems'));
    }

    /**
     * حفظ مرتجع شراء جديد
     */
    public function store(PurchaseReturnRequest $request)
    {
        try {
            $return = $this->returnService->create($request->validated());

            return redirect()
                ->route('invoices.purchase-returns.show', $return->id)
                ->with('success', 'تم إنشاء مرتجع الشراء بنجاح');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المرتجع: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل مرتجع شراء
     */
    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchaseInvoice.supplier',
            'purchaseInvoice.warehouse',
            'items.purchaseInvoiceItem.product'
        ]);

        return view('invoices.purchase-returns.index', compact('purchaseReturn'));
    }

    /**
     * عرض صفحة تعديل مرتجع شراء
     */
    public function edit(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['items', 'purchaseInvoice']);
        
        $invoice = $purchaseReturn->purchaseInvoice;
        $availableItems = $this->returnService->getAvailableItemsForReturn($invoice->id);
        
        // إضافة الأصناف المرتجعة حالياً للقائمة
        foreach ($purchaseReturn->items as $returnItem) {
            $found = false;
            foreach ($availableItems as &$availableItem) {
                if ($availableItem['purchase_invoice_item_id'] == $returnItem->purchase_invoice_item_id) {
                    $availableItem['available_qty'] += $returnItem->quantity_returned;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $availableItems[] = [
                    'purchase_invoice_item_id' => $returnItem->purchase_invoice_item_id,
                    'product' => $returnItem->purchaseInvoiceItem->product,
                    'original_qty' => $returnItem->purchaseInvoiceItem->qty,
                    'returned_qty' => 0,
                    'available_qty' => $returnItem->quantity_returned,
                    'unit_price' => $returnItem->unit_price,
                ];
            }
        }

        return view('invoices.purchase-returns.index', compact('purchaseReturn', 'invoice', 'availableItems'));
    }

    /**
     * تحديث مرتجع شراء
     */
    public function update(PurchaseReturnRequest $request, PurchaseReturn $purchaseReturn)
    {
        try {
            $this->returnService->update($purchaseReturn, $request->validated());

            return redirect()
                ->route('invoices.purchase-returns.show', $purchaseReturn->id)
                ->with('success', 'تم تحديث مرتجع الشراء بنجاح');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المرتجع: ' . $e->getMessage());
        }
    }

    /**
     * حذف مرتجع شراء
     */
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        try {
            $this->returnService->delete($purchaseReturn);

            return redirect()
                ->route('invoices.purchase-returns.index')
                ->with('success', 'تم حذف مرتجع الشراء بنجاح');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'حدث خطأ أثناء حذف المرتجع: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: الحصول على الأصناف المتاحة للإرجاع من فاتورة
     */
    public function getAvailableItems($invoiceId)
    {
        try {
            $availableItems = $this->returnService->getAvailableItemsForReturn($invoiceId);
            
            return response()->json([
                'success' => true,
                'items' => $availableItems
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}