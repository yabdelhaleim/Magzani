<?php

namespace App\Listeners\Accounting;

use App\Events\Manufacturing\ManufacturingOrderCancelled;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use App\Enums\JournalEntryStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReverseManufacturingOrderFromGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected JournalEntryService $journalService
    ) {}

    public function handle(ManufacturingOrderCancelled $event): void
    {
        $order = $event->order;
        $reason = $event->reason ?? 'إلغاء أمر التصنيع';

        Log::info("[ReverseManufacturingOrderFromGL] Reversing GL entries for cancelled manufacturing order #{$order->order_number}");

        // Find any posted journal entries for this manufacturing order
        $entries = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('status', JournalEntryStatus::POSTED)
            ->get();

        foreach ($entries as $entry) {
            try {
                $this->journalService->revert($entry, $reason, now());
            } catch (\Throwable $e) {
                Log::error("[ReverseManufacturingOrderFromGL] Failed to revert entry #{$entry->id}: " . $e->getMessage());
            }
        }
    }
}
