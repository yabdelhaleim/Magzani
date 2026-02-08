<?php


namespace App\Listeners\Invoice;

use App\Events\Invoice\PurchaseInvoiceCancelled;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePurchaseInvoiceCancellation
{
    public function handle(PurchaseInvoiceCancelled $event): void
    {
        try {
            // مسح الـ Cache
            Cache::forget('inventory_report_all');
            Cache::forget('dashboard_summary');

            Log::warning('Purchase Invoice Cancelled', [
                'invoice_id' => $event->invoice->id,
                'cancelled_by' => $event->cancelledBy,
                'reason' => $event->reason,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle purchase invoice cancellation', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
