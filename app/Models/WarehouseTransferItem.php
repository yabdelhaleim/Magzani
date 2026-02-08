<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class WarehouseTransferItem extends Model
{
    use HasFactory;

    // ==================== Configuration ====================
    
    protected $fillable = [
        'warehouse_transfer_id',
        'product_id',
        'quantity_sent',
        'quantity_received',
        'notes',
        'discrepancy_reason',
    ];

    protected $casts = [
        'quantity_sent' => 'decimal:3',
        'quantity_received' => 'decimal:3',
        'quantity_difference' => 'decimal:3', // computed column
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(WarehouseTransfer::class, 'warehouse_transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ==================== Query Scopes ====================

    public function scopeByTransfer(Builder $query, int $transferId): Builder
    {
        return $query->where('warehouse_transfer_id', $transferId);
    }

    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * ✅ العناصر التي بها فرق في الكمية
     */
    public function scopeWithDiscrepancy(Builder $query): Builder
    {
        return $query->whereRaw('quantity_sent != quantity_received');
    }

    /**
     * ✅ العناصر المستلمة بالكامل
     */
    public function scopeFullyReceived(Builder $query): Builder
    {
        return $query->whereRaw('quantity_sent = quantity_received');
    }

    /**
     * ✅ العناصر المستلمة جزئياً
     */
    public function scopePartiallyReceived(Builder $query): Builder
    {
        return $query->where('quantity_received', '>', 0)
                     ->whereRaw('quantity_sent > quantity_received');
    }

    /**
     * ✅ العناصر غير المستلمة
     */
    public function scopeNotReceived(Builder $query): Builder
    {
        return $query->where('quantity_received', 0);
    }

    /**
     * ✅ جلب العناصر مع بيانات المنتج - Eager Loading محسّن
     */
    public function scopeWithProduct(Builder $query): Builder
    {
        return $query->with([
            'product:id,name,code,sku,unit',
        ]);
    }

    /**
     * ✅ جلب العناصر مع التحويل
     */
    public function scopeWithTransfer(Builder $query): Builder
    {
        return $query->with([
            'transfer:id,transfer_number,status,transfer_date',
        ]);
    }

    /**
     * ✅ Scope شامل للقوائم
     */
    public function scopeForListing(Builder $query): Builder
    {
        return $query->withProduct()
                     ->orderBy('id');
    }

    // ==================== Accessors ====================

    /**
     * نسبة الاستلام (%)
     */
    public function getReceivePercentageAttribute(): float
    {
        if ($this->quantity_sent == 0) {
            return 0;
        }

        return round(($this->quantity_received / $this->quantity_sent) * 100, 2);
    }

    /**
     * حالة الاستلام (نصي)
     */
    public function getReceiveStatusAttribute(): string
    {
        if ($this->quantity_received == 0) {
            return 'غير مستلم';
        }

        if ($this->quantity_sent == $this->quantity_received) {
            return 'مستلم بالكامل';
        }

        if ($this->quantity_received < $this->quantity_sent) {
            return 'مستلم جزئياً';
        }

        return 'زيادة في الاستلام';
    }

    /**
     * لون حالة الاستلام
     */
    public function getReceiveStatusColorAttribute(): string
    {
        if ($this->quantity_received == 0) {
            return 'red';
        }

        if ($this->quantity_sent == $this->quantity_received) {
            return 'green';
        }

        if ($this->quantity_received < $this->quantity_sent) {
            return 'yellow';
        }

        return 'orange'; // زيادة
    }

    /**
     * هل يوجد فرق؟
     */
    public function getHasDiscrepancyAttribute(): bool
    {
        return $this->quantity_difference != 0;
    }

    /**
     * نوع الفرق
     */
    public function getDiscrepancyTypeAttribute(): ?string
    {
        if ($this->quantity_difference == 0) {
            return null;
        }

        if ($this->quantity_difference > 0) {
            return 'نقص'; // كمية مرسلة أكبر من المستلمة
        }

        return 'زيادة'; // كمية مستلمة أكبر من المرسلة
    }

    // ==================== Helper Methods ====================

    public function isFullyReceived(): bool
    {
        return $this->quantity_sent == $this->quantity_received;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->quantity_received > 0 
            && $this->quantity_received < $this->quantity_sent;
    }

    public function isNotReceived(): bool
    {
        return $this->quantity_received == 0;
    }

    public function hasDiscrepancy(): bool
    {
        return $this->quantity_difference != 0;
    }

    public function isShortage(): bool
    {
        return $this->quantity_difference > 0; // نقص
    }

    public function isOverage(): bool
    {
        return $this->quantity_difference < 0; // زيادة
    }

    /**
     * الحصول على تفاصيل العنصر
     */
    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product?->name,
            'product_code' => $this->product?->code,
            'quantity_sent' => (float) $this->quantity_sent,
            'quantity_received' => (float) $this->quantity_received,
            'quantity_difference' => (float) $this->quantity_difference,
            'receive_percentage' => $this->receive_percentage,
            'receive_status' => $this->receive_status,
            'has_discrepancy' => $this->has_discrepancy,
            'discrepancy_type' => $this->discrepancy_type,
            'discrepancy_reason' => $this->discrepancy_reason,
        ];
    }
}