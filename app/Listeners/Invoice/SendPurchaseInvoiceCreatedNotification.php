<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\PurchaseInvoiceCreated;
use App\Notifications\Invoice\NewPurchaseInvoiceNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendPurchaseInvoiceCreatedNotification
{
    public function handle(PurchaseInvoiceCreated $event): void
    {
        try {
            // إرسال إشعار للمدراء والمشتريات
            $users = \App\Models\User::role(['admin', 'purchaser'])->get();
            Notification::send($users, new NewPurchaseInvoiceNotification($event->invoice));

            Log::info('Purchase Invoice Created', [
                'invoice_id' => $event->invoice->id,
                'invoice_number' => $event->invoice->invoice_number,
                'supplier' => $event->invoice->supplier->name,
                'total' => $event->totalAmount,
                'created_by' => $event->userName,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send purchase invoice notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

