<?php
namespace App\Events\Stock;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $productId;
    public int $warehouseId;
    public int $oldQuantity;
    public int $newQuantity;
    public string $operation;
    public string $updatedBy;

    public function __construct(
        int $productId, 
        int $warehouseId, 
        int $oldQuantity, 
        int $newQuantity,
        string $operation = 'update',
        ?string $updatedBy = null
    ) {
        $this->productId = $productId;
        $this->warehouseId = $warehouseId;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
        $this->operation = $operation; // add, subtract, set
        $this->updatedBy = $updatedBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('stock-updates'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'product_id' => $this->productId,
            'warehouse_id' => $this->warehouseId,
            'old_quantity' => $this->oldQuantity,
            'new_quantity' => $this->newQuantity,
            'difference' => $this->newQuantity - $this->oldQuantity,
            'operation' => $this->operation,
            'updated_by' => $this->updatedBy,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}
