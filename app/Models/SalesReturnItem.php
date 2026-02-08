<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 🔄 SalesReturnItem Model - UPDATED
 * 
 * أصناف مرتجع المبيعات مع دعم الوحدات المتعددة
 */
class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sales_invoice_item_id',
        'product_id',
        'selling_unit_id', // 🆕 وحدة البيع
        'quantity_returned',
        'base_quantity_returned', // 🆕 الكمية المرتجعة بالوحدة الأساسية
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total',
        'item_condition',
        'return_reason',
        'notes',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:3',
        'base_quantity_returned' => 'decimal:3', // 🆕
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    /**
     * المرتجع
     */
    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    /**
     * صنف الفاتورة الأصلي
     */
    public function salesInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(SalesInvoiceItem::class);
    }

    /**
     * المنتج
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 🆕 وحدة البيع
     */
    public function sellingUnit(): BelongsTo
    {
        return $this->belongsTo(ProductSellingUnit::class, 'selling_unit_id');
    }

    /* ===========================
     * 📊 ACCESSORS
     * =========================== */

    /**
     * 🆕 الحصول على اسم الوحدة
     */
    public function getUnitNameAttribute(): string
    {
        return $this->sellingUnit->unit_name ?? ($this->product->base_unit_label ?? 'وحدة');
    }

    /**
     * 🆕 الحصول على تسمية الوحدة الكاملة
     */
    public function getUnitLabelAttribute(): string
    {
        return $this->sellingUnit->label ?? $this->unit_name;
    }

    /**
     * 🆕 معامل التحويل
     */
    public function getConversionFactorAttribute(): float
    {
        return $this->sellingUnit->conversion_factor ?? 1.0;
    }

    /**
     * الإجمالي الجزئي (قبل الخصم والضريبة)
     */
    public function getSubtotalAttribute(): float
    {
        return round($this->quantity_returned * $this->unit_price, 2);
    }

    /**
     * الصافي بعد الخصم
     */
    public function getNetAfterDiscountAttribute(): float
    {
        return round($this->subtotal - $this->discount_amount, 2);
    }

    /* ===========================
     * 🛠️ HELPER METHODS
     * =========================== */

    /**
     * 🆕 حساب الكمية المرتجعة بالوحدة الأساسية
     */
    public function calculateBaseQuantity(): float
    {
        if ($this->sellingUnit) {
            return $this->quantity_returned * $this->sellingUnit->conversion_factor;
        }
        
        return $this->quantity_returned;
    }

    /**
     * 🆕 تحديث الكمية الأساسية تلقائياً
     */
    public function updateBaseQuantity(): bool
    {
        $baseQty = $this->calculateBaseQuantity();
        return $this->update(['base_quantity_returned' => $baseQty]);
    }

    /**
     * حساب التفاصيل الكاملة للصنف المرتجع
     */
    public function getDetails(): array
    {
        return [
            'product_name' => $this->product->name ?? '',
            'unit_name' => $this->unit_name,
            'unit_label' => $this->unit_label,
            'quantity_returned' => $this->quantity_returned,
            'base_quantity_returned' => $this->base_quantity_returned ?? $this->calculateBaseQuantity(),
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'item_condition' => $this->item_condition,
            'return_reason' => $this->return_reason,
        ];
    }

    /* ===========================
     * 📅 EVENTS
     * =========================== */

    protected static function boot()
    {
        parent::boot();

        // قبل الحفظ: حساب الكمية الأساسية تلقائياً
        static::saving(function ($item) {
            if ($item->selling_unit_id && !$item->base_quantity_returned) {
                $item->base_quantity_returned = $item->calculateBaseQuantity();
            }
        });
    }
}