<?php
namespace App\Notifications\Invoice;

use App\Models\SalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public SalesInvoice $invoice;
    public ?string $reason;

    public function __construct(SalesInvoice $invoice, ?string $reason = null)
    {
        $this->invoice = $invoice;
        $this->reason = $reason;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'تم إلغاء فاتورة مبيعات',
            'message' => "تم إلغاء الفاتورة رقم {$this->invoice->invoice_number}",
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'reason' => $this->reason,
            'action_url' => route('sales.show', $this->invoice->id),
            'icon' => 'x-circle',
            'type' => 'warning',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => 'إلغاء فاتورة',
            'message' => "تم إلغاء الفاتورة {$this->invoice->invoice_number}",
        ];
    }
}

