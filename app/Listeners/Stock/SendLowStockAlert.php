<?php

namespace App\Listeners\Stock;

use App\Events\Stock\StockLow;
use App\Notifications\Stock\LowStockNotification;
use App\Services\NotificationDeliveryService;
use App\Services\SlackService;
use Illuminate\Support\Facades\Log;

class SendLowStockAlert
{
    public function __construct(
        private SlackService $slack,
        private NotificationDeliveryService $notifications
    ) {}

    public function handle(StockLow $event): void
    {
        try {
            $this->notifications->sendToAdmins(new LowStockNotification(
                $event->product,
                $event->warehouse,
                $event->currentQuantity,
                $event->minimumStock,
                $event->severity
            ));

            // إرسال لـ Slack في حالة critical
            if ($event->severity === 'critical' && $this->slack->isEnabled()) {
                $this->slack->notifyLowStock(
                    $event->product,
                    $event->warehouse,
                    $event->currentQuantity,
                    $event->minimumStock,
                    $event->severity
                );
            }

            // تسجيل
            $logMethod = $event->severity === 'critical' ? 'critical' : 'warning';

            Log::$logMethod('Low Stock Alert', [
                'product_id' => $event->product->id,
                'product_name' => $event->product->name,
                'warehouse' => $event->warehouse->name,
                'current_quantity' => $event->currentQuantity,
                'minimum_stock' => $event->minimumStock,
                'severity' => $event->severity,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send low stock alert', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
