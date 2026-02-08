<?php
namespace App\Events\Return;

use App\Models\SalesReturn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesReturnProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SalesReturn $salesReturn;
    public string $processedBy;

    public function __construct(SalesReturn $salesReturn, ?string $processedBy = null)
    {
        $this->salesReturn = $salesReturn;
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
            'return_id' => $this->salesReturn->id,
            'return_number' => $this->salesReturn->return_number,
            'invoice_number' => $this->salesReturn->salesInvoice->invoice_number,
            'total' => $this->salesReturn->total,
            'processed_by' => $this->processedBy,
            // الحل: استخدام optional() أو null-safe operator
            'return_date' => $this->salesReturn->return_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
        ];
    }
}
