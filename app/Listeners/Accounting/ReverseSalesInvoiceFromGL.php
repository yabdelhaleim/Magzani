<?php

namespace App\Listeners\Accounting;

use App\Events\Invoice\SalesInvoiceCancelled;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use App\Enums\JournalEntryStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReverseSalesInvoiceFromGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected JournalEntryService $journalService
    ) {}

    public function handle(SalesInvoiceCancelled $event): void
    {
        $invoice = $event->invoice;
        $reason = $event->reason ?? 'إلغاء فاتورة مبيعات';

        Log::info("[ReverseSalesInvoiceFromGL] Reversing GL entries for cancelled sales invoice #{$invoice->invoice_number}");

        if ($invoice->journal_entry_id) {
            $entry = JournalEntry::find($invoice->journal_entry_id);
            if ($entry && $entry->status === JournalEntryStatus::POSTED) {
                try {
                    $this->journalService->revert($entry, $reason, now());
                } catch (\Throwable $e) {
                    Log::error("[ReverseSalesInvoiceFromGL] Failed to revert invoice entry #{$entry->id}: " . $e->getMessage());
                }
            }
        }

        if ($invoice->cogs_entry_id) {
            $entry = JournalEntry::find($invoice->cogs_entry_id);
            if ($entry && $entry->status === JournalEntryStatus::POSTED) {
                try {
                    $this->journalService->revert($entry, $reason . ' (COGS)', now());
                } catch (\Throwable $e) {
                    Log::error("[ReverseSalesInvoiceFromGL] Failed to revert invoice COGS entry #{$entry->id}: " . $e->getMessage());
                }
            }
        }
    }
}
