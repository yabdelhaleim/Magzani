<?php
namespace App\Listeners\Transfer;

use App\Events\Transfer\TransferCompleted;
use App\Notifications\Transfer\TransferCompletedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class HandleTransferCompleted
{
    public function handle(TransferCompleted $event): void
    {
        try {
            // إرسال إشعار
            $managers = \App\Models\User::role('warehouse_manager')->get();
            Notification::send($managers, new TransferCompletedNotification($event->transfer));

            // مسح Cache المخزون
            Cache::forget('inventory_report_all');
            Cache::forget('inventory_report_' . $event->transfer->from_warehouse_id);
            Cache::forget('inventory_report_' . $event->transfer->to_warehouse_id);
            Cache::forget('pending_transfers_count');

            Log::info('Transfer Completed', [
                'transfer_id' => $event->transfer->id,
                'transfer_number' => $event->transfer->transfer_number,
                'completed_by' => $event->completedBy,
                'completed_at' => $event->transfer->completed_at,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle transfer completion', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
