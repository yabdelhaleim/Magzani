<?php
// app/Models/ProductSellingUnit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSellingUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_selling_units';

    protected $fillable = [
        'product_id',
        'base_unit_id',
        'unit_code',
        'unit_label',
        'conversion_factor',
        'quantity_in_base_unit',
        'unit_purchase_price',
        'unit_selling_price',
        'auto_calculate_price',
        'barcode',
        'is_default',
        'is_active',
        'display_order',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:6',
        'quantity_in_base_unit' => 'decimal:6',
        'unit_purchase_price' => 'decimal:2',
        'unit_selling_price' => 'decimal:2',
        'auto_calculate_price' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
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
     * العلاقة مع الوحدة الأساسية
     */
    public function baseUnit()
    {
        return $this->belongsTo(ProductBaseUnit::class, 'base_unit_id');
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
     * الوحدة الافتراضية
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * ترتيب العرض
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('unit_label');
    }

    // ===== Methods =====

    /**
     * حساب الأسعار من الوحدة الأساسية
     */
    public function calculatePrices()
    {
        if (!$this->baseUnit || !$this->auto_calculate_price) {
            return false;
        }

        // حساب سعر الشراء
        $this->unit_purchase_price = $this->baseUnit->base_purchase_price * $this->conversion_factor;

        // حساب سعر البيع
        $this->unit_selling_price = $this->baseUnit->base_selling_price * $this->conversion_factor;

        return true;
    }

    /**
     * حساب معامل التحويل من الوحدة الأساسية
     */
    public function calculateConversionFactor()
    {
        if (!$this->baseUnit || !$this->baseUnit->base_unit_weight_kg) {
            return 1;
        }

        // مثال: شيكارة 50 كجم من طن (1000 كجم) = 50 ÷ 1000 = 0.05
        if ($this->baseUnit->base_unit_type === 'weight') {
            return $this->quantity_in_base_unit / $this->baseUnit->base_unit_weight_kg;
        }

        // للوحدات الأخرى (قطعة، لتر...)
        return $this->quantity_in_base_unit;
    }

    /**
     * عدد الوحدات في الوحدة الأساسية
     */
    public function getUnitsPerBaseAttribute()
    {
        if ($this->conversion_factor > 0) {
            return 1 / $this->conversion_factor;
        }
        return 0;
    }
}