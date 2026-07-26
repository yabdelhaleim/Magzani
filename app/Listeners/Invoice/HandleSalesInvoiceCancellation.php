<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\SalesInvoiceCancelled;
use App\Notifications\Invoice\InvoiceCancelledNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleSalesInvoiceCancellation
{
    public function __construct(
        private NotificationDeliveryService $notifications
    ) {}

    public function handle(SalesInvoiceCancelled $event): void
    {
        try {
            $this->notifications->sendToAdmins(
                new InvoiceCancelledNotification($event->invoice, $event->reason)
            );

            // مسح الـ Cache
            Cache::forget('daily_sales_'.now()->format('Y-m-d'));
            Cache::forget('dashboard_summary');

            // تسجيل في اللوج
            Log::warning('Sales Invoice Cancelled', [
                'invoice_id' => $event->invoice->id,
                'invoice_number' => $event->invoice->invoice_number,
                'cancelled_by' => $event->cancelledBy,
                'reason' => $event->reason,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle invoice cancellation', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
