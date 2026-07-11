<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BatchPriceAdjustment — Gap 4.
 *
 * Audit + posting record for ONE price-adjustment event caused by a
 * supplier's late invoice. Strictly one row per `purchase_invoice_item`
 * to satisfy Q5 (each item ⇒ its own JE).
 *
 * Precision: decimal(15,4) across the board to match siblings.
 *
 * Variances:
 *   - `inventory_impact`: touches 1310 (raw stock + FG stock portion).
 *   - `cogs_impact`:       touches 5100 (sold-thru portion).
 *   - `fallback_used`:     when true, both impacts = 0 and the entire
 *                           diff was posted to 5160 (Gap 2 fallback).
 */
class BatchPriceAdjustment extends Model
{
    use HasFactory;

    protected $table = 'batch_price_adjustments';

    protected $fillable = [
        'purchase_invoice_item_id',
        'material_batch_id',
        'original_unit_cost',
        'new_unit_cost',
        'price_diff',
        'total_quantity_affected',
        'inventory_impact',
        'cogs_impact',
        'fallback_used',
        'fallback_reason',
        'journal_entry_id',
        'applied_by',
        'applied_at',
    ];

    protected $casts = [
        'original_unit_cost'        => 'decimal:4',
        'new_unit_cost'             => 'decimal:4',
        'price_diff'                => 'decimal:4',
        'total_quantity_affected'   => 'decimal:4',
        'inventory_impact'          => 'decimal:4',
        'cogs_impact'               => 'decimal:4',
        'fallback_used'             => 'boolean',
        'applied_at'                => 'datetime',
    ];

    public function purchaseInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoiceItem::class, 'purchase_invoice_item_id');
    }

    public function materialBatch(): BelongsTo
    {
        return $this->belongsTo(MaterialBatch::class, 'material_batch_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function appliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    /**
     * Net total the supplier billed more (or credited) for this batch.
     * Sign matches price_diff: positive ⇒ DR inventory/COGS, CR AP.
     */
    public function getNetImpactAttribute(): float
    {
        if ($this->fallback_used) {
            // When fallback fires, the entire diff lands on 5160.
            // We don't surface inventory/cogs here.
            return (float) $this->price_diff * (float) $this->total_quantity_affected;
        }

        return round(((float) $this->inventory_impact) + ((float) $this->cogs_impact), 4);
    }
}
