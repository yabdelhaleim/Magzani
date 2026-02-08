<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\SalesInvoiceConfirmed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateSalesInvoiceConfirmedCache
{
    public function handle(SalesInvoiceConfirmed $event): void
    {
        try {
            // مسح الـ Cache للتقارير
            Cache::forget('daily_sales_' . now()->format('Y-m-d'));
            Cache::forget('monthly_sales_' . now()->format('Y-m'));
            Cache::forget('dashboard_summary');

            Log::info('Sales Invoice Confirmed', [
                'invoice_id' => $event->invoice->id,
                'confirmed_by' => $event->confirmedBy,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update cache after invoice confirmation', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
