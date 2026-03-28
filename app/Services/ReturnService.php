<?php

namespace App\Services;

use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use RuntimeException;

class ReturnService
{
    public function __construct(
        private ProductService $productService,
        private CustomerService $customerService,
        private SupplierService $supplierService
    ) {}

    /* ===================== SALES RETURNS - QUERIES ===================== */

    /**
     * جلب مرتجعات المبيعات مع الفلاتر
     */
    public function getSalesReturnsWithFilters(Request $request)
    {
        $query = SalesReturn::with(['salesInvoice.customer', 'items.product']);

        if ($request->filled('return_number')) {
            $query->where('return_number', 'like', '%' . $request->return_number . '%');
        }

        if ($request->filled('sales_invoice_id')) {
            $query->where('sales_invoice_id', $request->sales_invoice_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        $returns = $query->latest('return_date')->paginate(20);

        foreach ($returns as $return) {
            $return->calculated_details = $this->calculateReturnDetails($return);
        }

        return $returns;
    }

    /**
     * حساب إحصائيات مرتجعات المبيعات
     */
    public function getSalesReturnsStatistics(Request $request): array
    {
        $baseQuery = SalesReturn::query();

        if ($request->filled('return_number')) {
            $baseQuery->where('return_number', 'like', '%' . $request->return_number . '%');
        }

        if ($request->filled('sales_invoice_id')) {
            $baseQuery->where('sales_invoice_id', $request->sales_invoice_id);
        }

        if ($request->filled('date_from')) {
            $baseQuery->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->whereDate('return_date', '<=', $request->date_to);
        }

        $allReturns = (clone $baseQuery)->with('items')->get();

        $stats = [
            'total_count' => $allReturns->count(),
            'today_count' => $allReturns->where('return_date', '>=', today())->count(),
            'total_amount' => 0,
            'total_items' => 0,
        ];

        foreach ($allReturns as $return) {
            $details = $this->calculateReturnDetails($return);
            $stats['total_amount'] += $details['total'];
            $stats['total_items'] += $details['items_count'];
        }

        $stats['total_amount'] = round($stats['total_amount'], 2);

        return $stats;
    }

    /**
     * حساب تفاصيل المرتجع
     */
    public function calculateReturnDetails(SalesReturn $return): array
    {
        $total = 0;
        $itemsCount = 0;

        foreach ($return->items as $item) {
            // استخدم unit_price بدلاً من price
            $itemTotal = $item->quantity_returned * $item->unit_price;
            $total += $itemTotal;
            $itemsCount++;
        }

        return [
            'total' => round($total, 2),
            'items_count' => $itemsCount,
            'return_number' => $return->return_number,
            'return_date' => $return->return_date,
        ];
    }

    /* ===================== CREATE SALES RETURN ===================== */

    /**
     * إنشاء مرتجع مبيعات
     */
    public function createSalesReturn(array $data): SalesReturn
    {
        return DB::transaction(function () use ($data) {

            // جلب الفاتورة مع التأمين
            $invoice = SalesInvoice::with(['items', 'returns.items', 'customer'])
                ->lockForUpdate()
                ->findOrFail($data['sales_invoice_id']);

            // التحقق من صحة الفاتورة
            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('لا يمكن إرجاع أصناف من فاتورة ملغاة');
            }

            // التحقق من الكميات المرتجعة
            $this->validateReturnQuantities(
                $invoice->items,
                $invoice->returns,
                $data['items']
            );

            // حساب الإجمالي
            $total = $this->calculateTotal($data['items']);

            // إنشاء المرتجع
            $return = SalesReturn::create([
                'sales_invoice_id' => $invoice->id,
                'customer_id'      => $invoice->customer_id,
                'warehouse_id'     => $invoice->warehouse_id,
                'return_number'    => $this->generateReturnNumber('sales'),
                'return_date'      => $data['return_date'] ?? now(),
                'subtotal'         => $total,
                'discount_amount'  => 0,
                'tax_amount'       => 0,
                'total'            => $total,
                'status'           => 'confirmed',
                'return_reason'    => $data['notes'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            // إضافة الأصناف المرتجعة
            foreach ($data['items'] as $item) {
                // البحث عن sales_invoice_item_id
                $invoiceItem = $invoice->items->firstWhere('product_id', $item['product_id']);
                
                if (!$invoiceItem) {
                    throw new RuntimeException("لم يتم العثور على الصنف في الفاتورة");
                }

                // إنشاء صنف المرتجع مع جميع الحقول المطلوبة
                $return->items()->create([
                    'sales_invoice_item_id' => $invoiceItem->id,
                    'product_id'            => $item['product_id'],
                    'quantity_returned'     => round($item['quantity'], 3),
                    'unit_price'            => round($item['price'], 2),
                    'discount_amount'       => 0,
                    'tax_amount'            => 0,
                    'total'                 => round($item['quantity'] * $item['price'], 2),
                    'item_condition'        => $data['item_condition'] ?? 'good',
                    'return_reason'         => $data['notes'] ?? null,
                ]);

                // إرجاع المخزون
                $this->productService->updateStock(
                    $item['product_id'],
                    $invoice->warehouse_id,
                    $item['quantity'],
                    'add'
                );
            }

            // تحديث رصيد العميل (إضافة المبلغ المرتجع)
            $this->customerService->updateBalance(
                $invoice->customer_id,
                $total,
                'add'
            );

            // تحديث الفاتورة (تقليل الإجمالي والمتبقي)
            $newTotal = max(0, $invoice->total - $total);
            $newRemaining = max(0, $invoice->remaining - $total);

            $invoice->update([
                'total'     => $newTotal,
                'remaining' => $newRemaining,
            ]);

            // معالجة الصور إن وجدت
            if (isset($data['images'])) {
                $this->handleReturnImages($return, $data['images']);
            }

            return $return->load('items.product', 'salesInvoice.customer');
        });
    }

    /* ===================== CANCEL SALES RETURN ===================== */

    /**
     * إلغاء مرتجع مبيعات
     */
    public function cancelSalesReturn(int $returnId): bool
    {
        return DB::transaction(function () use ($returnId) {

            $return = SalesReturn::with(['items', 'salesInvoice.customer'])
                ->lockForUpdate()
                ->findOrFail($returnId);

            // عكس المخزون (طرح الكميات المرتجعة)
            foreach ($return->items as $item) {
                $this->productService->updateStock(
                    $item->product_id,
                    $return->salesInvoice->warehouse_id,
                    $item->quantity_returned,
                    'subtract'
                );
            }

            // عكس رصيد العميل (طرح المبلغ المرتجع)
            $this->customerService->updateBalance(
                $return->salesInvoice->customer_id,
                $return->total,
                'subtract'
            );

            // إرجاع الفاتورة لحالتها السابقة
            $return->salesInvoice->update([
                'total'     => $return->salesInvoice->total + $return->total,
                'remaining' => $return->salesInvoice->remaining + $return->total,
            ]);

            // تحديث الحالة بدلاً من الحذف
            $return->update([
                'status' => 'cancelled'
            ]);

            return true;
        });
    }

    /* ===================== PURCHASE RETURNS ===================== */

    /**
     * إنشاء مرتجع مشتريات
     */
    public function createPurchaseReturn(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {

            $invoice = PurchaseInvoice::with(['items', 'returns.items', 'supplier'])
                ->lockForUpdate()
                ->findOrFail($data['purchase_invoice_id']);

            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('لا يمكن إرجاع أصناف من فاتورة ملغاة');
            }

            $this->validateReturnQuantities(
                $invoice->items,
                $invoice->returns,
                $data['items']
            );

            $total = $this->calculateTotal($data['items']);

            $return = PurchaseReturn::create([
                'purchase_invoice_id' => $invoice->id,
                'supplier_id'         => $invoice->supplier_id,
                'warehouse_id'        => $invoice->warehouse_id,
                'return_number'       => $this->generateReturnNumber('purchase'),
                'return_date'         => $data['return_date'] ?? now(),
                'subtotal'            => $total,
                'discount_amount'     => 0,
                'tax_amount'          => 0,
                'total'               => $total,
                'status'              => 'confirmed',
                'return_reason'       => $data['notes'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'created_by'          => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                // البحث عن purchase_invoice_item_id
                $invoiceItem = $invoice->items->firstWhere('product_id', $item['product_id']);
                
                if (!$invoiceItem) {
                    throw new RuntimeException("لم يتم العثور على الصنف في الفاتورة");
                }

                $return->items()->create([
                    'purchase_invoice_item_id' => $invoiceItem->id,
                    'product_id'               => $item['product_id'],
                    'quantity_returned'        => round($item['quantity'], 3),
                    'unit_price'               => round($item['price'], 2),
                    'discount_amount'          => 0,
                    'tax_amount'               => 0,
                    'total'                    => round($item['quantity'] * $item['price'], 2),
                    'item_condition'           => $data['item_condition'] ?? 'good',
                    'return_reason'            => $data['notes'] ?? null,
                ]);

                // طرح المخزون (لأنه مرتجع مشتريات)
                $this->productService->updateStock(
                    $item['product_id'],
                    $invoice->warehouse_id,
                    $item['quantity'],
                    'subtract'
                );
            }

            // تحديث رصيد المورد (طرح المبلغ المرتجع)
            $this->supplierService->updateBalance(
                $invoice->supplier_id,
                $total,
                'subtract'
            );

            $invoice->update([
                'total'     => max(0, $invoice->total - $total),
                'remaining' => max(0, $invoice->remaining - $total),
            ]);

            if (isset($data['images'])) {
                $this->handleReturnImages($return, $data['images']);
            }

            return $return->load('items.product', 'purchaseInvoice.supplier');
        });
    }

    /**
     * إلغاء مرتجع مشتريات
     */
    public function cancelPurchaseReturn(int $returnId): bool
    {
        return DB::transaction(function () use ($returnId) {

            $return = PurchaseReturn::with(['items', 'purchaseInvoice.supplier'])
                ->lockForUpdate()
                ->findOrFail($returnId);

            foreach ($return->items as $item) {
                $this->productService->updateStock(
                    $item->product_id,
                    $return->purchaseInvoice->warehouse_id,
                    $item->quantity_returned,
                    'add'
                );
            }

            $this->supplierService->updateBalance(
                $return->purchaseInvoice->supplier_id,
                $return->total,
                'add'
            );

            $return->purchaseInvoice->update([
                'total'     => $return->purchaseInvoice->total + $return->total,
                'remaining' => $return->purchaseInvoice->remaining + $return->total,
            ]);

            $return->update([
                'status' => 'cancelled'
            ]);

            return true;
        });
    }

    /* ===================== HELPERS ===================== */

    /**
     * حساب الإجمالي
     */
    private function calculateTotal(array $items): float
    {
        return round(
            collect($items)->sum(fn ($i) => $i['quantity'] * $i['price']),
            2
        );
    }

    /**
     * التحقق من الكميات المرتجعة
     */
    private function validateReturnQuantities($invoiceItems, $returns, array $returnItems): void
    {
        foreach ($returnItems as $returnItem) {

            $invoiceItem = $invoiceItems->firstWhere(
                'product_id',
                $returnItem['product_id']
            );

            if (!$invoiceItem) {
                $product = \App\Models\Product::find($returnItem['product_id']);
                throw new RuntimeException(
                    "المنتج '{$product->name}' غير موجود في الفاتورة"
                );
            }

            // حساب الكمية المرتجعة سابقاً
            $previousReturnedQty = $returns
                ->flatMap->items
                ->where('product_id', $returnItem['product_id'])
                ->sum('quantity_returned');

            $availableQty = $invoiceItem->quantity - $previousReturnedQty;

            if ($returnItem['quantity'] > $availableQty) {
                $product = \App\Models\Product::find($returnItem['product_id']);
                throw new RuntimeException(
                    "الكمية المرتجعة ({$returnItem['quantity']}) للمنتج '{$product->name}' أكبر من المتاح للإرجاع ({$availableQty})"
                );
            }
        }
    }

    /**
     * توليد رقم مرتجع
     */
    private function generateReturnNumber(string $type): string
    {
        $prefix = $type === 'sales' ? 'SR' : 'PR';
        $date = now()->format('Ymd');

        $model = $type === 'sales' ? SalesReturn::class : PurchaseReturn::class;

        $last = $model::whereDate('created_at', today())
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $sequence = $last ? ((int) substr($last->return_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * معالجة صور المرتجع
     */
    private function handleReturnImages($return, array $images): void
    {
        foreach ($images as $image) {
            if ($image && $image->isValid()) {
                $path = $image->store('returns', 'public');
                
                $return->images()->create([
                    'path' => $path,
                    'type' => 'evidence',
                ]);
            }
        }
    }
}