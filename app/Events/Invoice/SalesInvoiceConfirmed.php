<?php

namespace App\Events\Invoice;

use App\Models\SalesInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesInvoiceConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SalesInvoice $invoice;
    public string $confirmedBy;

    public function __construct(SalesInvoice $invoice, ?string $confirmedBy = null)
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
