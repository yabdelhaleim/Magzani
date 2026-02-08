<?php
namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $paymentType = $this->payment->payable_type === 'App\Models\Customer' 
            ? 'دفعة من عميل' 
            : 'دفعة لمورد';

        return [
            'title' => 'دفعة جديدة',
            'message' => "{$paymentType} بقيمة {$this->payment->amount} جنيه",
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'payment_type' => $paymentType,
            'action_url' => route('accounting.payments'),
            'icon' => 'dollar-sign',
            'type' => 'success',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $paymentType = $this->payment->payable_type === 'App\Models\Customer' 
            ? 'من عميل' 
            : 'لمورد';

        return (new MailMessage)
            ->subject('دفعة جديدة')
            ->line("تم تسجيل دفعة {$paymentType}")
            ->line("المبلغ: {$this->payment->amount} جنيه")
            ->line("طريقة الدفع: {$this->payment->payment_method}")
            ->action('عرض التفاصيل', route('accounting.payments'));
    }
}
