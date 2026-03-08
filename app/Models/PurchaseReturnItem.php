<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'purchase_invoice_item_id',
        'product_id',
        'quantity_returned',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total',
        'item_condition',
        'return_reason',
        'notes',
    ];

    // ==================== Relationships ====================

    public function return()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseInvoiceItem()
    {
        return $this->belongsTo(\App\Models\PurchaseInvoiceItem::class);
    }
}
