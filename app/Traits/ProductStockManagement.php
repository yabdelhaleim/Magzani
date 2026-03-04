<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * 🎯 Trait لإدارة المخزون في المنتجات
 * 
 * يُستخدم في:
 * - Product Model
 * 
 * الاستخدام:
 * use ProductStockManagement;
 */
trait ProductStockManagement
{
    /* ===========================
     * 📊 STOCK ACCESSORS
     * =========================== */

    /**
     * ✅ إجمالي المخزون (محسّن ومضمون)
     */
    public function getTotalStockAttribute(): float
    {
        // ✅ الحل 1: لو العلاقة محملة
        if ($this->relationLoaded('warehouses')) {
            return (float) $this->warehouses->sum(function ($warehouse) {
                return (float) ($warehouse->pivot->quantity ?? 0);
            });
        }

        // ✅ الحل 2: لو في قيمة من withSum
        if (array_key_exists('total_stock', $this->getAttributes())) {
            return (float) $this->getAttributes()['total_stock'];
        }

        // ✅ الحل 3: query مباشر (آخر حل)
        return (float) $this->warehouses()->sum('quantity');
    }

    /**
     * ✅ المخزون المتاح (غير المحجوز)
     */
    public function getAvailableStockAttribute(): float
    {
        // ✅ لو العلاقة محملة
        if ($this->relationLoaded('warehouses')) {
            return (float) $this->warehouses->sum(function ($warehouse) {
                $quantity = (float) ($warehouse->pivot->quantity ?? 0);
                $reserved = (float) ($warehouse->pivot->reserved_quantity ?? 0);
                return max(0, $quantity - $reserved);
            });
        }

        // ✅ لو في قيمة محسوبة
        if (array_key_exists('available_stock', $this->getAttributes())) {
            return (float) $this->getAttributes()['available_stock'];
        }

        // ✅ query مباشر
        $result = DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->selectRaw('SUM(GREATEST(0, quantity - COALESCE(reserved_quantity, 0))) as available')
            ->value('available');

        return (float) ($result ?? 0);
    }

    /**
     * ✅ المخزون المحجوز
     */
    public function getReservedStockAttribute(): float
    {
        if ($this->relationLoaded('warehouses')) {
            return (float) $this->warehouses->sum(function ($warehouse) {
                return (float) ($warehouse->pivot->reserved_quantity ?? 0);
            });
        }

        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->sum('reserved_quantity');
    }

    /**
     * ✅ قيمة المخزون الإجمالية (بسعر الشراء)
     */
    public function getStockValueAttribute(): float
    {
        return $this->total_stock * $this->purchase_price;
    }

    /**
     * ✅ قيمة المخزون المتوقعة (بسعر البيع)
     */
    public function getExpectedStockValueAttribute(): float
    {
        return $this->total_stock * $this->selling_price;
    }

    /**
     * ✅ نسبة توفر المخزون (%)
     */
    public function getStockAvailabilityPercentageAttribute(): float
    {
        $total = $this->total_stock;
        if ($total <= 0) return 0;

        return round(($this->available_stock / $total) * 100, 2);
    }

    /* ===========================
     * 🎯 STOCK SCOPES
     * =========================== */

    /**
     * ✅ المنتجات قليلة المخزون
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('warehouses', function ($q) {
            $q->whereRaw('product_warehouse.quantity <= COALESCE(product_warehouse.min_stock, products.stock_alert_quantity, 0)');
        });
    }

    /**
     * ✅ المنتجات النافذة
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereDoesntHave('warehouses', function ($q) {
            $q->where('quantity', '>', 0);
        });
    }

    /**
     * ✅ المنتجات المتوفرة
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('warehouses', function ($q) {
            $q->where('quantity', '>', 0);
        });
    }

    /**
     * ✅ مع إحصائيات المخزون (محسّن)
     */
    public function scopeWithStockStats(Builder $query): Builder
    {
        return $query
            ->withSum('warehouses as total_stock', 'quantity')
            ->withSum('warehouses as reserved_stock', 'reserved_quantity')
            ->addSelect([
                'available_stock' => function ($q) {
                    $q->selectRaw('SUM(GREATEST(0, quantity - COALESCE(reserved_quantity, 0)))')
                      ->from('product_warehouse')
                      ->whereColumn('product_warehouse.product_id', 'products.id');
                }
            ]);
    }

    /* ===========================
     * 🛠️ STOCK METHODS
     * =========================== */

    /**
     * ✅ الحصول على المخزون في مخزن معين
     */
    public function getStockInWarehouse(int $warehouseId): float
    {
        if ($this->relationLoaded('warehouses')) {
            $warehouse = $this->warehouses->firstWhere('id', $warehouseId);
            return $warehouse ? (float) ($warehouse->pivot->quantity ?? 0) : 0;
        }

        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    /**
     * ✅ المخزون المتاح في مخزن معين
     */
    public function getAvailableStockInWarehouse(int $warehouseId): float
    {
        if ($this->relationLoaded('warehouses')) {
            $warehouse = $this->warehouses->firstWhere('id', $warehouseId);
            if (!$warehouse) return 0;

            $quantity = (float) ($warehouse->pivot->quantity ?? 0);
            $reserved = (float) ($warehouse->pivot->reserved_quantity ?? 0);
            return max(0, $quantity - $reserved);
        }

        $result = DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->selectRaw('GREATEST(0, quantity - COALESCE(reserved_quantity, 0)) as available')
            ->first();

        return $result ? (float) $result->available : 0;
    }

    /**
     * ✅ هل المنتج متوفر في مخزن معين؟
     */
    public function isAvailableInWarehouse(int $warehouseId, float $quantity = 1): bool
    {
        return $this->getAvailableStockInWarehouse($warehouseId) >= $quantity;
    }

    /**
     * ✅ هل المنتج نفذ؟
     */
    public function isOutOfStock(): bool
    {
        return $this->total_stock <= 0;
    }

    /**
     * ✅ هل المخزون منخفض؟
     */
    public function isLowStock(): bool
    {
        $alertQty = $this->stock_alert_quantity ?? 10;
        return $this->total_stock > 0 && $this->total_stock <= $alertQty;
    }

    /**
     * ✅ هل المنتج يحتاج إعادة طلب؟
     */
    public function needsReorder(): bool
    {
        return $this->total_stock <= ($this->reorder_level ?? 0);
    }

    /**
     * ✅ حالة المخزون (نص)
     */
    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }

        if ($this->isLowStock()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * ✅ حالة المخزون (نص عربي)
     */
    public function getStockStatusLabel(): string
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'نفذ',
            'low_stock' => 'منخفض',
            'in_stock' => 'متوفر',
            default => 'غير معروف'
        };
    }

    /**
     * ✅ لون حالة المخزون
     */
    public function getStockStatusColor(): string
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'red',
            'low_stock' => 'orange',
            'in_stock' => 'green',
            default => 'gray'
        };
    }

    /**
     * ✅ التحقق من إمكانية البيع
     */
    public function canSell(float $quantity, int $warehouseId): bool
    {
        return $this->getAvailableStockInWarehouse($warehouseId) >= $quantity;
    }

    /**
     * ✅ حجز كمية من المخزون
     */
    public function reserveStock(float $quantity, int $warehouseId): bool
    {
        if (!$this->canSell($quantity, $warehouseId)) {
            return false;
        }

        return DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->update([
                'reserved_quantity' => DB::raw('COALESCE(reserved_quantity, 0) + ' . $quantity),
                'available_quantity' => DB::raw('GREATEST(0, quantity - COALESCE(reserved_quantity, 0) - ' . $quantity . ')'),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ إلغاء حجز كمية
     */
    public function releaseStock(float $quantity, int $warehouseId): bool
    {
        return DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->where('reserved_quantity', '>=', $quantity)
            ->update([
                'reserved_quantity' => DB::raw('GREATEST(0, COALESCE(reserved_quantity, 0) - ' . $quantity . ')'),
                'available_quantity' => DB::raw('quantity - GREATEST(0, COALESCE(reserved_quantity, 0) - ' . $quantity . ')'),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ تحديث المخزون في مخزن معين
     */
    public function updateStockInWarehouse(int $warehouseId, float $newQuantity): bool
    {
        return DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->update([
                'quantity' => $newQuantity,
                'available_quantity' => DB::raw('GREATEST(0, ' . $newQuantity . ' - COALESCE(reserved_quantity, 0))'),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ إضافة كمية للمخزون
     */
    public function addStock(float $quantity, int $warehouseId): bool
    {
        return DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->update([
                'quantity' => DB::raw('quantity + ' . $quantity),
                'available_quantity' => DB::raw('quantity + ' . $quantity . ' - COALESCE(reserved_quantity, 0)'),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ خصم كمية من المخزون
     */
    public function deductStock(float $quantity, int $warehouseId): bool
    {
        if (!$this->canSell($quantity, $warehouseId)) {
            return false;
        }

        return DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->update([
                'quantity' => DB::raw('GREATEST(0, quantity - ' . $quantity . ')'),
                'available_quantity' => DB::raw('GREATEST(0, quantity - ' . $quantity . ' - COALESCE(reserved_quantity, 0))'),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ نقل المخزون بين مخازن
     */
    public function transferStock(int $fromWarehouseId, int $toWarehouseId, float $quantity): bool
    {
        if (!$this->canSell($quantity, $fromWarehouseId)) {
            return false;
        }

        DB::beginTransaction();
        try {
            // خصم من المخزن الأول
            $this->deductStock($quantity, $fromWarehouseId);

            // إضافة للمخزن الثاني
            $this->addStock($quantity, $toWarehouseId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * ✅ الحصول على توزيع المخزون (كل المخازن)
     */
    public function getStockDistribution(): array
    {
        if ($this->relationLoaded('warehouses')) {
            return $this->warehouses->map(function ($warehouse) {
                return [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                    'quantity' => (float) ($warehouse->pivot->quantity ?? 0),
                    'reserved' => (float) ($warehouse->pivot->reserved_quantity ?? 0),
                    'available' => (float) ($warehouse->pivot->available_quantity ?? 0),
                    'min_stock' => (float) ($warehouse->pivot->min_stock ?? 0),
                ];
            })->toArray();
        }

        return DB::table('product_warehouse')
            ->join('warehouses', 'product_warehouse.warehouse_id', '=', 'warehouses.id')
            ->where('product_warehouse.product_id', $this->id)
            ->select([
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'product_warehouse.quantity',
                'product_warehouse.reserved_quantity as reserved',
                'product_warehouse.available_quantity as available',
                'product_warehouse.min_stock',
            ])
            ->get()
            ->toArray();
    }
}
















