<?php

namespace App\Listeners\Accounting;

use App\Events\Return\PurchaseReturnProcessed;
use App\Services\Accounting\PostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostPurchaseReturnToGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected PostingService $postingService
    ) {}

    public function handle(PurchaseReturnProcessed $event): void
    {
        $purchaseReturn = $event->purchaseReturn;

        Log::info("[PostPurchaseReturnToGL] Starting GL post for purchase return #{$purchaseReturn->return_number}");

        $entry = $this->postingService->postPurchaseReturn($purchaseReturn);

        if ($entry) {
            Log::info("[PostPurchaseReturnToGL] Purchase Return #{$purchaseReturn->return_number} posted to GL. Entry ID: {$entry->id}");
        }
    }
}
