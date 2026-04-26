<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturingOrderComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'component_name',   // legacy field for backward compatibility
        'component_type',
        'quantity',
        'unit',             // legacy
        'thickness_cm',
        'width_cm',
        'length_cm',
        'volume_cm3',
        'price_per_cubic_meter',
        'unit_cost',        // legacy
        'total_cost',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'thickness_cm' => 'decimal:4',
        'width_cm' => 'decimal:4',
        'length_cm' => 'decimal:4',
        'volume_cm3' => 'decimal:4',
        'price_per_cubic_meter' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function order(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ===========================
     * 📊 ACCESSORS
     * =========================== */

    public function getFormattedCostAttribute(): string
    {
        return number_format($this->total_cost, 2);
    }

    /* ===========================
     * 🛠️ HELPER METHODS
     * =========================== */

    /**
     * Calculate total cost from quantity and unit cost
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
    }
}