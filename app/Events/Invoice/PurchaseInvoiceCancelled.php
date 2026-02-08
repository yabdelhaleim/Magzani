<?php

namespace App\Events\Invoice;

use App\Models\PurchaseInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseInvoiceCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PurchaseInvoice $invoice;
    public string $cancelledBy;
    public ?string $reason;

    public function __construct(PurchaseInvoice $invoice, ?string $reason = null, ?string $cancelledBy = null)
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
}
