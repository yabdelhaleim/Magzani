<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
        // Additional costs
        'waste_cost',
        'labor_cost',
        'nails_cost',
        'tips_cost',
        'transport_cost',
        'fumigation_cost',
        'profit_margin',
        'profit_amount',
        'status',
        'warehouse_id',
        'notes',
        'produced_at',
        'created_by',
        'updated_by',
        'completed_by',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'cost_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'waste_cost' => 'decimal:4',
        'labor_cost' => 'decimal:4',
        'nails_cost' => 'decimal:4',
        'tips_cost' => 'decimal:4',
        'transport_cost' => 'decimal:4',
        'fumigation_cost' => 'decimal:4',
        'profit_margin' => 'decimal:2',
        'profit_amount' => 'decimal:4',
        'selling_price_per_unit' => 'decimal:4',
        'produced_at' => 'datetime',
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

    public function components(): HasMany
    {
        return $this->hasMany(ManufacturingOrderComponent::class, 'order_id');
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

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', ManufacturingOrder::class);
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
        return in_array($this->status, ['draft', 'confirmed']);
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

        if ($lastOrder && preg_match('/MO-' . $year . '-(\d+)/', $lastOrder->order_number, $matches)) {
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
}