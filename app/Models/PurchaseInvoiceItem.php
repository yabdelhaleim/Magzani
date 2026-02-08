<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_invoice_id',
        'product_id',
        'purchase_unit_id', // ✅ إضافة دعم وحدات الشراء
        'quantity',
        'base_quantity', // ✅ الكمية بالوحدة الأساسية
        'unit_code',
        'conversion_factor',
        'cost',
        'unit_cost', // للتوافق
        'discount',
        'discount_percent',
        'discount_amount',
        'tax',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'base_quantity' => 'decimal:3',
        'conversion_factor' => 'decimal:4',
        'cost' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(ProductSellingUnit::class, 'purchase_unit_id');
    }
    
    // ==================== Accessors ====================
    
    public function getSubtotalAttribute(): float
    {
        if (isset($this->attributes['subtotal'])) {
            return (float) $this->attributes['subtotal'];
        }
        
        return round($this->quantity * $this->cost, 2);
    }
    
    // ==================== Methods ====================
    
    public function calculateBaseQuantity(): float
    {
        $factor = $this->purchaseUnit?->conversion_factor 
            ?? $this->conversion_factor 
            ?? 1;
        
        return round($this->quantity * $factor, 3);
    }
    
    // ==================== Events ====================
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if (!$item->base_quantity && $item->purchase_unit_id) {
                $item->base_quantity = $item->calculateBaseQuantity();
            }
            
            if (!$item->subtotal) {
                $item->subtotal = round($item->quantity * $item->cost, 2);
            }
            
            if (!isset($item->total)) {
                $discount = $item->discount ?? 0;
                $tax = $item->tax ?? 0;
                $item->total = round($item->subtotal - $discount + $tax, 2);
            }
        });
    }
}