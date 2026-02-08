<?php
namespace App\Listeners\Payment;

use App\Events\Payment\PaymentReceived;
use App\Notifications\Payment\PaymentReceivedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class HandlePaymentReceived
{
    public function handle(PaymentReceived $event): void
    {
        try {
            // إرسال إشعار للمحاسبين
            $accountants = \App\Models\User::role('accountant')->get();
            Notification::send($accountants, new PaymentReceivedNotification($event->payment));

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
