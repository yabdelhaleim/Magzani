<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\StockCount;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class InventoryMovementService
{
    /**
     * تسجيل حركة مخزنية جديدة
     */
    public function recordMovement(array $data): InventoryMovement
    {
        return DB::transaction(function () use ($data) {
            // الحصول على الرصيد الحالي
            $currentStock =StockCount::where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            $quantityBefore = $currentStock ? $currentStock->quantity : 0;
            
            // حساب الكمية المحولة (+ للإدخال، - للإخراج)
            $quantityChange = $data['quantity_change'] ?? $data['quantity'];
            
            // حساب الرصيد الجديد
            $quantityAfter = $quantityBefore + $quantityChange;

            // التأكد من عدم وجود رصيد سالب (إلا في حالة التسويات)
            if ($quantityAfter < 0 && ($data['movement_type'] ?? '') !== 'adjustment') {
                throw new \Exception("لا يمكن إجراء الحركة: الرصيد سيصبح سالباً (الحالي: {$quantityBefore}, المطلوب: {$quantityChange})");
            }

            // توليد رقم الحركة
            $movementNumber = $this->generateMovementNumber($data['movement_type']);

            // إنشاء سجل الحركة
            $movement = InventoryMovement::create([
                'movement_number' => $movementNumber,
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $data['product_id'],
                'movement_type' => $data['movement_type'],
                'from_warehouse_id' => $data['from_warehouse_id'] ?? null,
                'to_warehouse_id' => $data['to_warehouse_id'] ?? null,
                
                // الكميات الأربعة
                'quantity' => abs($quantityChange), // الكمية المطلقة
                'quantity_change' => $quantityChange, // التغيير (+ أو -)
                'quantity_before' => $quantityBefore, // الرصيد قبل
                'quantity_after' => $quantityAfter, // الرصيد بعد
                
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
                'created_by' => auth()->id(),
            ]);

            // تحديث أو إنشاء warehouse_stock
            if ($currentStock) {
                $currentStock->update([
                    'quantity' => $quantityAfter,
                    'updated_at' => now(),
                ]);
            } else {
            StockCount::create([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $data['product_id'],
                    'quantity' => $quantityAfter,
                    'min_quantity' => 0,
                    'max_quantity' => null,
                ]);
            }

            return $movement->fresh(['warehouse', 'product', 'creator']);
        });
    }

    /**
     * توليد رقم الحركة
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
            default => 'MOV',
        };

        $date = now()->format('Ymd');
        $count = InventoryMovement::whereDate('created_at', today())->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    /**
     * الحصول على حركات منتج معين
     */
    public function getProductMovements(int $productId, ?int $warehouseId = null)
    {
        $query = InventoryMovement::with(['warehouse', 'creator'])
            ->where('product_id', $productId)
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get();
    }

    /**
     * الحصول على حركات مخزن معين
     */
    public function getWarehouseMovements(int $warehouseId, ?int $productId = null)
    {
        $query = InventoryMovement::with(['product', 'creator'])
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->get();
    }

    /**
     * إلغاء حركة مخزنية
     */
    public function reverseMovement(int $movementId, string $reason = null): InventoryMovement
    {
        return DB::transaction(function () use ($movementId, $reason) {
            $originalMovement = InventoryMovement::findOrFail($movementId);

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
            ];

            return $this->recordMovement($reverseData);
        });
    }

    /**
     * الحصول على رصيد منتج في مخزن
     */
    public function getProductBalance(int $productId, int $warehouseId): float
    {
        $stock = StockCount::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    /**
     * حساب إجمالي حركات منتج
     */
    public function getProductMovementSummary(int $productId, ?int $warehouseId = null): array
    {
        $query = InventoryMovement::where('product_id', $productId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $movements = $query->get();

        return [
            'total_movements' => $movements->count(),
            'total_in' => $movements->where('quantity_change', '>', 0)->sum('quantity_change'),
            'total_out' => abs($movements->where('quantity_change', '<', 0)->sum('quantity_change')),
            'net_movement' => $movements->sum('quantity_change'),
            'current_balance' => $this->getProductBalance($productId, $warehouseId ?? 0),
        ];
    }
}