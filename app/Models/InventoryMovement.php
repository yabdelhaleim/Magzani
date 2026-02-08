<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'movement_number',
        'warehouse_id',
        'product_id',
        'movement_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'notes',
        'reference_type',
        'reference_id',
        'movement_date',
        'created_by',
    ];

    protected $casts = [
        'quantity_before' => 'decimal:3',
        'quantity_change' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'movement_date' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // ==================== Scopes ====================

    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('movement_date', [$from, $to]);
    }

    public function scopeIncreases($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    public function scopeDecreases($query)
    {
        return $query->where('quantity_change', '<', 0);
    }

    // ==================== Accessors ====================

    public function getMovementTypeLabelAttribute(): string
    {
        return match($this->movement_type) {
            'initial_stock' => '🏁 رصيد افتتاحي',
            'purchase' => '📥 إضافة/شراء',
            'sale' => '📤 صرف/بيع',
            'transfer_in' => '⬅️ تحويل وارد',
            'transfer_out' => '➡️ تحويل صادر',
            'adjustment_in' => '📈 تسوية جرد (زيادة)',
            'adjustment_out' => '📉 تسوية جرد (نقص)',
            'return_in' => '↩️ مرتجع وارد',
            'return_out' => '↪️ مرتجع صادر',
            'damage' => '💥 تالف',
            'expired' => '⏰ منتهي الصلاحية',
            default => '❓ غير محدد',
        };
    }

    public function getIsIncreaseAttribute(): bool
    {
        return $this->quantity_change > 0;
    }

    public function getIsDecreaseAttribute(): bool
    {
        return $this->quantity_change < 0;
    }
}