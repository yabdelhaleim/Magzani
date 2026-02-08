<?php

namespace App\Listeners\Return;

use App\Events\Return\SalesReturnProcessed;
use App\Notifications\Return\SalesReturnNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class HandleSalesReturnProcessed
{
    public function handle(SalesReturnProcessed $event): void
    {
        try {
            // إرسال إشعار للمدراء
            $admins = \App\Models\User::role('admin')->get();
            Notification::send($admins, new SalesReturnNotification($event->salesReturn));

            // مسح الـ Cache
            Cache::forget('inventory_report_all');
            Cache::forget('daily_sales_' . now()->format('Y-m-d'));

            Log::info('Sales Return Processed', [
                'return_id' => $event->salesReturn->id,
                'return_number' => $event->salesReturn->return_number,
                'invoice_number' => $event->salesReturn->salesInvoice->invoice_number,
                'total' => $event->salesReturn->total,
                'processed_by' => $event->processedBy,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle sales return', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
