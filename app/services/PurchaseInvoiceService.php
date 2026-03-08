<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseInvoiceService
{
    /**
     * إنشاء فاتورة شراء جديدة
     */
    public function create(array $data): PurchaseInvoice
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. إنشاء الفاتورة
                $invoice = $this->createInvoice($data);

                // 2. إضافة الأصناف
                $this->attachItems($invoice, $data['items']);

                // 3. حساب الإجمالي
                $this->calculateTotal($invoice);

                // 4. تحديث المخزون
                $this->updateInventory($invoice);

                // 5. تسجيل في اللوج
                Log::info('تم إنشاء فاتورة شراء', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total' => $invoice->total
                ]);

                return $invoice->fresh(['items.product', 'supplier', 'warehouse']);

            } catch (Exception $e) {
                Log::error('خطأ في إنشاء فاتورة الشراء: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تحديث فاتورة شراء موجودة
     */
    public function update(PurchaseInvoice $invoice, array $data): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            try {
                // 1. استرجاع حركة المخزون القديمة
                $this->reverseInventory($invoice);

                // 2. حذف الأصناف القديمة
                $invoice->items()->delete();

                // 3. تحديث بيانات الفاتورة
                $invoice->update([
                    'supplier_id' => $data['supplier_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'invoice_date' => $data['invoice_date'],
                    'notes' => $data['notes'] ?? null,
                    'discount' => $data['discount'] ?? 0,
                    'tax' => $data['tax'] ?? 0,
                ]);

                // 4. إضافة الأصناف الجديدة
                $this->attachItems($invoice, $data['items']);

                // 5. إعادة حساب الإجمالي
                $this->calculateTotal($invoice);

                // 6. تحديث المخزون بالبيانات الجديدة
                $this->updateInventory($invoice);

                Log::info('تم تحديث فاتورة الشراء', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number
                ]);

                return $invoice->fresh(['items.product', 'supplier', 'warehouse']);

            } catch (Exception $e) {
                Log::error('خطأ في تحديث فاتورة الشراء: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * حذف فاتورة شراء (Soft Delete)
     */
    public function delete(PurchaseInvoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            try {
                // 1. استرجاع حركة المخزون
                $this->reverseInventory($invoice);

                // 2. حذف الفاتورة (Soft Delete)
                $invoice->delete();

                Log::info('تم حذف فاتورة الشراء', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number
                ]);

                return true;

            } catch (Exception $e) {
                Log::error('خطأ في حذف فاتورة الشراء: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * إنشاء سجل الفاتورة
     */
    private function createInvoice(array $data): PurchaseInvoice
    {
        return PurchaseInvoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
            'invoice_date' => $data['invoice_date'],
            'notes' => $data['notes'] ?? null,
            'discount' => $data['discount'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'status' => 'draft', // مسودة - مؤكدة - ملغاة
            'total' => 0, // سيتم حسابه لاحقاً
        ]);
    }

    /**
     * إضافة أصناف الفاتورة
     */
    private function attachItems(PurchaseInvoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            // استخراج البيانات - الفورم يرسل قيم فارغة '' فيجب تحويلها لعدد أو null
            $quantity = (float) ($item['qty'] ?? 1) ?: 1;
            $price = (float) ($item['price'] ?? 0);
            $conversionFactor = (float) ($item['conversion_factor'] ?? 1) ?: 1;
            $weight = isset($item['weight']) && $item['weight'] !== '' && $item['weight'] !== null
                ? (float) $item['weight'] : null;
            $baseUnitType = !empty($item['base_unit_type']) ? $item['base_unit_type'] : null;
            $unitCode = !empty($item['unit_code']) ? $item['unit_code'] : null;
            
            // حساب الكمية بالوحدة الأساسية
            $baseQuantity = $quantity * $conversionFactor;
            
            // للمنتجات بالوزن: الكمية الأساسية = الوزن
            if ($baseUnitType === 'weight' && $weight) {
                $baseQuantity = $weight;
            }
            
            $itemTotal = $quantity * $price;
            PurchaseInvoiceItem::create([
                'purchase_invoice_id' => $invoice->id,
                'product_id' => $item['product_id'],
                'purchase_unit_id' => null, // لا نستخدم foreign key مؤقتاً
                'unit_code' => $unitCode,
                'conversion_factor' => $conversionFactor,
                'quantity' => $quantity,
                'base_quantity' => $baseQuantity,
                'weight' => $weight,
                'base_unit_type' => $baseUnitType,
                'unit_price' => $price,
                'unit_cost' => $price,
                'cost_price' => $price,
                'subtotal' => $itemTotal,
                'total' => $itemTotal,
            ]);
        }
    }

    /**
     * حساب إجمالي الفاتورة
     */
    private function calculateTotal(PurchaseInvoice $invoice): void
    {
        $subtotal = $invoice->items()->sum('total');
        $discount = $invoice->discount ?? 0;
        $tax = $invoice->tax ?? 0;

        $total = ($subtotal - $discount) + $tax;

        $invoice->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    /**
     * تحديث المخزون (زيادة الكميات)
     */
    private function updateInventory(PurchaseInvoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            // تحديد الكمية المضافة للمخزون
            // للمنتجات بالوزن: نستخدم الوزن، وإلا نستخدم الكمية بالوحدة الأساسية
            $quantityToAdd = $item->base_quantity;
            
            // إذا كان المنتج بالوزن وله وزن محدد، نستخدم الوزن
            if ($item->base_unit_type === 'weight' && $item->weight) {
                $quantityToAdd = $item->weight;
            }
            
            // تحديث رصيد المنتج في المخزن
            DB::table('product_warehouse')
                ->updateOrInsert(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $invoice->warehouse_id,
                    ],
                    [
                        'quantity' => DB::raw('quantity + ' . $quantityToAdd),
                        'updated_at' => now(),
                    ]
                );
        }
    }

    /**
     * عكس حركة المخزون (عند التعديل أو الحذف)
     */
    private function reverseInventory(PurchaseInvoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            // تحديد الكمية المراد إرجاعها
            $quantityToReverse = $item->base_quantity;
            
            // إذا كان المنتج بالوزن وله وزن محدد، نستخدم الوزن
            if ($item->base_unit_type === 'weight' && $item->weight) {
                $quantityToReverse = $item->weight;
            }
            
            // إرجاع الكمية من المخزن
            DB::table('product_warehouse')
                ->where('product_id', $item->product_id)
                ->where('warehouse_id', $invoice->warehouse_id)
                ->decrement('quantity', $quantityToReverse);
        }
    }

    /**
     * توليد رقم فاتورة تلقائي
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = PurchaseInvoice::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastInvoice ? $lastInvoice->id + 1 : 1;
        
        return sprintf('PUR-%s-%03d', $year, $number);
    }

    /**
     * الحصول على قائمة الفواتير مع الفلترة والبحث
     */
    public function getInvoices(array $filters = [])
    {
        $query = PurchaseInvoice::with(['supplier', 'warehouse', 'items'])
            ->orderBy('invoice_date', 'desc');

        // فلتر بالمورد
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // فلتر بالمخزن
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        // فلتر بالحالة
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // فلتر بالتاريخ من - إلى
        if (!empty($filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $filters['date_to']);
        }

        // بحث برقم الفاتورة
        if (!empty($filters['search'])) {
            $query->where('invoice_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * احصائيات فواتير الشراء
     */
    public function getStatistics()
    {
        return [
            'total_invoices' => PurchaseInvoice::count(),
            'total_amount' => PurchaseInvoice::sum('total'),
            'pending_invoices' => PurchaseInvoice::where('status', 'pending')->count(),
            'today_amount' => PurchaseInvoice::whereDate('invoice_date', today())->sum('total'),
        ];
    }
}