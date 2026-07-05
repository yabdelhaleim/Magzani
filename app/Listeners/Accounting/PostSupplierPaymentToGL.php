<?php

namespace App\Listeners\Accounting;

use App\Events\Payment\SupplierPaymentCreated;
use App\Services\Accounting\PostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostSupplierPaymentToGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected PostingService $postingService
    ) {}

    public function handle(SupplierPaymentCreated $event): void
    {
        $payment = $event->payment;

        Log::info("[PostSupplierPaymentToGL] Starting GL post for supplier payment #{$payment->id}");

        $entry = $this->postingService->postSupplierPayment($payment);

        if ($entry) {
            Log::info("[PostSupplierPaymentToGL] Supplier Payment #{$payment->id} posted to GL. Entry ID: {$entry->id}");
        }
    }
}
