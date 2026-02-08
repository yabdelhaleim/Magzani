<?php
namespace App\Listeners\Payment;

use App\Events\Payment\PaymentCancelled;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePaymentCancellation
{
    public function handle(PaymentCancelled $event): void
    {
        try {
            Cache::forget('cash_balance');
            Cache::forget('dashboard_summary');

            Log::warning('Payment Cancelled', [
                'payment_id' => $event->payment->id,
                'cancelled_by' => $event->cancelledBy,
                'reason' => $event->reason,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle payment cancellation', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
