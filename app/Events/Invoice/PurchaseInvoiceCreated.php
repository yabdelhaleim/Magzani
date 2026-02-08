<?php
namespace App\Events\Invoice;

use App\Models\PurchaseInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseInvoiceCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PurchaseInvoice $invoice;
    public string $userName;
    public float $totalAmount;

    public function __construct(PurchaseInvoice $invoice, ?string $userName = null)
    {
        $this->invoice = $invoice;
        $this->userName = $userName ?? auth()->user()?->name ?? 'النظام';
        $this->totalAmount = $invoice->total;
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
            'supplier_name' => $this->invoice->supplier->name,
            'total' => $this->totalAmount,
            'created_by' => $this->userName,
            'created_at' => $this->invoice->created_at->diffForHumans(),
        ];
    }
}
