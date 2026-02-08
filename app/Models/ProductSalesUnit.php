<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany;

// class ProductSellingUnit extends Model
// {
//     use HasFactory;

//     protected $table = 'product_selling_units';

//     protected $fillable = [
//         'product_id',
//         'unit_name',
//         'unit_code',
//         'quantity_in_base_unit',
//         'barcode',
//         'is_default',
//         'is_active',
//         'display_order',
//     ];

//     protected $casts = [
//         'quantity_in_base_unit' => 'decimal:6',
//         'is_default' => 'boolean',
//         'is_active' => 'boolean',
//         'display_order' => 'integer',
//     ];

//     protected $appends = [
//         'label',
//         'conversion_factor',
//         'selling_price',
//         'purchase_price',
//         'is_base',
//         'unit',
//     ];

//     /* ===========================
//      * 🔗 RELATIONSHIPS
//      * =========================== */

//     public function product(): BelongsTo
//     {
//         return $this->belongsTo(Product::class);
//     }

//     public function salesInvoiceItems(): HasMany
//     {
//         return $this->hasMany(SalesInvoiceItem::class, 'selling_unit_id');
//     }

//     public function salesReturnItems(): HasMany
//     {
//         return $this->hasMany(SalesReturnItem::class, 'selling_unit_id');
//     }

//     /* ===========================
//      * 📊 ACCESSORS (Getters)
//      * =========================== */

//     public function getLabelAttribute(): string
//     {
//         if ($this->is_base) {
//             return $this->unit_name;
//         }
        
//         $baseUnit = $this->product->base_unit_label ?? 'وحدة';
//         return "{$this->unit_name} ({$this->quantity_in_base_unit} {$baseUnit})";
//     }

//     public function getConversionFactorAttribute(): float
//     {
//         return (float) $this->quantity_in_base_unit;
//     }

//     public function getIsBaseAttribute(): bool
//     {
//         return abs($this->quantity_in_base_unit - 1.0) < 0.0001;
//     }

//     public function getSellingPriceAttribute(): float
//     {
//         if (!$this->product) {
//             return 0.0;
//         }

//         $baseSellingPrice = $this->product->selling_price ?? 0;
//         return round($baseSellingPrice * $this->conversion_factor, 2);
//     }

//     public function getPurchasePriceAttribute(): float
//     {
//         if (!$this->product) {
//             return 0.0;
//         }

//         $basePurchasePrice = $this->product->purchase_price ?? 0;
//         return round($basePurchasePrice * $this->conversion_factor, 2);
//     }

//     public function getUnitAttribute(): string
//     {
//         return $this->unit_code ?? $this->unit_name;
//     }

//     /* ===========================
//      * 🔧 MUTATORS (Setters)
//      * =========================== */

//     public function setUnitNameAttribute($value): void
//     {
//         $this->attributes['unit_name'] = trim($value);
//     }

//     public function setUnitCodeAttribute($value): void
//     {
//         $this->attributes['unit_code'] = $value ? trim($value) : null;
//     }

//     public function setBarcodeAttribute($value): void
//     {
//         $this->attributes['barcode'] = $value ? trim($value) : null;
//     }

//     /* ===========================
//      * 🎯 SCOPES
//      * =========================== */

//     public function scopeActive($query)
//     {
//         return $query->where('is_active', true);
//     }

//     public function scopeInactive($query)
//     {
//         return $query->where('is_active', false);
//     }

//     public function scopeDefault($query)
//     {
//         return $query->where('is_default', true);
//     }

//     public function scopeBase($query)
//     {
//         return $query->whereBetween('quantity_in_base_unit', [0.9999, 1.0001]);
//     }

//     // 🔧 FIX: استخدام display_order فقط
//     public function scopeOrdered($query)
//     {
//         return $query->orderBy('display_order')->orderBy('quantity_in_base_unit');
//     }

//     public function scopeByBarcode($query, string $barcode)
//     {
//         return $query->where('barcode', $barcode);
//     }

//     public function scopeByCode($query, string $code)
//     {
//         return $query->where('unit_code', $code);
//     }

//     /* ===========================
//      * 🛠️ HELPER METHODS
//      * =========================== */

//     public function toBaseUnit(float $quantity): float
//     {
//         return round($quantity * $this->conversion_factor, 6);
//     }

//     public function fromBaseUnit(float $baseQuantity): float
//     {
//         if ($this->conversion_factor == 0) {
//             return 0;
//         }
        
//         return round($baseQuantity / $this->conversion_factor, 6);
//     }

//     public function calculateTotalPrice(float $quantity, string $priceType = 'selling'): float
//     {
//         $price = $priceType === 'selling' ? $this->selling_price : $this->purchase_price;
//         return round($quantity * $price, 2);
//     }

//     public function activate(): bool
//     {
//         return $this->update(['is_active' => true]);
//     }

//     public function deactivate(): bool
//     {
//         if ($this->is_default) {
//             return false;
//         }

//         return $this->update(['is_active' => false]);
//     }

//     public function setAsDefault(): bool
//     {
//         return \DB::transaction(function () {
//             static::where('product_id', $this->product_id)
//                 ->where('id', '!=', $this->id)
//                 ->update(['is_default' => false]);

//             return $this->update(['is_default' => true]);
//         });
//     }

//     public function getFullInfo(): array
//     {
//         return [
//             'id' => $this->id,
//             'product_id' => $this->product_id,
//             'product_name' => $this->product->name ?? '',
//             'unit_name' => $this->unit_name,
//             'unit_code' => $this->unit_code,
//             'label' => $this->label,
//             'conversion_factor' => $this->conversion_factor,
//             'is_base' => $this->is_base,
//             'is_default' => $this->is_default,
//             'is_active' => $this->is_active,
//             'selling_price' => $this->selling_price,
//             'purchase_price' => $this->purchase_price,
//             'barcode' => $this->barcode,
//             'display_order' => $this->display_order,
//         ];
//     }

//     /* ===========================
//      * 📅 EVENTS
//      * =========================== */

//     protected static function boot()
//     {
//         parent::boot();

//         static::creating(function ($unit) {
//             $existingUnits = static::where('product_id', $unit->product_id)->count();
//             if ($existingUnits === 0) {
//                 $unit->is_default = true;
//             }
//         });

//         static::deleted(function ($unit) {
//             if ($unit->is_default) {
//                 $firstUnit = static::where('product_id', $unit->product_id)
//                     ->active()
//                     ->ordered()
//                     ->first();
                
//                 if ($firstUnit) {
//                     $firstUnit->setAsDefault();
//                 }
//             }
//         });
//     }
// }