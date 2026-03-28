<?php

namespace App\Services\Accounting;

use App\Models\Payment;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Get payments list with filters
     */
    public function getPaymentsList(array $filters = [])
    {
        $query = Payment::with(['user', 'account'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $payments = $query->paginate(20);

        // Calculate statistics
        $todaySupplierPayments = Payment::where('type', 'supplier')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $todayCustomerPayments = Payment::where('type', 'customer')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        return compact('payments', 'todaySupplierPayments', 'todayCustomerPayments');
    }

    /**
     * Create new payment
     */
    public function createPayment(array $data)
    {
        // Determine account based on payment method
        $accountType = in_array($data['method'], ['cash']) ? 'cash' : 'bank';
        $account = Account::where('type', $accountType)->first();

        if (!$account) {
            throw new \Exception('الحساب غير موجود');
        }

        // For supplier payments, check if we have enough balance
        if ($data['type'] === 'supplier' && $account->balance < $data['amount']) {
            throw new \Exception('الرصيد غير كافٍ في الحساب');
        }

        // Create payment
        $payment = Payment::create([
            'type' => $data['type'], // supplier or customer
            'name' => $data['name'],
            'amount' => $data['amount'],
            'method' => $data['method'], // cash, bank, check, card
            'reference' => $data['reference'] ?? null,
            'date' => $data['date'] ?? Carbon::now(),
            'notes' => $data['notes'] ?? null,
            'account_id' => $account->id,
            'user_id' => Auth::id(),
            'status' => in_array($data['method'], ['cash']) ? 'verified' : 'pending',
            'metadata' => json_encode([
                'created_by' => Auth::user()->name,
                'created_at_time' => Carbon::now()->toDateTimeString(),
            ]),
        ]);

        // Update account balance
        if ($data['type'] === 'supplier') {
            // Payment to supplier - deduct from account
            $account->decrement('balance', $data['amount']);
        } else {
            // Receipt from customer - add to account
            $account->increment('balance', $data['amount']);
        }

        // Create transaction record
        $this->createPaymentTransaction($payment, $account);

        // Log activity
        activity()
            ->performedOn($payment)
            ->causedBy(Auth::user())
            ->withProperties([
                'amount' => $data['amount'],
                'type' => $data['type'],
                'name' => $data['name']
            ])
            ->log('payment_created');

        return $payment;
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails($id)
    {
        return Payment::with(['user', 'account'])->find($id);
    }

    /**
     * Update payment
     */
    public function updatePayment($id, array $data)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return null;
        }

        // If amount changed, adjust account balance
        if (isset($data['amount']) && $data['amount'] != $payment->amount) {
            $difference = $data['amount'] - $payment->amount;
            $account = $payment->account;

            if ($payment->type === 'supplier') {
                // For supplier payments, more money means we need more balance
                if ($difference > 0 && $account->balance < $difference) {
                    throw new \Exception('الرصيد غير كافٍ لزيادة المبلغ');
                }
                
                if ($difference > 0) {
                    $account->decrement('balance', $difference);
                } else {
                    $account->increment('balance', abs($difference));
                }
            } else {
                // For customer receipts, more money means more income
                if ($difference > 0) {
                    $account->increment('balance', $difference);
                } else {
                    $account->decrement('balance', abs($difference));
                }
            }
        }

        // Update payment
        $payment->update([
            'name' => $data['name'] ?? $payment->name,
            'amount' => $data['amount'] ?? $payment->amount,
            'method' => $data['method'] ?? $payment->method,
            'reference' => $data['reference'] ?? $payment->reference,
            'date' => $data['date'] ?? $payment->date,
            'notes' => $data['notes'] ?? $payment->notes,
        ]);

        // Log activity
        activity()
            ->performedOn($payment)
            ->causedBy(Auth::user())
            ->log('payment_updated');

        return $payment;
    }

    /**
     * Delete payment
     */
    public function deletePayment($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return false;
        }

        // Reverse the payment in account
        $account = $payment->account;
        
        if ($payment->type === 'supplier') {
            // Was a payment out, return it
            $account->increment('balance', $payment->amount);
        } else {
            // Was a receipt, deduct it
            if ($account->balance < $payment->amount) {
                throw new \Exception('لا يمكن حذف المقبوض: الرصيد الحالي غير كافٍ');
            }
            $account->decrement('balance', $payment->amount);
        }

        // Log activity
        activity()
            ->performedOn($payment)
            ->causedBy(Auth::user())
            ->withProperties(['amount' => $payment->amount])
            ->log('payment_deleted');

        $payment->delete();

        return true;
    }

    /**
     * Get statistics
     */
    public function getStatistics($period = 'today')
    {
        $query = Payment::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
        }

        $supplierPayments = (clone $query)->where('type', 'supplier')->sum('amount');
        $customerPayments = (clone $query)->where('type', 'customer')->sum('amount');

        return [
            'supplier_payments' => $supplierPayments,
            'customer_payments' => $customerPayments,
            'net_cash_flow' => $customerPayments - $supplierPayments,
            'total_transactions' => $query->count(),
        ];
    }

    /**
     * Get supplier payments
     */
    public function getSupplierPayments($supplierId = null)
    {
        $query = Payment::where('type', 'supplier');

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get customer payments
     */
    public function getCustomerPayments($customerId = null)
    {
        $query = Payment::where('type', 'customer');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Verify payment
     */
    public function verifyPayment($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return null;
        }

        $payment->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => Carbon::now(),
        ]);

        // Log activity
        activity()
            ->performedOn($payment)
            ->causedBy(Auth::user())
            ->log('payment_verified');

        return $payment;
    }

    /**
     * Cancel payment
     */
    public function cancelPayment($id, $reason)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return null;
        }

        // Reverse the transaction
        $account = $payment->account;

        if ($payment->type === 'supplier') {
            $account->increment('balance', $payment->amount);
        } else {
            if ($account->balance < $payment->amount) {
                throw new \Exception('لا يمكن إلغاء الدفعة: الرصيد غير كافٍ');
            }
            $account->decrement('balance', $payment->amount);
        }

        $payment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => Carbon::now(),
        ]);

        // Log activity
        activity()
            ->performedOn($payment)
            ->causedBy(Auth::user())
            ->withProperties(['reason' => $reason])
            ->log('payment_cancelled');

        return $payment;
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments()
    {
        return Payment::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get cash flow analysis
     */
    public function getCashFlowAnalysis($period = 'month')
    {
        $startDate = Carbon::now();
        
        switch ($period) {
            case 'week':
                $startDate = $startDate->startOfWeek();
                break;
            case 'month':
                $startDate = $startDate->startOfMonth();
                break;
            case 'year':
                $startDate = $startDate->startOfYear();
                break;
        }

        $payments = Payment::where('created_at', '>=', $startDate)->get();

        $analysis = [
            'total_in' => $payments->where('type', 'customer')->sum('amount'),
            'total_out' => $payments->where('type', 'supplier')->sum('amount'),
            'net_flow' => 0,
            'by_method' => [],
            'daily_flow' => [],
        ];

        $analysis['net_flow'] = $analysis['total_in'] - $analysis['total_out'];

        // Group by method
        $analysis['by_method'] = $payments->groupBy('method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ];
        });

        // Group by day
        $analysis['daily_flow'] = $payments->groupBy(function ($payment) {
            return Carbon::parse($payment->created_at)->format('Y-m-d');
        })->map(function ($group) {
            return [
                'in' => $group->where('type', 'customer')->sum('amount'),
                'out' => $group->where('type', 'supplier')->sum('amount'),
                'net' => $group->where('type', 'customer')->sum('amount') - $group->where('type', 'supplier')->sum('amount'),
            ];
        });

        return $analysis;
    }

    /**
     * Bulk verify payments
     */
    public function bulkVerify(array $paymentIds)
    {
        $payments = Payment::whereIn('id', $paymentIds)->get();

        foreach ($payments as $payment) {
            $this->verifyPayment($payment->id);
        }

        return $payments->count();
    }

    /**
     * Get payment methods statistics
     */
    public function getMethodsStatistics($from = null, $to = null)
    {
        $query = Payment::query();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->select('method', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get()
            ->keyBy('method');
    }

    /**
     * Export report
     */
    public function exportReport(array $filters)
    {
        // This would integrate with Laravel Excel
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    /**
     * Create payment transaction record
     */
    private function createPaymentTransaction($payment, $account)
    {
        $transaction = new Transaction();
        $transaction->account_id = $account->id;
        $transaction->type = $payment->type === 'supplier' ? 'expense' : 'income';
        $transaction->reference = "PAY-{$payment->id}";
        $transaction->description = $payment->type === 'supplier' 
            ? "دفع لمورد: {$payment->name}" 
            : "تحصيل من عميل: {$payment->name}";
        
        if ($payment->type === 'supplier') {
            $transaction->out_amount = $payment->amount;
            $transaction->in_amount = 0;
            $transaction->balance_before = $account->balance + $payment->amount;
        } else {
            $transaction->in_amount = $payment->amount;
            $transaction->out_amount = 0;
            $transaction->balance_before = $account->balance - $payment->amount;
        }
        
        $transaction->balance_after = $account->balance;
        $transaction->user_id = Auth::id();
        $transaction->metadata = json_encode([
            'payment_id' => $payment->id,
            'payment_type' => $payment->type,
            'payment_method' => $payment->method,
        ]);
        $transaction->save();

        return $transaction;
    }
}