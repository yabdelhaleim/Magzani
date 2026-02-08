<?php

namespace App\Listeners\Return;

use App\Events\Return\PurchaseReturnProcessed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePurchaseReturnProcessed
{
    public function handle(PurchaseReturnProcessed $event): void
    {
        try {
            Cache::forget('inventory_report_all');
            Cache::forget('dashboard_summary');

            Log::info('Purchase Return Processed', [
                'return_id' => $event->purchaseReturn->id,
                'return_number' => $event->purchaseReturn->return_number,
                'total' => $event->purchaseReturn->total,
                'processed_by' => $event->processedBy,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle purchase return', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
