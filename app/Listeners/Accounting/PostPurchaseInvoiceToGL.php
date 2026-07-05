<?php

namespace App\Listeners\Accounting;

use App\Events\Invoice\PurchaseInvoiceConfirmed;
use App\Services\Accounting\PostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostPurchaseInvoiceToGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected PostingService $postingService
    ) {}

    public function handle(PurchaseInvoiceConfirmed $event): void
    {
        $invoice = $event->invoice;

        Log::info("[PostPurchaseInvoiceToGL] Starting GL post for purchase invoice #{$invoice->invoice_number}");

        $entry = $this->postingService->postPurchaseInvoice($invoice);

        if ($entry) {
            Log::info("[PostPurchaseInvoiceToGL] Purchase Invoice #{$invoice->invoice_number} posted to GL. Entry ID: {$entry->id}");
        }
    }
}
