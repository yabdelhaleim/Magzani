<?php
namespace App\Notifications\Invoice;

use App\Models\SalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewSalesInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public SalesInvoice $invoice;

    public function __construct(SalesInvoice $invoice)
    {
        $this->invoice = $invoice;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'فاتورة مبيعات جديدة',
            'message' => "تم إنشاء فاتورة مبيعات رقم {$this->invoice->invoice_number}",
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name' => $this->invoice->customer->name,
            'total' => $this->invoice->total,
            'action_url' => route('sales.show', $this->invoice->id),
            'icon' => 'receipt',
            'type' => 'success',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'فاتورة مبيعات جديدة',
            'message' => "فاتورة رقم {$this->invoice->invoice_number} بقيمة {$this->invoice->total} جنيه",
            'invoice_id' => $this->invoice->id,
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('فاتورة مبيعات جديدة')
            ->greeting('مرحباً!')
            ->line("تم إنشاء فاتورة مبيعات جديدة رقم: {$this->invoice->invoice_number}")
            ->line("العميل: {$this->invoice->customer->name}")
            ->line("الإجمالي: {$this->invoice->total} جنيه")
            ->action('عرض الفاتورة', route('sales.show', $this->invoice->id))
            ->line('شكراً لاستخدامك نظامنا!');
    }
}
