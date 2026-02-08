<?php
namespace App\Events\Transfer;

use App\Models\WarehouseTransfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WarehouseTransfer $transfer;
    public string $initiatedBy;

    public function __construct(WarehouseTransfer $transfer, ?string $initiatedBy = null)
    {
        $this->transfer = $transfer;
        $this->initiatedBy = $initiatedBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transfers'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'transfer_id' => $this->transfer->id,
            'transfer_number' => $this->transfer->transfer_number,
            'from_warehouse' => $this->transfer->fromWarehouse->name,
            'to_warehouse' => $this->transfer->toWarehouse->name,
            'items_count' => $this->transfer->items->count(),
            'initiated_by' => $this->initiatedBy,
            'status' => $this->transfer->status,
        ];
    }
}
