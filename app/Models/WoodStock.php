<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WoodStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'product_id',
        'purchase_reference',
        'length_cm',
        'width_cm',
        'thickness_cm',
        'quantity',
        'volume_cm3',
        'unit_cost',
        'total_cost',
        'notes',
        'received_at',
    ];

    protected $casts = [
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'thickness_cm' => 'decimal:2',
        'quantity' => 'integer',
        'volume_cm3' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'received_at' => 'date',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function woodDispensings(): HasMany
    {
        return $this->hasMany(WoodDispensing::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', WoodStock::class);
    }

    /* ===========================
     * 📊 ACCESSORS
     * =========================== */

    public function getVolumeM3TotalAttribute(): float
    {
        return round($this->volume_cm3 / 1000000, 4);
    }

    public function getVolumeM2TotalAttribute(): float
    {
        if ($this->thickness_cm <= 0) return 0;
        return round($this->volume_cm3 / $this->thickness_cm / 10000, 4);
    }

    public function getDispensedCm3Attribute(): float
    {
        return (float) $this->woodDispensings()->sum('volume_cm3_taken');
    }

    public function getRemainingCm3Attribute(): float
    {
        return round($this->volume_cm3 - $this->dispensed_cm3, 4);
    }

    public function getRemainingM3Attribute(): float
    {
        return round($this->remaining_cm3 / 1000000, 4);
    }

    public function getRemainingM2Attribute(): float
    {
        if ($this->thickness_cm <= 0) return 0;
        return round($this->remaining_cm3 / $this->thickness_cm / 10000, 4);
    }

    /* ===========================
     * 🎯 SCOPES
     * =========================== */

    public function scopeWithStock($query)
    {
        // remaining_cm3 = volume_cm3 - (sum of woodDispensings)
        return $query->whereRaw('volume_cm3 > (SELECT COALESCE(SUM(volume_cm3_taken), 0) FROM wood_dispensings WHERE wood_dispensings.wood_stock_id = wood_stocks.id)');
    }

    /* ===========================
     * 🛠️ EVENTS
     * =========================== */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($woodStock) {
            $woodStock->volume_cm3 = $woodStock->length_cm * $woodStock->width_cm * $woodStock->thickness_cm * $woodStock->quantity;
            
            if ($woodStock->unit_cost > 0) {
                // total_cost = (volume_cm3 / 1,000,000) * unit_cost (price_per_m3)
                $woodStock->total_cost = ($woodStock->volume_cm3 / 1000000) * $woodStock->unit_cost;
            }
        });
        
        static::updating(function ($woodStock) {
            // Recalculate just in case dimensions change
            $woodStock->volume_cm3 = $woodStock->length_cm * $woodStock->width_cm * $woodStock->thickness_cm * $woodStock->quantity;
            
            if ($woodStock->unit_cost > 0) {
                $woodStock->total_cost = ($woodStock->volume_cm3 / 1000000) * $woodStock->unit_cost;
            }
        });
    }
}
