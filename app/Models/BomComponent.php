<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomComponent extends Model
{
    protected $fillable = [
        'manufacturing_cost_id',
        'component_product_id',
        'component_name',
        'quantity',
        'uom_id',
        'cost_per_uom',
        'component_category_id',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'cost_per_uom' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function manufacturingCost(): BelongsTo
    {
        return $this->belongsTo(ManufacturingCost::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComponentCategory::class, 'component_category_id');
    }

    /**
     * Calculate cost: quantity × cost_per_uom
     */
    public function calculateCost(): float
    {
        return (float) ($this->quantity * $this->cost_per_uom);
    }
}
