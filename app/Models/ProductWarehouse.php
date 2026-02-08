<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductWarehouse extends Pivot
{
    protected $table = 'product_warehouse';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'min_stock',
        'average_cost',
        'last_count_quantity',
        'last_count_date',
        'adjustment_total',
        'last_sale_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'min_stock' => 'decimal:3',
        'average_cost' => 'decimal:2',
        'last_count_quantity' => 'decimal:3',
        'adjustment_total' => 'decimal:3',
        'last_count_date' => 'datetime',
        'last_sale_date' => 'date',
    ];

    // ==================== Relationships ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id', 'product_id')
            ->where('warehouse_id', $this->warehouse_id);
    }

    // ==================== Accessors (محسّنة) ====================

    /**
     * ✅ الكمية المتاحة (محسّن)
     */
    public function getAvailableQuantityAttribute(): float
    {
        $quantity = (float) ($this->attributes['quantity'] ?? 0);
        $reserved = (float) ($this->attributes['reserved_quantity'] ?? 0);
        return max(0, $quantity - $reserved);
    }

    /**
     * ✅ هل المخزون منخفض؟
     */
    public function getIsLowStockAttribute(): bool
    {
        $quantity = (float) ($this->attributes['quantity'] ?? 0);
        $minStock = (float) ($this->attributes['min_stock'] ?? 0);
        return $quantity <= $minStock;
    }

    /**
     * ✅ فرق الجرد
     */
    public function getCountVarianceAttribute(): ?float
    {
        if (!isset($this->attributes['last_count_quantity'])) {
            return null;
        }

        $current = (float) ($this->attributes['quantity'] ?? 0);
        $lastCount = (float) $this->attributes['last_count_quantity'];

        return round($current - $lastCount, 3);
    }

    /**
     * ✅ نسبة الاستخدام
     */
    public function getUsagePercentageAttribute(): float
    {
        $quantity = (float) ($this->attributes['quantity'] ?? 0);
        $reserved = (float) ($this->attributes['reserved_quantity'] ?? 0);

        if ($quantity == 0) {
            return 0;
        }

        return round(($reserved / $quantity) * 100, 2);
    }

    // ==================== Methods (محسّنة) ====================

    /**
     * ✅ إضافة كمية
     */
    public function addStock(float $quantity, ?float $cost = null): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('الكمية يجب أن تكون أكبر من صفر');
        }

        $updates = [
            'quantity' => \DB::raw("quantity + {$quantity}"),
            'available_quantity' => \DB::raw("quantity + {$quantity} - COALESCE(reserved_quantity, 0)"),
            'updated_at' => now(),
        ];

        // ✅ تحديث متوسط التكلفة
        if ($cost !== null && $cost > 0) {
            $currentQty = (float) $this->quantity;
            $currentCost = (float) ($this->average_cost ?? 0);
            $newAvgCost = (($currentQty * $currentCost) + ($quantity * $cost)) / ($currentQty + $quantity);
            $updates['average_cost'] = round($newAvgCost, 2);
        }

        return \DB::table($this->table)
            ->where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->update($updates) > 0;
    }

    /**
     * ✅ خصم كمية
     */
    public function deductStock(float $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('الكمية يجب أن تكون أكبر من صفر');
        }

        if ($this->available_quantity < $quantity) {
            throw new \RuntimeException('الكمية المتاحة غير كافية');
        }

        return \DB::table($this->table)
            ->where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->where(\DB::raw('quantity - COALESCE(reserved_quantity, 0)'), '>=', $quantity)
            ->update([
                'quantity' => \DB::raw("quantity - {$quantity}"),
                'available_quantity' => \DB::raw("quantity - {$quantity} - COALESCE(reserved_quantity, 0)"),
                'last_sale_date' => now(),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ حجز كمية
     */
    public function reserve(float $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('الكمية يجب أن تكون أكبر من صفر');
        }

        if ($this->available_quantity < $quantity) {
            throw new \RuntimeException('الكمية المتاحة غير كافية للحجز');
        }

        return \DB::table($this->table)
            ->where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->where(\DB::raw('quantity - COALESCE(reserved_quantity, 0)'), '>=', $quantity)
            ->update([
                'reserved_quantity' => \DB::raw("COALESCE(reserved_quantity, 0) + {$quantity}"),
                'available_quantity' => \DB::raw("quantity - COALESCE(reserved_quantity, 0) - {$quantity}"),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ إلغاء حجز
     */
    public function release(float $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('الكمية يجب أن تكون أكبر من صفر');
        }

        $reserved = (float) ($this->reserved_quantity ?? 0);
        if ($reserved < $quantity) {
            throw new \RuntimeException('الكمية المحجوزة أقل من الكمية المطلوب إلغاؤها');
        }

        return \DB::table($this->table)
            ->where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->update([
                'reserved_quantity' => \DB::raw("GREATEST(0, COALESCE(reserved_quantity, 0) - {$quantity})"),
                'available_quantity' => \DB::raw("quantity - GREATEST(0, COALESCE(reserved_quantity, 0) - {$quantity})"),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ✅ تحديث من الجرد
     */
    public function updateFromStockCount(float $actualQuantity): bool
    {
        $variance = $actualQuantity - $this->quantity;

        return \DB::table($this->table)
            ->where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->update([
                'quantity' => $actualQuantity,
                'available_quantity' => max(0, $actualQuantity - ($this->reserved_quantity ?? 0)),
                'last_count_quantity' => $this->quantity,
                'last_count_date' => now(),
                'adjustment_total' => \DB::raw("COALESCE(adjustment_total, 0) + {$variance}"),
                'updated_at' => now(),
            ]) > 0;
    }
}