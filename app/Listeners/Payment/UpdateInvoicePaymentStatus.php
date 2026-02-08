<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentReceived;

class UpdateInvoicePaymentStatus
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $invoice = $payment->payable;

        if ($invoice) {
            $invoice->updatePaymentStatus();
        }
    }
}