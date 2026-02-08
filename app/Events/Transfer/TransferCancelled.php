<?php
namespace App\Events\Transfer;

use App\Models\WarehouseTransfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WarehouseTransfer $transfer;
    public string $cancelledBy;
    public ?string $reason;

    public function __construct(WarehouseTransfer $transfer, ?string $reason = null, ?string $cancelledBy = null)
    {
        $this->transfer = $transfer;
        $this->reason = $reason;
        $this->cancelledBy = $cancelledBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transfers'),
        ];
    }
}
