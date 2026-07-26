<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\PurchaseInvoiceCreated;
use App\Notifications\Invoice\NewPurchaseInvoiceNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Support\Facades\Log;

class SendPurchaseInvoiceCreatedNotification
{
    public function __construct(
        private NotificationDeliveryService $notifications
    ) {}

    public function handle(PurchaseInvoiceCreated $event): void
    {
        try {
            $this->notifications->sendToAdmins(
                new NewPurchaseInvoiceNotification($event->invoice)
            );

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
