<?php
namespace App\Events\Return;

use App\Models\PurchaseReturn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseReturnProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PurchaseReturn $purchaseReturn;
    public string $processedBy;

    public function __construct(PurchaseReturn $purchaseReturn, ?string $processedBy = null)
    {
        $this->purchaseReturn = $purchaseReturn;
        $this->processedBy = $processedBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('returns'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'return_id' => $this->purchaseReturn->id,
            'return_number' => $this->purchaseReturn->return_number,
            'invoice_number' => $this->purchaseReturn->purchaseInvoice->invoice_number,
            'total' => $this->purchaseReturn->total,
            'processed_by' => $this->processedBy,
            'return_date' => $this->purchaseReturn->return_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
        ];
    }
}