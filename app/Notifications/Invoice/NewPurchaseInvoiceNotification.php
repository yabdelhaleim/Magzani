<?php
namespace App\Notifications\Invoice;

use App\Models\PurchaseInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPurchaseInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public PurchaseInvoice $invoice;

    public function __construct(PurchaseInvoice $invoice)
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
            'title' => 'فاتورة مشتريات جديدة',
            'message' => "تم إنشاء فاتورة مشتريات رقم {$this->invoice->invoice_number}",
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'supplier_name' => $this->invoice->supplier->name,
            'total' => $this->invoice->total,
            'action_url' => route('invoices.purchases.show', $this->invoice->id),
            'icon' => 'shopping-cart',
            'type' => 'info',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('فاتورة مشتريات جديدة')
            ->line("تم إنشاء فاتورة مشتريات رقم: {$this->invoice->invoice_number}")
            ->line("المورد: {$this->invoice->supplier->name}")
            ->line("الإجمالي: {$this->invoice->total} جنيه")
            ->action('عرض الفاتورة', route('invoices.purchases.show', $this->invoice->id));
    }
}

