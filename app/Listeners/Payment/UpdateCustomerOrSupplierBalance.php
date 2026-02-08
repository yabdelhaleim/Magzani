<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentReceived;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class UpdateCustomerOrSupplierBalance
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $invoice = $payment->payable;

        DB::transaction(function () use ($payment, $invoice) {
            if ($invoice instanceof SalesInvoice) {
                // تقليل رصيد العميل
                $invoice->customer->decrement('current_balance', $payment->amount);
            } elseif ($invoice instanceof PurchaseInvoice) {
                // تقليل رصيد المورد
                $invoice->supplier->decrement('current_balance', $payment->amount);
            }
        });
    }
}