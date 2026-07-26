<?php

namespace App\Listeners\Return;

use App\Events\Return\PurchaseReturnProcessed;
use App\Notifications\Return\PurchaseReturnNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePurchaseReturnProcessed
{
    public function __construct(
        private NotificationDeliveryService $notifications
    ) {}

    public function handle(PurchaseReturnProcessed $event): void
    {
        try {
            $this->notifications->sendToAdmins(
                new PurchaseReturnNotification($event->purchaseReturn)
            );

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
