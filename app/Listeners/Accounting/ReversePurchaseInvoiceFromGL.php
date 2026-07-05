<?php

namespace App\Listeners\Accounting;

use App\Events\Invoice\PurchaseInvoiceCancelled;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use App\Enums\JournalEntryStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReversePurchaseInvoiceFromGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected JournalEntryService $journalService
    ) {}

    public function handle(PurchaseInvoiceCancelled $event): void
    {
        $invoice = $event->invoice;
        $reason = $event->reason ?? 'إلغاء فاتورة مشتريات';

        Log::info("[ReversePurchaseInvoiceFromGL] Reversing GL entries for cancelled purchase invoice #{$invoice->invoice_number}");

        if ($invoice->journal_entry_id) {
            $entry = JournalEntry::find($invoice->journal_entry_id);
            if ($entry && $entry->status === JournalEntryStatus::POSTED) {
                try {
                    $this->journalService->revert($entry, $reason, now());
                } catch (\Throwable $e) {
                    Log::error("[ReversePurchaseInvoiceFromGL] Failed to revert purchase invoice entry #{$entry->id}: " . $e->getMessage());
                }
            }
        }
    }
}
