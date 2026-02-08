<?php
namespace App\Listeners\Stock;

use App\Events\Stock\StockLow;
use App\Services\SlackService;
use App\Notifications\Stock\LowStockNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendLowStockAlert
{
    public function __construct(
        private SlackService $slack
    ) {}

    public function handle(StockLow $event): void
    {
        try {
            // إرسال إشعار للمدراء والمخزنجية
            $users = \App\Models\User::role(['admin', 'warehouse_manager'])->get();
            
            Notification::send($users, new LowStockNotification(
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
