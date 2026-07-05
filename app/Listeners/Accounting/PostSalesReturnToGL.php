<?php

namespace App\Listeners\Accounting;

use App\Events\Return\SalesReturnProcessed;
use App\Services\Accounting\PostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostSalesReturnToGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected PostingService $postingService
    ) {}

    public function handle(SalesReturnProcessed $event): void
    {
        $salesReturn = $event->salesReturn;

        Log::info("[PostSalesReturnToGL] Starting GL post for sales return #{$salesReturn->return_number}");

        // حساب تكلفة البضاعة المرتجعة لعكس COGS
        $cogsAmount = 0.0;
        $salesReturn->loadMissing('items.product');

        foreach ($salesReturn->items as $item) {
            $product = $item->product;
            $purchasePrice = $product ? (float) $product->purchase_price : 0.0;
            $baseQty = (float) ($item->base_quantity_returned ?? $item->quantity_returned ?? 0);
            $cogsAmount += $purchasePrice * $baseQty;
        }

        $entry = $this->postingService->postSalesReturn($salesReturn, $cogsAmount);

        if ($entry) {
            Log::info("[PostSalesReturnToGL] Sales Return #{$salesReturn->return_number} posted to GL. Entry ID: {$entry->id}");
        }
    }
}
