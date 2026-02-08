<?php
namespace App\Listeners\Invoice;

use App\Events\Invoice\SalesInvoiceCreated;
use App\Notifications\Invoice\NewSalesInvoiceNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendSalesInvoiceCreatedNotification
{
    public function handle(SalesInvoiceCreated $event): void
    {
        try {
            // إرسال إشعار للمدراء
            $admins = \App\Models\User::role('admin')->get();
            Notification::send($admins, new NewSalesInvoiceNotification($event->invoice));

            // تسجيل في اللوج
            Log::info('Sales Invoice Created', [
                'invoice_id' => $event->invoice->id,
                'invoice_number' => $event->invoice->invoice_number,
                'customer' => $event->invoice->customer->name,
                'total' => $event->totalAmount,
                'created_by' => $event->userName,
            ]);

            // إرسال بريد إلكتروني للعميل (اختياري)
            if ($event->invoice->customer->email) {
                // Mail::to($event->invoice->customer)->send(new InvoiceMail($event->invoice));
            }

        } catch (\Exception $e) {
            Log::error('Failed to send sales invoice notification', [
                'invoice_id' => $event->invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
