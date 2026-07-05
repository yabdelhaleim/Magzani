<?php

namespace App\Notifications\Accounting;

use App\Models\SalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SalesInvoice $invoice,
        public int $daysOverdue
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("فاتورة متأخرة #{$this->invoice->invoice_number}")
            ->line("فاتورة العميل {$this->invoice->customer?->name} متأخرة {$this->daysOverdue} يوم.")
            ->line("المبلغ المتبقي: " . number_format((float) $this->invoice->total - (float) $this->invoice->paid, 2))
            ->action('عرض الفاتورة', url("/invoices/sales/{$this->invoice->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'overdue_invoice',
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name'  => $this->invoice->customer?->name,
            'days_overdue'   => $this->daysOverdue,
            'remaining'      => (float) $this->invoice->total - (float) $this->invoice->paid,
        ];
    }
}
