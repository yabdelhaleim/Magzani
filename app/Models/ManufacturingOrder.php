<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManufacturingOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'product_id',
        'product_name',
        'quantity_produced',
        'cost_per_unit',
        'total_cost',
        'selling_price_per_unit',
        'labor_cost', // kept labor_cost in db if not dropped
        'profit_margin',
        'profit_amount',
        'status',
        'warehouse_id',
        'customer_id',
        'notes',
        'produced_at',
        'standard_cost_at_completion',
        'actual_cost_at_completion',
        'total_variance',
        'variance_type',
        'material_variance',
        'labor_overhead_variance',
        'variance_posted_at',
        'variance_journal_entry_id',
        'cost_locked_at',
        'created_by',
        'updated_by',
        'completed_by',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'cost_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'labor_cost' => 'decimal:4',
        'profit_margin' => 'decimal:2',
        'profit_amount' => 'decimal:4',
        'selling_price_per_unit' => 'decimal:4',
        'produced_at' => 'datetime',
        'standard_cost_at_completion' => 'decimal:4',
        'actual_cost_at_completion' => 'decimal:4',
        'total_variance' => 'decimal:4',
        'material_variance' => 'decimal:4',
        'labor_overhead_variance' => 'decimal:4',
        'variance_posted_at' => 'datetime',
        'cost_locked_at' => 'datetime',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(ManufacturingOrderComponent::class, 'order_id');
    }

    public function extraCosts(): HasMany
    {
        return $this->hasMany(ManufacturingOrderExtraCost::class, 'manufacturing_order_id');
    }

    public function materialDispensings(): HasMany
    {
        return $this->hasMany(MaterialDispensing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function woodDispensings(): HasMany
    {
        return $this->hasMany(WoodDispensing::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', ManufacturingOrder::class);
    }

    /**
     * Gap 2 — link to the variance journal entry (account 5160), if any.
     */
    public function varianceJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'variance_journal_entry_id');
    }

    /* ===========================
     * 📊 ACCESSORS
     * =========================== */

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === 'confirmed';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanConfirmAttribute(): bool
    {
        return $this->status === 'draft' && $this->components()->count() > 0;
    }

    public function getCanCompleteAttribute(): bool
    {
        return $this->status === 'confirmed' && $this->quantity_produced > 0;
    }

    /* ===========================
     * 🎯 SCOPES
     * =========================== */

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
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
        return $query->whereIn('status', ['draft', 'confirmed']);
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('product_name', 'like', "%{$search}%");
        });
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /* ===========================
     * 🛠️ HELPER METHODS
     * =========================== */

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber(): string
    {
        $year = now()->format('Y');

        // Get the last order number for this year
        $lastOrder = self::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder && preg_match('/MO-'.$year.'-(\d+)/', $lastOrder->order_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('MO-%s-%04d', $year, $newNumber);
    }

    /**
     * Get the total components cost for this order
     */
    public function getComponentsTotalCost(): float
    {
        return (float) $this->components()->sum('total_cost');
    }

    /**
     * Validate that the order is ready to be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === 'draft'
            && $this->components()->count() > 0
            && $this->quantity_produced > 0
            && $this->cost_per_unit > 0
            && $this->selling_price_per_unit >= $this->cost_per_unit;
    }

    /**
     * Validate that the order is ready to be completed (add to inventory)
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'confirmed'
            && $this->quantity_produced > 0;
    }

    /* ===========================
     * 🔄 DYNAMIC ACCESSORS FOR STOCK SERVICE
     * =========================== */

    public function getSourceWarehouseIdAttribute(): ?int
    {
        return $this->warehouse_id;
    }

    public function getDestinationWarehouseIdAttribute(): ?int
    {
        return $this->warehouse_id;
    }

    public function getQuantityAttribute(): float
    {
        return (float) $this->quantity_produced;
    }

    /* ===========================
     * 📊 GAP 2 — VARIANCE ACCESSORS
     * =========================== */

    public function getHasVarianceAttribute(): bool
    {
        return $this->variance_journal_entry_id !== null;
    }

    public function getIsFavorableVarianceAttribute(): bool
    {
        return $this->variance_type === 'favorable';
    }

    public function getIsUnfavorableVarianceAttribute(): bool
    {
        return $this->variance_type === 'unfavorable';
    }

    public function getIsVarianceLockedAttribute(): bool
    {
        return $this->cost_locked_at !== null;
    }

    /**
     * Scope: only completed orders that carry a computed variance record.
     */
    public function scopeWithVariance(Builder $query): Builder
    {
        return $query->whereNotNull('total_variance');
    }

    public function scopeFavorableVariance(Builder $query): Builder
    {
        return $query->where('variance_type', 'favorable');
    }

    public function scopeUnfavorableVariance(Builder $query): Builder
    {
        return $query->where('variance_type', 'unfavorable');
    }
}
