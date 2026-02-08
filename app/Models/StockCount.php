<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class StockCount extends Model
{
    protected $fillable = [
        'count_number',
        'warehouse_id',
        'count_date',
        'count_type',
        'status',
        'total_items',
        'items_counted',
        'discrepancies',
        'adjustments_applied',
        'adjustments_skipped',
        'notes',
        'started_at',
        'created_by',
        'completed_by',
        'cancelled_by',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'count_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_items' => 'integer',
        'items_counted' => 'integer',
        'discrepancies' => 'integer',
        'adjustments_applied' => 'integer',
        'adjustments_skipped' => 'integer',
    ];

    // ==================== Relationships ====================

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ==================== Scopes ====================

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'in_progress']);
    }

    public function scopeForWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('count_type', $type);
    }

    public function scopeDateRange(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('count_date', [$from, $to]);
    }

    public function scopeWithDiscrepancies(Builder $query): Builder
    {
        return $query->where('discrepancies', '>', 0);
    }

    // ==================== Accessors ====================

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_items == 0) {
            return 0;
        }
        
        return round(($this->items_counted / $this->total_items) * 100, 2);
    }

    public function getHasDiscrepanciesAttribute(): bool
    {
        return $this->discrepancies > 0;
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getCanStartAttribute(): bool
    {
        return $this->status === 'draft' && $this->total_items > 0;
    }

    public function getCanCompleteAttribute(): bool
    {
        return $this->status === 'in_progress' 
            && $this->items_counted === $this->total_items;
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => '📝 مسودة',
            'in_progress' => '⏳ جاري التنفيذ',
            'completed' => '✅ مكتمل',
            'cancelled' => '❌ ملغي',
            default => '❓ غير محدد',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->count_type) {
            'full' => '📦 جرد شامل',
            'partial' => '📋 جرد جزئي',
            'random' => '🎲 جرد عشوائي',
            default => '❓ غير محدد',
        };
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? now();
        $duration = $this->started_at->diff($end);

        if ($duration->days > 0) {
            return "{$duration->days} يوم، {$duration->h} ساعة";
        }

        if ($duration->h > 0) {
            return "{$duration->h} ساعة، {$duration->i} دقيقة";
        }

        return "{$duration->i} دقيقة";
    }

    // ==================== Methods ====================

    /**
     * التحقق من إمكانية تعديل الجرد
     */
    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * الحصول على العناصر المعلقة
     */
    public function getPendingItems()
    {
        return $this->items()
            ->where('status', 'pending')
            ->with('product:id,name,sku,barcode')
            ->get();
    }

    /**
     * الحصول على العناصر التي بها فروقات
     */
    public function getItemsWithVariance()
    {
        return $this->items()
            ->where('variance', '!=', 0)
            ->with('product:id,name,sku,barcode')
            ->orderByDesc(\DB::raw('ABS(variance)'))
            ->get();
    }

    /**
     * الحصول على العناصر المعتمدة
     */
    public function getApprovedItems()
    {
        return $this->items()
            ->where('adjustment_approved', true)
            ->with('product:id,name,sku,barcode')
            ->get();
    }

    // ==================== Boot ====================

    protected static function boot()
    {
        parent::boot();

        // عند الحذف، حذف العناصر المرتبطة
        static::deleting(function ($stockCount) {
            if ($stockCount->isForceDeleting()) {
                $stockCount->items()->delete();
            }
        });
    }
}