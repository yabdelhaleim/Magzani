<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovement extends Model
{
    // ✅ جميع الحقول المطلوبة
    protected $fillable = [
        'movement_number',
        'warehouse_id',
        'product_id',
        'movement_type',
        'from_warehouse_id',
        'to_warehouse_id',
        
        // ✅ الكميات الأربعة
        'quantity',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        
        // ✅ التكاليف والأسعار
        'unit_cost',
        'unit_price',
        'total_cost',
        'total_price',
        
        // ✅ المراجع
        'reference_type',
        'reference_id',
        'purchase_invoice_id',
        'sales_invoice_id',
        'transfer_id',
        
        // ✅ معلومات إضافية
        'batch_number',
        'expiry_date',
        'movement_date',
        'notes',
        'reason',
        'created_by',
        'archived',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'quantity_change' => 'decimal:3',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
        'movement_date' => 'datetime',
        'expiry_date' => 'date',
        'archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== Scopes ====================

    public function scopeForWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('movement_type', $type);
    }

    public function scopeBetweenDates(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('movement_date', [$from, $to]);
    }

    public function scopeIncreases(Builder $query): Builder
    {
        return $query->where('quantity_change', '>', 0);
    }

    public function scopeDecreases(Builder $query): Builder
    {
        return $query->where('quantity_change', '<', 0);
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('archived', false);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('archived', true);
    }

    // ✅ Scope شامل للعرض
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with([
            'warehouse:id,name,code',
            'product:id,name,code,sku',
            'creator:id,name',
        ]);
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
            'adjustment' => '⚙️ تسوية جرد',
            'return_in' => '↩️ مرتجع وارد',
            'return_out' => '↪️ مرتجع صادر',
            'return_from_transfer' => '🔄 عكس تحويل (وارد)',
            'transfer_reversed' => '🔄 عكس تحويل (صادر)',
            'damage' => '💥 تالف',
            'expired' => '⏰ منتهي الصلاحية',
            'production' => '🏭 إنتاج',
            'consumption' => '🔧 استهلاك',
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

    public function getAbsoluteQuantityAttribute(): float
    {
        return abs($this->quantity_change);
    }

    public function getDirectionAttribute(): string
    {
        return $this->quantity_change > 0 ? 'in' : 'out';
    }

    public function getDirectionLabelAttribute(): string
    {
        return $this->quantity_change > 0 ? 'وارد' : 'صادر';
    }

    public function getDirectionColorAttribute(): string
    {
        return $this->quantity_change > 0 ? 'green' : 'red';
    }

    // ==================== Helper Methods ====================

    public function isInbound(): bool
    {
        return $this->quantity_change > 0;
    }

    public function isOutbound(): bool
    {
        return $this->quantity_change < 0;
    }

    public function isTransfer(): bool
    {
        return in_array($this->movement_type, ['transfer_in', 'transfer_out', 'return_from_transfer', 'transfer_reversed']);
    }

    public function isPurchase(): bool
    {
        return $this->movement_type === 'purchase';
    }

    public function isSale(): bool
    {
        return $this->movement_type === 'sale';
    }

    public function isAdjustment(): bool
    {
        return $this->movement_type === 'adjustment';
    }

    /**
     * ✅ الحصول على تفاصيل كاملة
     */
    public function getFullDetails(): array
    {
        return [
            'id' => $this->id,
            'movement_number' => $this->movement_number,
            'movement_type' => $this->movement_type,
            'movement_type_label' => $this->movement_type_label,
            'warehouse_name' => $this->warehouse?->name,
            'product_name' => $this->product?->name,
            'quantity' => (float) $this->quantity,
            'quantity_change' => (float) $this->quantity_change,
            'quantity_before' => (float) $this->quantity_before,
            'quantity_after' => (float) $this->quantity_after,
            'unit_cost' => (float) $this->unit_cost,
            'total_cost' => (float) $this->total_cost,
            'movement_date' => $this->movement_date,
            'direction' => $this->direction,
            'direction_label' => $this->direction_label,
            'notes' => $this->notes,
        ];
    }
}