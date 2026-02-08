<?php
namespace App\Listeners\Transfer;

use App\Events\Transfer\TransferCancelled;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleTransferCancellation
{
    public function handle(TransferCancelled $event): void
    {
        try {
            Cache::forget('pending_transfers_count');

            Log::warning('Transfer Cancelled', [
                'transfer_id' => $event->transfer->id,
                'cancelled_by' => $event->cancelledBy,
                'reason' => $event->reason,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle transfer cancellation', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
