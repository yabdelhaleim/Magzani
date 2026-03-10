<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductWarehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class InventoryMovementService
{
    /**
     * ✅ تسجيل حركة مخزنية واحدة - مع التحقق من المفاتيح المطلوبة
     */
    public function recordMovement(array $data): InventoryMovement
    {
        // ✅ التحقق من وجود المفاتيح المطلوبة
        $this->validateMovementData($data);

        return DB::transaction(function () use ($data) {
            
            // الحصول على الرصيد الحالي
            $currentStock = ProductWarehouse::where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            $quantityBefore = $currentStock ? $currentStock->quantity : 0;
            
            // حساب الكمية المحولة (+ للإدخال، - للإخراج)
            $quantityChange = $data['quantity_change'] ?? $data['quantity'] ?? 0;
            
            // حساب الرصيد الجديد
            $quantityAfter = $quantityBefore + $quantityChange;

            // التأكد من عدم وجود رصيد سالب (إلا في حالة التسويات)
            if ($quantityAfter < 0 && ($data['movement_type'] ?? '') !== 'adjustment') {
                throw new Exception("لا يمكن إجراء الحركة: الرصيد سيصبح سالباً (الحالي: {$quantityBefore}, المطلوب: {$quantityChange})");
            }

            // توليد رقم الحركة
            $movementNumber = $data['movement_number'] ?? $this->generateMovementNumber($data['movement_type']);

            // إنشاء سجل الحركة
            $movement = InventoryMovement::create([
                'movement_number' => $movementNumber,
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $data['product_id'],
                'movement_type' => $data['movement_type'],
                'from_warehouse_id' => $data['from_warehouse_id'] ?? null,
                'to_warehouse_id' => $data['to_warehouse_id'] ?? null,
                
                // الكميات الأربعة
                'quantity' => abs($quantityChange),
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                
                // التكاليف والأسعار
                'unit_cost' => $data['unit_cost'] ?? 0,
                'unit_price' => $data['unit_price'] ?? 0,
                'total_cost' => ($data['unit_cost'] ?? 0) * abs($quantityChange),
                'total_price' => ($data['unit_price'] ?? 0) * abs($quantityChange),
                
                // معلومات إضافية
                'movement_date' => $data['movement_date'] ?? now(),
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'purchase_invoice_id' => $data['purchase_invoice_id'] ?? null,
                'sales_invoice_id' => $data['sales_invoice_id'] ?? null,
                'transfer_id' => $data['transfer_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'reason' => $data['reason'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // تحديث أو إنشاء product_warehouse
            if ($currentStock) {
                $currentStock->update([
                    'quantity' => $quantityAfter,
                    'updated_at' => now(),
                ]);
            } else {
                ProductWarehouse::create([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $data['product_id'],
                    'quantity' => $quantityAfter,
                    'min_stock' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            return $movement->fresh(['warehouse', 'product', 'creator']);
        });
    }

    /**
     * ✅ تسجيل حركات متعددة بالـ Bulk - محسّن للعمليات الكبيرة
     */
    public function bulkRecordMovements(array $movements): array
    {
        $results = [];
        $errors = [];

        // معالجة بالـ Chunks لتجنب مشاكل الذاكرة
        collect($movements)->chunk(100)->each(function ($chunk) use (&$results, &$errors) {
            foreach ($chunk as $index => $movement) {
                try {
                    $results[] = $this->recordMovement($movement);
                } catch (Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'movement' => $movement,
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('❌ فشل تسجيل الحركة', [
                        'index' => $index,
                        'movement' => $movement,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        if (!empty($errors)) {
            Log::warning('⚠️ بعض الحركات فشلت', [
                'total' => count($movements),
                'success' => count($results),
                'failed' => count($errors)
            ]);
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total' => count($movements),
            'success_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * ✅ التحقق من صحة بيانات الحركة
     */
    private function validateMovementData(array $data): void
    {
        $requiredFields = ['warehouse_id', 'product_id', 'movement_type'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null) {
                throw new Exception("الحقل المطلوب مفقود: {$field}");
            }
        }

        // التحقق من نوع الحركة
        $validTypes = [
            'purchase', 'sale', 'return_in', 'return_out',
            'transfer_in', 'transfer_out', 'adjustment',
            'damage', 'expired', 'production', 'consumption'
        ];

        if (!in_array($data['movement_type'], $validTypes)) {
            throw new Exception("نوع الحركة غير صالح: {$data['movement_type']}");
        }

        // التحقق من الكمية
        if (!isset($data['quantity_change']) && !isset($data['quantity'])) {
            throw new Exception("يجب تحديد الكمية (quantity_change أو quantity)");
        }
    }

    /**
     * ✅ توليد رقم الحركة - محسّن
     */
    private function generateMovementNumber(string $type): string
    {
        $prefix = match($type) {
            'purchase' => 'PUR',
            'sale' => 'SAL',
            'return_in' => 'RIN',
            'return_out' => 'ROUT',
            'transfer_in' => 'TIN',
            'transfer_out' => 'TOUT',
            'adjustment' => 'ADJ',
            'damage' => 'DMG',
            'expired' => 'EXP',
            'production' => 'PRD',
            'consumption' => 'CON',
            default => 'MOV',
        };

        $date = now()->format('Ymd');
        
        // استخدام timestamp لضمان التفرّد
        $timestamp = now()->format('His');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        return sprintf('%s-%s-%s-%s', $prefix, $date, $timestamp, $random);
    }

    /**
     * ✅ الحصول على حركات منتج معين - محسّن
     */
    public function getProductMovements(
        int $productId, 
        ?int $warehouseId = null,
        array $filters = []
    ) {
        $query = InventoryMovement::with(['warehouse:id,name', 'creator:id,name'])
            ->where('product_id', $productId)
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        // فلاتر إضافية
        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        // استخدام pagination للبيانات الكبيرة
        return $query->paginate($filters['per_page'] ?? 50);
    }

    /**
     * ✅ الحصول على حركات مخزن معين - محسّن
     */
    public function getWarehouseMovements(
        int $warehouseId, 
        ?int $productId = null,
        array $filters = []
    ) {
        $query = InventoryMovement::with(['product:id,name,code', 'creator:id,name'])
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        // فلاتر إضافية
        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        return $query->paginate($filters['per_page'] ?? 50);
    }

    /**
     * ✅ إلغاء حركة مخزنية
     */
    public function reverseMovement(int $movementId, string $reason = null): InventoryMovement
    {
        return DB::transaction(function () use ($movementId, $reason) {
            $originalMovement = InventoryMovement::findOrFail($movementId);

            // التحقق من عدم عكس حركة معكوسة
            if ($originalMovement->movement_type === 'adjustment' && 
                str_contains($originalMovement->notes ?? '', 'عكس حركة')) {
                throw new Exception('لا يمكن عكس حركة معكوسة مسبقاً');
            }

            // إنشاء حركة معاكسة
            $reverseData = [
                'warehouse_id' => $originalMovement->warehouse_id,
                'product_id' => $originalMovement->product_id,
                'movement_type' => 'adjustment',
                'quantity_change' => -$originalMovement->quantity_change,
                'unit_cost' => $originalMovement->unit_cost,
                'unit_price' => $originalMovement->unit_price,
                'movement_date' => now(),
                'notes' => "عكس حركة #{$originalMovement->movement_number}",
                'reason' => $reason ?? "إلغاء حركة #{$originalMovement->movement_number}",
                'reference_type' => get_class($originalMovement),
                'reference_id' => $originalMovement->id,
            ];

            return $this->recordMovement($reverseData);
        });
    }

    /**
     * ✅ الحصول على رصيد منتج في مخزن
     */
    public function getProductBalance(int $productId, int $warehouseId): float
    {
        $stock = ProductWarehouse::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    /**
     * ✅ الحصول على رصيد عدة منتجات في مخزن
     */
    public function getMultipleProductsBalance(array $productIds, int $warehouseId): array
    {
        return ProductWarehouse::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->pluck('quantity', 'product_id')
            ->toArray();
    }

    /**
     * ✅ حساب إجمالي حركات منتج - محسّن
     */
    public function getProductMovementSummary(int $productId, ?int $warehouseId = null): array
    {
        $query = InventoryMovement::where('product_id', $productId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        // استخدام selectRaw للأداء
        $summary = $query->selectRaw('
            COUNT(*) as total_movements,
            SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as total_in,
            SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as total_out,
            SUM(quantity_change) as net_movement,
            SUM(total_cost) as total_cost,
            SUM(total_price) as total_price
        ')->first();

        $currentBalance = $warehouseId 
            ? $this->getProductBalance($productId, $warehouseId) 
            : 0;

        return [
            'total_movements' => $summary->total_movements ?? 0,
            'total_in' => $summary->total_in ?? 0,
            'total_out' => $summary->total_out ?? 0,
            'net_movement' => $summary->net_movement ?? 0,
            'current_balance' => $currentBalance,
            'total_cost' => $summary->total_cost ?? 0,
            'total_price' => $summary->total_price ?? 0,
        ];
    }

    /**
     * ✅ حساب إجمالي حركات مخزن - محسّن
     */
    public function getWarehouseMovementSummary(int $warehouseId, array $filters = []): array
    {
        $query = InventoryMovement::where('warehouse_id', $warehouseId);

        // فلاتر التاريخ
        if (!empty($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        $summary = $query->selectRaw('
            COUNT(*) as total_movements,
            COUNT(DISTINCT product_id) as total_products,
            SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as total_in,
            SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as total_out,
            SUM(total_cost) as total_cost,
            SUM(total_price) as total_price
        ')->first();

        // إحصائيات حسب النوع
        $byType = InventoryMovement::where('warehouse_id', $warehouseId)
            ->selectRaw('
                movement_type,
                COUNT(*) as count,
                SUM(ABS(quantity_change)) as total_quantity
            ')
            ->groupBy('movement_type')
            ->get()
            ->keyBy('movement_type');

        return [
            'total_movements' => $summary->total_movements ?? 0,
            'total_products' => $summary->total_products ?? 0,
            'total_in' => $summary->total_in ?? 0,
            'total_out' => $summary->total_out ?? 0,
            'total_cost' => $summary->total_cost ?? 0,
            'total_price' => $summary->total_price ?? 0,
            'by_type' => $byType->toArray(),
        ];
    }

    /**
     * ✅ تنظيف الحركات القديمة (أرشفة)
     */
    public function archiveOldMovements(int $daysOld = 365): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return DB::transaction(function () use ($cutoffDate) {
            // يمكن نقلها إلى جدول أرشيف
            $count = InventoryMovement::where('movement_date', '<', $cutoffDate)
                ->where('archived', false)
                ->update(['archived' => true]);

            Log::info("✅ تم أرشفة {$count} حركة مخزنية");
            
            return $count;
        });
    }
}