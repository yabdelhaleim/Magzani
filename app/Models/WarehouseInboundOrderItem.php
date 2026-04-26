<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInboundOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_order_id',
        'product_id',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function inboundOrder()
    {
        return $this->belongsTo(WarehouseInboundOrder::class, 'inbound_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
