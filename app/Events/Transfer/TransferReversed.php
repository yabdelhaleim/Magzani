<?php
namespace App\Events\Transfer;

use App\Models\WarehouseTransfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferReversed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WarehouseTransfer $transfer;
    public string $reversedBy;
    public ?string $reason;

    public function __construct(WarehouseTransfer $transfer, ?string $reason = null, ?string $reversedBy = null)
    {
        $this->transfer = $transfer;
        $this->reason = $reason;
        $this->reversedBy = $reversedBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transfers'),
        ];
    }
}