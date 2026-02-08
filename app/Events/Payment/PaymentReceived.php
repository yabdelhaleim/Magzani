<?php

namespace App\Events\Payment;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public string $recordedBy;
    public string $paymentType;

    public function __construct(Payment $payment, ?string $recordedBy = null)
    {
        $this->payment = $payment;
        $this->recordedBy = $recordedBy ?? auth()->user()?->name ?? 'النظام';
        $this->paymentType = $this->payment->payable_type === 'App\Models\Customer' 
            ? 'دفعة من عميل' 
            : 'دفعة لمورد';
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
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'payment_type' => $this->paymentType,
            'recorded_by' => $this->recordedBy,
            'payment_date' => $this->payment->payment_date->format('Y-m-d'),
        ];
    }
}
