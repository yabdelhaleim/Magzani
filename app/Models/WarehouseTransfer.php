<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class WarehouseTransfer extends Model
{
    use HasFactory, SoftDeletes;

    // ==================== Configuration ====================
    
    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'expected_date',
        'received_date',
        'status',
        'reversed_at',
        'notes',
        'created_by',
        'updated_by',
        'confirmed_by',
        'received_by',
        'confirmed_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'reversed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
    ];

    // ==================== Relationships ====================

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseTransferItem::class, 'warehouse_transfer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // ==================== Query Scopes ====================

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where('status', 'received');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeReversed(Builder $query): Builder
    {
        return $query->where('status', 'reversed');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeFromWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('from_warehouse_id', $warehouseId);
    }

    public function scopeToWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('to_warehouse_id', $warehouseId);
    }

    public function scopeByWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where(function ($q) use ($warehouseId) {
            $q->where('from_warehouse_id', $warehouseId)
              ->orWhere('to_warehouse_id', $warehouseId);
        });
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('transfer_date', [$startDate, $endDate]);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('transfer_number', 'like', "%{$search}%");
    }

    /**
     * ✅ جلب التحويلات مع الإحصائيات - استعلام محسّن
     * استخدام الأعمدة الموجودة فعلياً في الجدول
     */
    public function scopeWithStats(Builder $query): Builder
    {
        return $query->withCount('items as items_count')
            ->addSelect([
                // ✅ مجموع الكميات المرسلة
                'total_quantity' => WarehouseTransferItem::selectRaw('COALESCE(SUM(quantity_sent), 0)')
                    ->whereColumn('warehouse_transfer_id', 'warehouse_transfers.id'),
                
                // ✅ مجموع الكميات المستلمة
                'total_received' => WarehouseTransferItem::selectRaw('COALESCE(SUM(quantity_received), 0)')
                    ->whereColumn('warehouse_transfer_id', 'warehouse_transfers.id'),
                
                // ✅ مجموع الفرق (calculated column)
                'total_difference' => WarehouseTransferItem::selectRaw('COALESCE(SUM(quantity_sent - quantity_received), 0)')
                    ->whereColumn('warehouse_transfer_id', 'warehouse_transfers.id'),
            ]);
    }

    /**
     * ✅ جلب التحويلات مع كل العلاقات - Eager Loading محسّن
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with([
            'fromWarehouse:id,name,code',
            'toWarehouse:id,name,code',
            'createdBy:id,name',
            'confirmedBy:id,name',
            'receivedBy:id,name',
        ]);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('transfer_date', 'desc')
                     ->orderBy('created_at', 'desc');
    }

    /**
     * ✅ Scope شامل للقوائم
     */
    public function scopeForListing(Builder $query): Builder
    {
        return $query->withStats()
            ->withRelations()
            ->latest();
    }

    // ==================== Accessors ====================

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'مسودة',
            'pending' => 'قيد الانتظار',
            'in_transit' => 'قيد النقل',
            'received' => 'مستلم',
            'cancelled' => 'ملغي',
            'reversed' => 'معكوس',
            default => 'غير محدد'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'in_transit' => 'blue',
            'received' => 'green',
            'cancelled' => 'red',
            'reversed' => 'orange',
            default => 'gray'
        };
    }

    public function getDaysInTransitAttribute(): ?int
    {
        if ($this->status !== 'in_transit' || !$this->transfer_date) {
            return null;
        }

        return $this->transfer_date->diffInDays(now());
    }

    public function getIsLateAttribute(): bool
    {
        if (!$this->expected_date || $this->status === 'received') {
            return false;
        }

        return now()->gt($this->expected_date);
    }

    // ==================== Helper Methods ====================

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInTransit(): bool
    {
        return $this->status === 'in_transit';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'in_transit']);
    }

    public function canBeReversed(): bool
    {
        return $this->status === 'received';
    }

    public function isConfirmed(): bool
    {
        return !is_null($this->confirmed_at);
    }
}