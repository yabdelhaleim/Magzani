<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaterialBatch extends Model
{
    use HasFactory;

    protected $table = 'material_batches';

    protected $fillable = [
        'batch_code',
        'product_id',
        'warehouse_id',
        'supplier_id',
        'uom_id',
        'quantity',
        'remaining_qty',
        'unit_cost',
        'original_unit_cost',
        'original_unit_cost_locked_at',
        'purchase_reference',
        'received_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'remaining_qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'original_unit_cost' => 'decimal:4',
        'original_unit_cost_locked_at' => 'datetime',
        'received_at' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function dispensings(): HasMany
    {
        return $this->hasMany(MaterialDispensing::class, 'material_batch_id');
    }

    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(InventoryMovement::class, 'reference');
    }

    public function attributes(): MorphMany
    {
        return $this->morphMany(ProductAttribute::class, 'attributable');
    }

    /* ===========================
     * 📊 GAP 4 — BATCH TRACKING
     * =========================== */

    public function genealogyLinks(): HasMany
    {
        return $this->hasMany(BatchGenealogy::class, 'source_material_batch_id');
    }

    public function purchaseLinks(): HasMany
    {
        return $this->hasMany(MaterialBatchPurchaseLink::class, 'material_batch_id');
    }

    public function priceAdjustments(): HasMany
    {
        return $this->hasMany(BatchPriceAdjustment::class, 'material_batch_id');
    }

    /**
     * The price that was effective when this batch was received. Falls back
     * to the current `unit_cost` for legacy rows that predate the
     * snapshot field.
     */
    public function getOriginalPriceSnapshotAttribute(): float
    {
        return (float) ($this->original_unit_cost ?? $this->unit_cost ?? 0);
    }

    /**
     * Has any of this batch been consumed into a finished-good batch?
     */
    public function getHasGenealogyAttribute(): bool
    {
        return $this->genealogyLinks()->exists();
    }

    public function scopeWithStock($query)
    {
        return $query->where('remaining_qty', '>', 0);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('batch_code', $code);
    }
}
