<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FinishedGoodBatch — Gap 4.
 *
 * A "batch" for a manufactured (finished) product. Symmetric to
 * MaterialBatch but lives in the FG side of the genealogy graph.
 *
 * Generated automatically when a ManufacturingOrder completes, by
 * BatchGenealogyService::recordGenealogyOnCompletion().
 */
class FinishedGoodBatch extends Model
{
    use HasFactory;

    protected $table = 'finished_good_batches';

    protected $fillable = [
        'batch_code',
        'product_id',
        'warehouse_id',
        'manufacturing_order_id',
        'quantity',
        'remaining_qty',
        'unit_cost',
        'standard_unit_cost',
        'produced_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'remaining_qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'standard_unit_cost' => 'decimal:4',
        'produced_at' => 'date',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class, 'manufacturing_order_id');
    }

    public function genealogyLinks(): HasMany
    {
        return $this->hasMany(BatchGenealogy::class, 'finished_good_batch_id');
    }

    /* ===========================
     * 📊 ACCESSORS
     * =========================== */

    public function getIsFullySoldAttribute(): bool
    {
        return (float) $this->remaining_qty <= 0.0001;
    }

    public function getIsInStockAttribute(): bool
    {
        return (float) $this->remaining_qty > 0.0001;
    }

    /**
     * Quantity that left inventory (sold / dispensed / etc.).
     */
    public function getSoldQuantityAttribute(): float
    {
        return round(((float) $this->quantity) - ((float) $this->remaining_qty), 4);
    }

    /**
     * Ratio of sold quantity to total produced (0..1).
     * Used by BatchImpactSplitService when the actual sales invoice
     * linkage is unavailable — fallback to size-weighted share.
     */
    public function getSoldRatioAttribute(): float
    {
        $qty = (float) $this->quantity;
        if ($qty <= 0) {
            return 0.0;
        }
        return round($this->getSoldQuantityAttribute() / $qty, 6);
    }
}
