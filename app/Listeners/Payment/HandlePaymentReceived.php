<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentReceived;
use App\Notifications\Payment\PaymentReceivedNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePaymentReceived
{
    public function __construct(
        private NotificationDeliveryService $notifications
    ) {}

    public function handle(PaymentReceived $event): void
    {
        try {
            // Routine payment feedback stays a temporary toast, not a bell item.
            $this->notifications->sendToAdmins(
                new PaymentReceivedNotification($event->payment)
            );

            // مسح Cache الرصيد النقدي
            Cache::forget('cash_balance');
            Cache::forget('dashboard_summary');

            // تسجيل في اللوج
            Log::info('Payment Received', [
                'payment_id' => $event->payment->id,
                'amount' => $event->payment->amount,
                'payment_type' => $event->paymentType,
                'method' => $event->payment->payment_method,
                'recorded_by' => $event->recordedBy,
            ]);

            // إرسال SMS للعميل (اختياري)
            if ($event->payment->payable_type === 'App\Models\Customer') {
                // SMS Service here
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle payment received', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
