<?php
// app/Models/ProductBaseUnit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBaseUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_base_units';

    protected $fillable = [
        'product_id',
        'product_code',
        'base_unit_type',
        'base_unit_code',
        'base_unit_label',
        'base_unit_weight_kg',
        'base_purchase_price',
        'base_selling_price',
        'profit_margin',
        'is_active',
        'auto_update_selling_units',
        'effective_from',
        'effective_to',
        'notes',
        'currency',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'base_unit_weight_kg' => 'decimal:6',
        'base_purchase_price' => 'decimal:2',
        'base_selling_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'is_active' => 'boolean',
        'auto_update_selling_units' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    // ===== العلاقات =====

    /**
     * العلاقة مع المنتج
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * العلاقة مع وحدات البيع
     */
    public function sellingUnits()
    {
        return $this->hasMany(ProductSellingUnit::class, 'base_unit_id');
    }

    /**
     * العلاقة مع وحدات البيع النشطة فقط
     */
    public function activeSellingUnits()
    {
        return $this->hasMany(ProductSellingUnit::class, 'base_unit_id')
                    ->where('is_active', true)
                    ->orderBy('display_order');
    }

    /**
     * العلاقة مع سجل التغييرات
     */
    public function priceHistory()
    {
        return $this->hasMany(PriceChangeHistory::class, 'base_unit_id');
    }

    /**
     * المستخدم اللي أنشأ السجل
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * المستخدم اللي عدّل السجل
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===== Scopes =====

    /**
     * فقط الوحدات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * الوحدات السارية حالياً
     */
    public function scopeCurrent($query)
    {
        $now = now()->toDateString();
        return $query->where('effective_from', '<=', $now)
                    ->where(function($q) use ($now) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $now);
                    });
    }

    /**
     * حسب نوع الوحدة
     */
    public function scopeByType($query, $type)
    {
        return $query->where('base_unit_type', $type);
    }

    // ===== Accessors & Mutators =====

    /**
     * حساب هامش الربح تلقائياً
     */
    public function calculateProfitMargin()
    {
        if ($this->base_purchase_price > 0) {
            return (($this->base_selling_price - $this->base_purchase_price) / $this->base_purchase_price) * 100;
        }
        return 0;
    }

    /**
     * تحديث أسعار وحدات البيع تلقائياً
     */
    public function updateSellingUnitsPrices()
    {
        if (!$this->auto_update_selling_units) {
            return false;
        }

        $updated = 0;
        foreach ($this->sellingUnits as $sellingUnit) {
            if ($sellingUnit->auto_calculate_price) {
                $sellingUnit->calculatePrices();
                $sellingUnit->save();
                $updated++;
            }
        }

        return $updated;
    }
}