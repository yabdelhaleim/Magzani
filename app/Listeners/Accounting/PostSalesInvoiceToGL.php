<?php

namespace App\Listeners\Accounting;

use App\Events\Invoice\SalesInvoiceConfirmed;
use App\Services\Accounting\PostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostSalesInvoiceToGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected PostingService $postingService
    ) {}

    public function handle(SalesInvoiceConfirmed $event): void
    {
        $invoice = $event->invoice;

        Log::info("[PostSalesInvoiceToGL] Starting GL post for sales invoice #{$invoice->invoice_number}");

        // 1. ترحيل الفاتورة للدفتر العام
        $entry = $this->postingService->postSalesInvoice($invoice);

        if ($entry) {
            Log::info("[PostSalesInvoiceToGL] Invoice #{$invoice->invoice_number} posted to GL. Entry ID: {$entry->id}");

            // 2. حساب تكلفت البضاعة المباعة (COGS) وترحيلها
            $cogsAmount = 0.0;
            $invoice->loadMissing('items.product');

            foreach ($invoice->items as $item) {
                $product = $item->product;
                $purchasePrice = $product ? (float) $product->purchase_price : 0.0;
                $baseQty = (float) ($item->base_quantity ?? $item->quantity ?? 0);
                $cogsAmount += $purchasePrice * $baseQty;
            }

            if ($cogsAmount > 0) {
                $cogsEntry = $this->postingService->postSalesInvoiceCogs($invoice, $cogsAmount);
                if ($cogsEntry) {
                    Log::info("[PostSalesInvoiceToGL] COGS for Invoice #{$invoice->invoice_number} posted to GL. COGS Entry ID: {$cogsEntry->id}, Amount: {$cogsAmount}");
                }
            }
        }
    }
}
