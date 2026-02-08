<?php
namespace App\Listeners\Transfer;

use App\Events\Transfer\TransferInitiated;
use App\Notifications\Transfer\TransferInitiatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendTransferInitiatedNotification
{
    public function handle(TransferInitiated $event): void
    {
        try {
            // إرسال إشعار لمسؤولي المخازن
            $managers = \App\Models\User::role('warehouse_manager')->get();
            Notification::send($managers, new TransferInitiatedNotification($event->transfer));

            Log::info('Transfer Initiated', [
                'transfer_id' => $event->transfer->id,
                'transfer_number' => $event->transfer->transfer_number,
                'from_warehouse' => $event->transfer->fromWarehouse->name,
                'to_warehouse' => $event->transfer->toWarehouse->name,
                'items_count' => $event->transfer->items->count(),
                'initiated_by' => $event->initiatedBy,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send transfer notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
