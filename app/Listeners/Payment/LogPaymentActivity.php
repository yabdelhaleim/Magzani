<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentReceived;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogPaymentActivity
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'log_name' => 'payment',
            'description' => 'تم تسجيل دفعة رقم: ' . $payment->payment_number,
            'subject_type' => get_class($payment),
            'subject_id' => $payment->id,
            'causer_type' => 'App\Models\User',
            'causer_id' => Auth::id(),
            'properties' => json_encode([
                'payment_number' => $payment->payment_number,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
            ]),
        ]);
    }
}