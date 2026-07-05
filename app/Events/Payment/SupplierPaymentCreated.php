<?php

namespace App\Events\Payment;

use App\Models\SupplierPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierPaymentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SupplierPayment $payment;
    public string $recordedBy;

    public function __construct(SupplierPayment $payment, ?string $recordedBy = null)
    {
        $this->payment = $payment;
        $this->recordedBy = $recordedBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payments'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'supplier_id' => $this->payment->supplier_id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'recorded_by' => $this->recordedBy,
            'payment_date' => $this->payment->payment_date ? \Carbon\Carbon::parse($this->payment->payment_date)->format('Y-m-d') : now()->format('Y-m-d'),
        ];
    }
}
