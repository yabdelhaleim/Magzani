<?php

namespace App\Events\Invoice;

use App\Models\PurchaseInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseInvoiceConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PurchaseInvoice $invoice;
    public string $confirmedBy;

    public function __construct(PurchaseInvoice $invoice, ?string $confirmedBy = null)
    {
        $this->invoice = $invoice;
        $this->confirmedBy = $confirmedBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('invoices'),
        ];
    }
}
