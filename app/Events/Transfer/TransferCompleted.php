<?php

namespace App\Events\Transfer;

use App\Models\WarehouseTransfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WarehouseTransfer $transfer;
    public string $completedBy;

    public function __construct(WarehouseTransfer $transfer, ?string $completedBy = null)
    {
        $this->transfer = $transfer;
        $this->completedBy = $completedBy ?? auth()->user()?->name ?? 'النظام';
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
            'completed_by' => $this->completedBy,
            'completed_at' => $this->transfer->completed_at?->diffForHumans(),
        ];
    }
}
