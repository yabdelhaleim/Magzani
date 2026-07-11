<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BatchGenealogy — Gap 4.
 *
 * The M2M bridge: which raw-material batches went into which
 * finished-good batches, and how much. One row per (source → finished)
 * consumption event.
 *
 * The unique constraint `bg_source_finished_unique` ensures idempotency:
 * re-running recordGenealogyOnCompletion for the same MO cannot create
 * duplicate rows. The migration in 2026_07_08_000004 enforces this.
 *
 * Precision: decimal(15,4) — must match FinishedGoodBatch and
 * MaterialBatch so all downstream split math reconciles.
 */
class BatchGenealogy extends Model
{
    use HasFactory;

    protected $table = 'batch_genealogy';

    protected $fillable = [
        'source_material_batch_id',
        'finished_good_batch_id',
        'quantity_consumed',
        'source_unit_cost_snapshot',
        'consumed_at',
    ];

    protected $casts = [
        'quantity_consumed' => 'decimal:4',
        'source_unit_cost_snapshot' => 'decimal:4',
        'consumed_at' => 'datetime',
    ];

    public function sourceBatch(): BelongsTo
    {
        return $this->belongsTo(MaterialBatch::class, 'source_material_batch_id');
    }

    public function finishedBatch(): BelongsTo
    {
        return $this->belongsTo(FinishedGoodBatch::class, 'finished_good_batch_id');
    }

    /**
     * Total value contribution this raw batch gave to its finished batch
     * (quantity_consumed × source_unit_cost_snapshot). Decimal(15,4).
     */
    public function getContributionValueAttribute(): float
    {
        return round(
            ((float) $this->quantity_consumed) * ((float) $this->source_unit_cost_snapshot),
            4
        );
    }
}
