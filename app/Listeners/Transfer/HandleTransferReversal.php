<?php
namespace App\Listeners\Transfer;

use App\Events\Transfer\TransferReversed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleTransferReversal
{
    public function handle(TransferReversed $event): void
    {
        try {
            // مسح Cache المخزون
            Cache::forget('inventory_report_all');
            Cache::forget('inventory_report_' . $event->transfer->from_warehouse_id);
            Cache::forget('inventory_report_' . $event->transfer->to_warehouse_id);

            Log::warning('Transfer Reversed', [
                'transfer_id' => $event->transfer->id,
                'reversed_by' => $event->reversedBy,
                'reason' => $event->reason,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle transfer reversal', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
