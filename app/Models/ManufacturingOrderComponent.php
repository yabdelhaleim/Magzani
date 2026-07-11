<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturingOrderComponent extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_order_components';

    protected $fillable = [
        'order_id',
        'material_batch_id',
        'component_name',
        'component_type',
        'quantity',
        'uom_id',
        'unit_cost',
        'total_cost',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class, 'order_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MaterialBatch::class, 'material_batch_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedCostAttribute(): string
    {
        return number_format($this->total_cost, 2);
    }

    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
    }

    public function getProductIdAttribute(): ?int
    {
        if ($this->material_batch_id) {
            $batch = $this->batch ?: MaterialBatch::find($this->material_batch_id);
            return $batch ? $batch->product_id : null;
        }
        return null;
    }
}