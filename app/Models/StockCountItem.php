<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id',
        'product_id',
        'system_quantity',
        'actual_quantity',
        'variance',
        'status',
        'notes',
        'adjustment_approved',
        'approval_notes',
        'counted_by',
        'approved_by',
        'counted_at',
        'approved_at',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'variance' => 'decimal:3',
        'adjustment_approved' => 'boolean',
        'counted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==================== Scopes ====================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCounted(Builder $query): Builder
    {
        return $query->where('status', 'counted');
    }

    public function scopeAdjusted(Builder $query): Builder
    {
        return $query->where('status', 'adjusted');
    }

    public function scopeSkipped(Builder $query): Builder
    {
        return $query->where('status', 'skipped');
    }

    public function scopeWithVariance(Builder $query): Builder
    {
        return $query->where('variance', '!=', 0);
    }

    public function scopeWithoutVariance(Builder $query): Builder
    {
        return $query->where('variance', '=', 0);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('adjustment_approved', true);
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('variance', '!=', 0)
                     ->where('adjustment_approved', false)
                     ->whereIn('status', ['counted', 'adjusted']);
    }

    public function scopeSurplus(Builder $query): Builder
    {
        return $query->where('variance', '>', 0);
    }

    public function scopeShortage(Builder $query): Builder
    {
        return $query->where('variance', '<', 0);
    }

    public function scopeOrderByVariance(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy(\DB::raw('ABS(variance)'), $direction);
    }

    // ==================== Accessors ====================

    public function getHasVarianceAttribute(): bool
    {
        return $this->variance != 0;
    }

    public function getVarianceTypeAttribute(): string
    {
        if ($this->variance > 0) {
            return 'surplus'; // فائض
        }
        
        if ($this->variance < 0) {
            return 'shortage'; // عجز
        }
        
        return 'match'; // مطابق
    }

    public function getVarianceLabelAttribute(): string
    {
        return match($this->variance_type) {
            'surplus' => '📈 فائض',
            'shortage' => '📉 عجز',
            'match' => '✅ مطابق',
            default => '❓',
        };
    }

    public function getVariancePercentageAttribute(): float
    {
        if ($this->system_quantity == 0) {
            return 0;
        }

        return round(($this->variance / $this->system_quantity) * 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => '⏳ لم يتم الجرد',
            'counted' => '📊 تم الجرد',
            'adjusted' => '✅ تم التسوية',
            'skipped' => '⏭️ تم التخطي',
            default => '❓ غير محدد',
        };
    }

    public function getApprovalStatusLabelAttribute(): string
    {
        if ($this->variance == 0) {
            return 'لا يحتاج موافقة';
        }

        if ($this->adjustment_approved) {
            return '✅ معتمد';
        }

        if ($this->status === 'skipped') {
            return '⏭️ تم التخطي';
        }

        return '⏳ في انتظار الموافقة';
    }

    public function getVarianceAbsAttribute(): float
    {
        return abs($this->variance);
    }

    // ==================== Methods ====================

    /**
     * هل يمكن جرد هذا العنصر؟
     */
    public function canBeCount(): bool
    {
        return $this->status === 'pending' 
            && $this->stockCount->status === 'in_progress';
    }

    /**
     * هل يحتاج موافقة؟
     */
    public function needsApproval(): bool
    {
        return $this->variance != 0 
            && !$this->adjustment_approved
            && in_array($this->status, ['counted', 'adjusted']);
    }

    /**
     * هل يمكن الموافقة عليه؟
     */
    public function canBeApproved(): bool
    {
        return $this->variance != 0 
            && !$this->adjustment_approved
            && in_array($this->status, ['counted', 'adjusted'])
            && in_array($this->stockCount->status, ['in_progress', 'completed']);
    }

    /**
     * هل تم تطبيق التسوية؟
     */
    public function isAdjusted(): bool
    {
        return $this->status === 'adjusted';
    }

    /**
     * هل تم تخطيه؟
     */
    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }
}