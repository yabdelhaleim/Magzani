<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaterialDispensing extends Model
{
    use HasFactory;

    protected $table = 'material_dispensings';

    protected $fillable = [
        'material_batch_id',
        'manufacturing_order_id',
        'quantity_taken',
        'source_unit_cost',
        'dispensing_method',
        'dispensed_at',
        'notes',
    ];

    protected $casts = [
        'quantity_taken' => 'decimal:4',
        'source_unit_cost' => 'decimal:4',
        'dispensed_at' => 'date',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MaterialBatch::class, 'material_batch_id');
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class, 'manufacturing_order_id');
    }

    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(InventoryMovement::class, 'reference');
    }

    /* ===========================
     * 📊 GAP 4 — DISPENSE SNAPSHOT
     * =========================== */

    /**
     * Cost at the moment of dispensing (= batch.unit_cost snapshot).
     * Falls back to the current batch unit_cost for legacy rows.
     */
    public function getCostAtConsumptionAttribute(): float
    {
        return (float) ($this->source_unit_cost ?? $this->batch?->unit_cost ?? 0);
    }

    /**
     * Total value consumed in this dispensing event.
     * Decimal(15,4) on the multiplication keeps split math reconciling.
     */
    public function getTotalCostAtConsumptionAttribute(): float
    {
        return round($this->quantity_taken * $this->getCostAtConsumptionAttribute(), 4);
    }
}
