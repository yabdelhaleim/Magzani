<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseReturnService
{
    /**
     * إنشاء مرتجع شراء جديد
     */
    public function create(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. جلب الفاتورة الأصلية
                $invoice = PurchaseInvoice::with(['items', 'supplier', 'warehouse'])->findOrFail($data['purchase_invoice_id']);

                // 2. إنشاء المرتجع
                $return = $this->createReturn($data, $invoice);

                // 3. إضافة الأصناف المرتجعة
                $this->attachItems($return, $data['items']);

                // 4. حساب الإجمالي
                $this->calculateTotal($return);

                // 5. تحديث المخزون (تقليل الكميات)
                $this->updateInventory($return, $invoice);

                // 6. تسجيل في اللوج
                Log::info('تم إنشاء مرتجع شراء', [
                    'return_id' => $return->id,
                    'return_number' => $return->return_number,
                    'invoice_number' => $invoice->invoice_number,
                ]);

                return $return->fresh(['items.purchaseInvoiceItem.product', 'purchaseInvoice.supplier']);

            } catch (Exception $e) {
                Log::error('خطأ في إنشاء مرتجع الشراء: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تحديث مرتجع شراء موجود
     */
    public function update(PurchaseReturn $return, array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($return, $data) {
            try {
                $invoice = $return->purchaseInvoice;

                // 1. استرجاع حركة المخزون القديمة
                $this->reverseInventory($return, $invoice);

                // 2. حذف الأصناف القديمة
                $return->items()->delete();

                // 3. تحديث بيانات المرتجع
                $return->update([
                    'return_date' => $data['return_date'],
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'status' => $data['status'] ?? $return->status,
                    'return_reason' => $data['return_reason'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'updated_by' => auth()->id(),
                ]);

                // 4. إضافة الأصناف الجديدة
                $this->attachItems($return, $data['items']);

                // 5. إعادة حساب الإجمالي
                $this->calculateTotal($return);

                // 6. تحديث المخزون بالبيانات الجديدة
                $this->updateInventory($return, $invoice);

                Log::info('تم تحديث مرتجع الشراء', [
                    'return_id' => $return->id,
                    'return_number' => $return->return_number,
                ]);

                return $return->fresh(['items.purchaseInvoiceItem.product', 'purchaseInvoice.supplier']);

            } catch (Exception $e) {
                Log::error('خطأ في تحديث مرتجع الشراء: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * حذف مرتجع شراء (Soft Delete)
     */
    public function delete(PurchaseReturn $return): bool
    {
        return DB::transaction(function () use ($return) {
            try {
                $invoice = $return->purchaseInvoice;

                // 1. استرجاع حركة المخزون
                $this->reverseInventory($return, $invoice);

                // 2. حذف المرتجع (Soft Delete)
                $return->delete();

                Log::info('تم حذف مرتجع الشراء', [
                    'return_id' => $return->id,
                    'return_number' => $return->return_number,
                ]);

                return true;

            } catch (Exception $e) {
                Log::error('خطأ في حذف مرتجع الشراء: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * إنشاء سجل المرتجع
     */
    private function createReturn(array $data, PurchaseInvoice $invoice): PurchaseReturn
    {
        return PurchaseReturn::create([
            'return_number' => $this->generateReturnNumber(),
            'purchase_invoice_id' => $invoice->id,
            'supplier_id' => $invoice->supplier_id,
            'warehouse_id' => $invoice->warehouse_id,
            'return_date' => $data['return_date'],
            'subtotal' => 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total' => 0,
            'status' => $data['status'] ?? 'draft',
            'return_reason' => $data['return_reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * إضافة أصناف المرتجع
     */
    private function attachItems(PurchaseReturn $return, array $items): void
    {
        foreach ($items as $item) {
            // جلب بيانات الصنف من الفاتورة الأصلية
            $originalItem = \App\Models\PurchaseInvoiceItem::findOrFail($item['purchase_invoice_item_id']);

            $qty = $item['quantity_returned'];
            $price = $originalItem->price;
            $itemTotal = $qty * $price;

            PurchaseReturnItem::create([
                'purchase_return_id' => $return->id,
                'purchase_invoice_item_id' => $originalItem->id,
                'product_id' => $originalItem->product_id,
                'quantity_returned' => $qty,
                'unit_price' => $price,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $itemTotal,
                'item_condition' => $item['item_condition'],
                'return_reason' => $item['return_reason'],
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    /**
     * حساب إجمالي المرتجع
     */
    private function calculateTotal(PurchaseReturn $return): void
    {
        $subtotal = $return->items()->sum('total');
        $discountAmount = $return->discount_amount ?? 0;
        $taxAmount = $return->tax_amount ?? 0;

        $total = ($subtotal - $discountAmount) + $taxAmount;

        $return->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * تحديث المخزون (تقليل الكميات - لأنها مرتجعة للمورد)
     */
    private function updateInventory(PurchaseReturn $return, PurchaseInvoice $invoice): void
    {
        foreach ($return->items as $item) {
            // تقليل الكمية من المخزن
            DB::table('product_warehouse')
                ->where('product_id', $item->product_id)
                ->where('warehouse_id', $invoice->warehouse_id)
                ->decrement('qty', $item->quantity_returned);
        }
    }

    /**
     * عكس حركة المخزون (عند التعديل أو الحذف)
     */
    private function reverseInventory(PurchaseReturn $return, PurchaseInvoice $invoice): void
    {
        foreach ($return->items as $item) {
            // إرجاع الكمية للمخزن
            DB::table('product_warehouse')
                ->where('product_id', $item->product_id)
                ->where('warehouse_id', $invoice->warehouse_id)
                ->increment('qty', $item->quantity_returned);
        }
    }

    /**
     * توليد رقم مرتجع تلقائي
     */
    private function generateReturnNumber(): string
    {
        $year = date('Y');
        $lastReturn = PurchaseReturn::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastReturn ? $lastReturn->id + 1 : 1;
        
        return sprintf('RET-%s-%03d', $year, $number);
    }

    /**
     * الحصول على قائمة المرتجعات مع الفلترة والبحث
     */
    public function getReturns(array $filters = [])
    {
        $query = PurchaseReturn::with(['purchaseInvoice.supplier', 'items.purchaseInvoiceItem.product'])
            ->orderBy('return_date', 'desc');

        // فلتر بالمورد
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // فلتر بالحالة
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // فلتر بالتاريخ
        if (!empty($filters['date'])) {
            $query->whereDate('return_date', $filters['date']);
        }

        // بحث (برقم المرتجع أو اسم المورد أو اسم المنتج)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseInvoice.supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items.purchaseInvoiceItem.product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * احصائيات مرتجعات الشراء
     */
    public function getStatistics()
    {
        return [
            'total_returns' => PurchaseReturn::count(),
            'total_amount' => PurchaseReturn::sum('total'),
            'pending_returns' => PurchaseReturn::where('status', 'pending')->count(),
            'today_returns' => PurchaseReturn::whereDate('return_date', today())->count(),
        ];
    }

    /**
     * الحصول على الأصناف المتاحة للإرجاع من فاتورة معينة
     */
    public function getAvailableItemsForReturn($invoiceId)
    {
        $invoice = PurchaseInvoice::with(['items.product'])->findOrFail($invoiceId);
        
        $availableItems = [];
        
        foreach ($invoice->items as $item) {
            // حساب الكمية المرتجعة سابقاً
            $returnedQty = PurchaseReturnItem::where('purchase_invoice_item_id', $item->id)->sum('quantity_returned');
            
            $availableQty = $item->qty - $returnedQty;
            
            if ($availableQty > 0) {
                $availableItems[] = [
                    'purchase_invoice_item_id' => $item->id,
                    'product' => $item->product,
                    'original_qty' => $item->qty,
                    'returned_qty' => $returnedQty,
                    'available_qty' => $availableQty,
                    'unit_price' => $item->price,
                ];
            }
        }
        
        return $availableItems;
    }
}