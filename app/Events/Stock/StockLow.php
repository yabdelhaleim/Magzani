<?php

namespace App\Events\Stock;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLow implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product;
    public Warehouse $warehouse;
    public int $currentQuantity;
    public int $minimumStock;
    public string $severity;

    public function __construct(Product $product, Warehouse $warehouse, int $currentQuantity, int $minimumStock)
    {
        $this->product = $product;
        $this->warehouse = $warehouse;
        $this->currentQuantity = $currentQuantity;
        $this->minimumStock = $minimumStock;
        
        // تحديد مستوى الخطورة
        $percentage = ($currentQuantity / $minimumStock) * 100;
        $this->severity = match (true) {
            $currentQuantity == 0 => 'critical',
            $percentage <= 25 => 'high',
            $percentage <= 50 => 'medium',
            default => 'low'
        };
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('stock-alerts'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'warehouse_name' => $this->warehouse->name,
            'current_quantity' => $this->currentQuantity,
            'minimum_stock' => $this->minimumStock,
            'severity' => $this->severity,
            'shortage' => $this->minimumStock - $this->currentQuantity,
        ];
    }
}

