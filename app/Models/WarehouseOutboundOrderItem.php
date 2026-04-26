<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseOutboundOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_order_id',
        'product_id',
        'requested_quantity',
        'approved_quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:3',
        'approved_quantity' => 'decimal:3',
    ];

    public function outboundOrder()
    {
        return $this->belongsTo(WarehouseOutboundOrder::class, 'outbound_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
