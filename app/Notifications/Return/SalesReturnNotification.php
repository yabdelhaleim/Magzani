<?php
namespace App\Notifications\Return;

use App\Models\SalesReturn;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesReturnNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public SalesReturn $salesReturn;

    public function __construct(SalesReturn $salesReturn)
    {
        $this->salesReturn = $salesReturn;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'مرتجع مبيعات جديد',
            'message' => "تم معالجة مرتجع رقم {$this->salesReturn->return_number}",
            'return_id' => $this->salesReturn->id,
            'return_number' => $this->salesReturn->return_number,
            'invoice_number' => $this->salesReturn->salesInvoice->invoice_number,
            'total' => $this->salesReturn->total,
            'action_url' => route('sales-returns.index'),
            'icon' => 'rotate-ccw',
            'type' => 'warning',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('مرتجع مبيعات جديد')
            ->line("تم معالجة مرتجع مبيعات رقم: {$this->salesReturn->return_number}")
            ->line("الفاتورة الأصلية: {$this->salesReturn->salesInvoice->invoice_number}")
            ->line("قيمة المرتجع: {$this->salesReturn->total} جنيه")
            ->action('عرض المرتجعات', route('sales-returns.index'));
    }
}
