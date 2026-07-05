<?php

namespace App\Listeners\Accounting;

use App\Events\Payment\PaymentReceived;
use App\Services\Accounting\PostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostPaymentToGL implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected PostingService $postingService
    ) {}

    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;

        Log::info("[PostPaymentToGL] Starting GL post for payment #{$payment->id}");

        $entry = $this->postingService->postCustomerPayment($payment);

        if ($entry) {
            Log::info("[PostPaymentToGL] Payment #{$payment->id} posted to GL. Entry ID: {$entry->id}");
        }
    }
}
