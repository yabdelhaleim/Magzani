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

    protected $fillable = [
        'product_id',
        'product_name',
        'price_per_cubic_meter',
        'total_volume_cm3',
        'total_volume_m3',
        'material_cost',
        'labor_cost',
        'nails_hardware_cost',
        'transportation_cost',
        'tips_misc_cost',
        'fumigation_cost',
        'additional_costs_total',
        'total_cost',
        'profit_percentage',
        'profit_amount',
        'final_price',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price_per_cubic_meter' => 'decimal:2',
        'total_volume_cm3' => 'decimal:4',
        'total_volume_m3' => 'decimal:6',
        'material_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'nails_hardware_cost' => 'decimal:2',
        'transportation_cost' => 'decimal:2',
        'tips_misc_cost' => 'decimal:2',
        'fumigation_cost' => 'decimal:2',
        'additional_costs_total' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
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
}
