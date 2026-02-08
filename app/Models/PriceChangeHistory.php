<?php
// app/Models/PriceChangeHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'price_change_history';

    public $timestamps = false; // لأننا بنستخدم changed_at

    protected $fillable = [
        'product_id',
        'base_unit_id',
        'old_base_purchase_price',
        'new_base_purchase_price',
        'old_base_selling_price',
        'new_base_selling_price',
        'diff_percentage',
        'change_reason',
        'affected_selling_units',
        'selling_units_updated',
        'affected_units_details',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'old_base_purchase_price' => 'decimal:2',
        'new_base_purchase_price' => 'decimal:2',
        'old_base_selling_price' => 'decimal:2',
        'new_base_selling_price' => 'decimal:2',
        'diff_percentage' => 'decimal:2',
        'affected_selling_units' => 'integer',
        'selling_units_updated' => 'boolean',
        'affected_units_details' => 'array',
        'changed_at' => 'datetime',
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
     * المستخدم اللي غيّر السعر
     */
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ===== Scopes =====

    /**
     * ترتيب حسب التاريخ (الأحدث أولاً)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('changed_at', 'desc');
    }

    /**
     * التغييرات في فترة معينة
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('changed_at', [$from, $to]);
    }

    /**
     * التغييرات اللي تم تحديث وحداتها
     */
    public function scopeUpdated($query)
    {
        return $query->where('selling_units_updated', true);
    }

    // ===== Accessors =====

    /**
     * الفرق في سعر الشراء
     */
    public function getPurchasePriceDiffAttribute()
    {
        return $this->new_base_purchase_price - $this->old_base_purchase_price;
    }

    /**
     * الفرق في سعر البيع
     */
    public function getSellingPriceDiffAttribute()
    {
        return $this->new_base_selling_price - $this->old_base_selling_price;
    }

    /**
     * نسبة التغيير في سعر الشراء
     */
    public function getPurchaseDiffPercentageAttribute()
    {
        if ($this->old_base_purchase_price > 0) {
            return (($this->new_base_purchase_price - $this->old_base_purchase_price) / $this->old_base_purchase_price) * 100;
        }
        return 0;
    }

    /**
     * نسبة التغيير في سعر البيع
     */
    public function getSellingDiffPercentageAttribute()
    {
        if ($this->old_base_selling_price > 0) {
            return (($this->new_base_selling_price - $this->old_base_selling_price) / $this->old_base_selling_price) * 100;
        }
        return 0;
    }
}