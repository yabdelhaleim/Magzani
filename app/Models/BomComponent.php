<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomComponent extends Model
{
    protected $fillable = [
        'manufacturing_cost_id',
        'component_name',
        'quantity',
        'length_cm',
        'width_cm',
        'thickness_cm',
        'volume_cm3',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'thickness_cm' => 'decimal:2',
        'volume_cm3' => 'decimal:4',
    ];

    public function manufacturingCost(): BelongsTo
    {
        return $this->belongsTo(ManufacturingCost::class);
    }

    public function calculateVolume(): float
    {
        return (float) ($this->length_cm * $this->width_cm * $this->thickness_cm * $this->quantity);
    }
}
