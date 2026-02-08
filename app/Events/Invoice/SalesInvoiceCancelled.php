<?php
namespace App\Events\Invoice;

use App\Models\SalesInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesInvoiceCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SalesInvoice $invoice;
    public string $cancelledBy;
    public ?string $reason;

    public function __construct(SalesInvoice $invoice, ?string $reason = null, ?string $cancelledBy = null)
    {
        $this->invoice = $invoice;
        $this->reason = $reason;
        $this->cancelledBy = $cancelledBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('invoices'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'cancelled_by' => $this->cancelledBy,
            'reason' => $this->reason,
            'cancelled_at' => now()->diffForHumans(),
        ];
    }
}
