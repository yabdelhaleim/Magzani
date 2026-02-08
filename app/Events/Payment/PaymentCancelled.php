<?php
namespace App\Events\Payment;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public string $cancelledBy;
    public ?string $reason;

    public function __construct(Payment $payment, ?string $reason = null, ?string $cancelledBy = null)
    {
        $this->payment = $payment;
        $this->reason = $reason;
        $this->cancelledBy = $cancelledBy ?? auth()->user()?->name ?? 'النظام';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payments'),
        ];
    }
}
