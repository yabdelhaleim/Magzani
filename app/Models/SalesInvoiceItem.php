<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        // الفاتورة والمنتج
        'sales_invoice_id',
        'invoice_id', // للتوافق
        'product_id',
        'selling_unit_id',
        
        // الكميات
        'quantity',
        'base_quantity', // تم استبداله بـ quantity_in_base_unit
        'quantity_in_base_unit', // للتوافق
        
        // الوحدة
        'unit_code',
        'conversion_factor',
        
        // الأسعار
        'price',
        'unit_price', // للتوافق
        
        // الخصم
        'discount',
        'discount_percent',
        'discount_amount', // للتوافق
        
        // الضريبة
        'tax',
        'tax_rate',
        'tax_amount', // للتوافق
        
        // الإجماليات
        'subtotal',
        'total',
        
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'base_quantity' => 'decimal:3',
        'quantity_in_base_unit' => 'decimal:3',
        'conversion_factor' => 'decimal:4',
        'price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
    
    // للتوافق
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sellingUnit(): BelongsTo
    {
        return $this->belongsTo(ProductSellingUnit::class, 'selling_unit_id');
    }

    /* ===========================
     * 📊 ACCESSORS - محسّن
     * =========================== */

    public function getUnitNameAttribute(): string
    {
        return $this->sellingUnit?->unit_name 
            ?? $this->product?->base_unit_label 
            ?? 'وحدة';
    }

    public function getUnitLabelAttribute(): string
    {
        return $this->sellingUnit?->label ?? $this->unit_name;
    }

    public function getSubtotalAttribute(): float
    {
        // ✅ استخدام القيم المحفوظة إذا كانت موجودة
        if (isset($this->attributes['subtotal'])) {
            return (float) $this->attributes['subtotal'];
        }
        
        return round($this->quantity * $this->price, 2);
    }

    public function getNetAfterDiscountAttribute(): float
    {
        $subtotal = $this->subtotal;
        $discount = $this->discount ?? 0;
        
        return round($subtotal - $discount, 2);
    }

    /* ===========================
     * 🛠️ HELPER METHODS
     * =========================== */

    /**
     * ✅ حساب الكمية بالوحدة الأساسية
     */
    public function calculateBaseQuantity(): float
    {
        $factor = $this->sellingUnit?->conversion_factor 
            ?? $this->conversion_factor 
            ?? 1;
        
        return round($this->quantity * $factor, 3);
    }

    /**
     * ✅ حساب تفاصيل الصنف
     */
    public function getCalculatedDetails(): array
    {
        $subtotal = $this->quantity * $this->price;
        $discount = $this->discount ?? 0;
        $afterDiscount = $subtotal - $discount;
        $tax = $this->tax ?? 0;
        $total = $afterDiscount + $tax;
        
        return [
            'product_name' => $this->product?->name ?? '',
            'unit_name' => $this->unit_name,
            'quantity' => $this->quantity,
            'base_quantity' => $this->base_quantity ?? $this->calculateBaseQuantity(),
            'price' => $this->price,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'after_discount' => round($afterDiscount, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
        ];
    }

    /* ===========================
     * 📅 EVENTS
     * =========================== */

    protected static function boot()
    {
        parent::boot();

        // ✅ قبل الحفظ: حساب الكمية الأساسية تلقائياً
        static::saving(function ($item) {
            // حساب base_quantity إذا لم تكن محددة
            if (!$item->base_quantity && $item->selling_unit_id) {
                $item->base_quantity = $item->calculateBaseQuantity();
            }
            
            // للتوافق
            if (!$item->quantity_in_base_unit) {
                $item->quantity_in_base_unit = $item->base_quantity;
            }
            
            // حساب الإجماليات تلقائياً إذا لم تكن محددة
            if (!$item->subtotal) {
                $item->subtotal = round($item->quantity * $item->price, 2);
            }
            
            if (!isset($item->total)) {
                $discount = $item->discount ?? 0;
                $tax = $item->tax ?? 0;
                $item->total = round($item->subtotal - $discount + $tax, 2);
            }
        });
    }
}