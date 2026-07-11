<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManufacturingCost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'manufacturing_costs';

    protected $fillable = [
        'product_id',
        'product_name',
        'material_cost',
        'total_cost',
        'profit_percentage',
        'profit_amount',
        'final_price',
        'status',
        'notes',
        // Gap 2 — Standard costing fields (BOM-level).
        'standard_material_cost',
        'standard_labor_cost',
        'standard_overhead_cost',
        'standard_cost',
        'standard_cost_effective_from',
        'standard_cost_effective_to',
        'standard_cost_updated_by',
        'standard_cost_updated_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'material_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        // Gap 2
        'standard_material_cost' => 'decimal:4',
        'standard_labor_cost' => 'decimal:4',
        'standard_overhead_cost' => 'decimal:4',
        'standard_cost' => 'decimal:4',
        'standard_cost_effective_from' => 'date',
        'standard_cost_effective_to' => 'date',
        'standard_cost_updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(BomComponent::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === 'confirmed';
    }

    /* ===========================
     * 📊 GAP 2 — STANDARD COST ACCESSORS
     * =========================== */

    /**
     * The currently-effective total standard cost per unit.
     * Returns 0 if the standard has not been set yet.
     */
    public function getEffectiveStandardCostAttribute(): float
    {
        $material = (float) $this->standard_material_cost;
        $labor = (float) $this->standard_labor_cost;
        $overhead = (float) $this->standard_overhead_cost;

        $computed = $material + $labor + $overhead;

        // Prefer the persisted total if it was set, fall back to component sum.
        $stored = (float) $this->standard_cost;

        return $stored > 0 ? $stored : round($computed, 4);
    }

    public function getHasStandardCostAttribute(): bool
    {
        return $this->getEffectiveStandardCostAttribute() > 0;
    }

    /**
     * Audit relation — the user who last revised the standard cost.
     */
    public function standardCostUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'standard_cost_updated_by');
    }
}
