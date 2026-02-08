<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentReceived;
use App\Models\CashTransaction;

class RecordInAccountingLedger
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;

        // تسجيل في الخزينة
        CashTransaction::create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'deposit',
            'amount' => $payment->amount,
            'transaction_date' => $payment->payment_date,
            'category' => 'payment',
            'description' => 'دفعة على فاتورة رقم: ' . $payment->payable->invoice_number,
            'reference' => $payment->payment_number,
            'created_by' => $payment->created_by,
        ]);
    }

    protected function generateTransactionNumber()
    {
        $last = CashTransaction::latest('id')->first();
        $number = $last ? intval(substr($last->transaction_number, 3)) + 1 : 1;
        return 'TRX' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}