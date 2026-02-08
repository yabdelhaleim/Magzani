<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashService
{
    /**
     * Get dashboard data
     */
    public function getDashboardData(array $filters = [])
    {
        // Get accounts
        $cashAccount = CashTransaction::where('type', 'cash')->first();
        $bankAccount = CashTransaction::where('type', 'bank')->first();
        $accounts = CashTransaction::all();

        // Calculate balances
        $cashBalance = $cashAccount ? $cashAccount->balance : 0;
        $bankBalance = $bankAccount ? $bankAccount->balance : 0;
        $totalLiquidity = $cashBalance + $bankBalance;

        // Get today's transactions count
        $todayTransactions = CashTransaction::whereDate('created_at', Carbon::today())->count();

        // Get transactions with filters
        $query = CashTransaction::with(['account', 'user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (!empty($filters['reference'])) {
            $query->where('reference', 'LIKE', '%' . $filters['reference'] . '%');
        }

        $transactions = $query->paginate(20);

        return compact(
            'cashBalance',
            'bankBalance',
            'totalLiquidity',
            'todayTransactions',
            'transactions',
            'accounts'
        );
    }

    /**
     * Get transaction details
     */
    public function getTransactionDetails($id)
    {
        return CashTransaction::with(['account', 'user'])->find($id);
    }

    /**
     * Create income transaction
     */
    public function createIncome(array $data)
    {
        $account = CashTransaction::findOrFail($data['account_id']);

        // Create transaction
        $transaction =CashTransaction::create([
            'account_id' => $account->id,
            'type' => 'income',
            'reference' => $this->generateReference('INC'),
            'description' => $data['description'],
            'in_amount' => $data['amount'],
            'out_amount' => 0,
            'balance_before' => $account->balance,
            'balance_after' => $account->balance + $data['amount'],
            'user_id' => Auth::id(),
            'metadata' => json_encode([
                'source' => $data['source'] ?? null,
                'category' => $data['category'] ?? null,
            ]),
        ]);

        // Update account balance
        $account->increment('balance', $data['amount']);

        // Log activity
        activity()
            ->performedOn($transaction)
            ->causedBy(Auth::user())
            ->withProperties(['amount' => $data['amount']])
            ->log('income_created');

        return $transaction;
    }

    /**
     * Create expense transaction
     */
    public function createExpense(array $data)
    {
        $account = CashTransaction::findOrFail($data['account_id']);

        // Check sufficient balance
        if ($account->balance < $data['amount']) {
            throw new \Exception('الرصيد غير كافٍ في الحساب');
        }

        // Create transaction
        $transaction = CashTransaction::create([
            'account_id' => $account->id,
            'type' => 'expense',
            'reference' => $this->generateReference('EXP'),
            'description' => $data['description'],
            'in_amount' => 0,
            'out_amount' => $data['amount'],
            'balance_before' => $account->balance,
            'balance_after' => $account->balance - $data['amount'],
            'user_id' => Auth::id(),
            'metadata' => json_encode([
                'category' => $data['category'] ?? null,
                'beneficiary' => $data['beneficiary'] ?? null,
            ]),
        ]);

        // Update account balance
        $account->decrement('balance', $data['amount']);

        // Log activity
        activity()
            ->performedOn($transaction)
            ->causedBy(Auth::user())
            ->withProperties(['amount' => $data['amount']])
            ->log('expense_created');

        return $transaction;
    }

    /**
     * Create transfer transaction
     */
    public function createTransfer(array $data)
    {
        $fromAccount = CashTransaction::findOrFail($data['from_account_id']);
        $toAccount = CashTransaction::findOrFail($data['to_account_id']);

        // Validate
        if ($fromAccount->id === $toAccount->id) {
            throw new \Exception('لا يمكن التحويل إلى نفس الحساب');
        }

        if ($fromAccount->balance < $data['amount']) {
            throw new \Exception('الرصيد غير كافٍ في الحساب المصدر');
        }

        $reference = $this->generateReference('TRF');

        // Create debit transaction (from account)
        $debitTransaction = CashTransaction::create([
            'account_id' => $fromAccount->id,
            'type' => 'transfer',
            'reference' => $reference,
            'description' => $data['description'] ?? "تحويل إلى {$toAccount->name}",
            'in_amount' => 0,
            'out_amount' => $data['amount'],
            'balance_before' => $fromAccount->balance,
            'balance_after' => $fromAccount->balance - $data['amount'],
            'user_id' => Auth::id(),
            'metadata' => json_encode([
                'transfer_to' => $toAccount->id,
                'transfer_type' => 'debit',
            ]),
        ]);

        // Create credit transaction (to account)
        $creditTransaction =CashTransaction::create([
            'account_id' => $toAccount->id,
            'type' => 'transfer',
            'reference' => $reference,
            'description' => $data['description'] ?? "تحويل من {$fromAccount->name}",
            'in_amount' => $data['amount'],
            'out_amount' => 0,
            'balance_before' => $toAccount->balance,
            'balance_after' => $toAccount->balance + $data['amount'],
            'user_id' => Auth::id(),
            'metadata' => json_encode([
                'transfer_from' => $fromAccount->id,
                'transfer_type' => 'credit',
            ]),
        ]);

        // Update balances
        $fromAccount->decrement('balance', $data['amount']);
        $toAccount->increment('balance', $data['amount']);

        // Log activity
        activity()
            ->performedOn($debitTransaction)
            ->causedBy(Auth::user())
            ->withProperties([
                'amount' => $data['amount'],
                'from' => $fromAccount->name,
                'to' => $toAccount->name,
            ])
            ->log('transfer_created');

        return [$debitTransaction, $creditTransaction];
    }

    /**
     * Update transaction
     */
    public function updateTransaction($id, array $data)
    {
        $transaction = CashTransaction::find($id);

        if (!$transaction) {
            return null;
        }

        // For now, we'll just update the description and metadata
        // Updating amounts requires reversing and recreating transactions
        $transaction->update([
            'description' => $data['description'] ?? $transaction->description,
            'metadata' => json_encode(array_merge(
                json_decode($transaction->metadata, true) ?? [],
                $data['metadata'] ?? []
            )),
        ]);

        return $transaction;
    }

    /**
     * Delete transaction (reverse it)
     */
    public function deleteTransaction($id)
    {
        $transaction = CashTransaction::find($id);

        if (!$transaction) {
            return false;
        }

        $account = $transaction->account;

        // Reverse the transaction
        if ($transaction->in_amount > 0) {
            // Was income, deduct it
            if ($account->balance < $transaction->in_amount) {
                throw new \Exception('لا يمكن حذف الحركة: الرصيد الحالي غير كافٍ');
            }
            $account->decrement('balance', $transaction->in_amount);
        } else {
            // Was expense, add it back
            $account->increment('balance', $transaction->out_amount);
        }

        // Log activity
        activity()
            ->performedOn($transaction)
            ->causedBy(Auth::user())
            ->log('transaction_deleted');

        $transaction->delete();

        return true;
    }

    /**
     * Get account balance
     */
    public function getAccountBalance($accountId)
    {
        $account = CashTransaction::find($accountId);
        return $account ? $account->balance : 0;
    }

    /**
     * Get statistics
     */
    public function getStatistics($period = 'today')
    {
        $query = CashTransaction::query();

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

        return [
            'total_income' => $query->sum('in_amount'),
            'total_expense' => $query->sum('out_amount'),
            'net_flow' => $query->sum('in_amount') - $query->sum('out_amount'),
            'transaction_count' => $query->count(),
        ];
    }

    /**
     * Reconcile account
     */
    public function reconcileAccount($accountId, $actualBalance, $date, $notes = null)
    {
        $account = CashTransaction::findOrFail($accountId);
        $systemBalance = $account->balance;
        $difference = $actualBalance - $systemBalance;

        if ($difference != 0) {
            // Create adjustment transaction
            $transaction = CashTransaction::create([
                'account_id' => $account->id,
                'type' => $difference > 0 ? 'income' : 'expense',
                'reference' => $this->generateReference('REC'),
                'description' => "تسوية حساب - {$notes}",
                'in_amount' => $difference > 0 ? abs($difference) : 0,
                'out_amount' => $difference < 0 ? abs($difference) : 0,
                'balance_before' => $systemBalance,
                'balance_after' => $actualBalance,
                'user_id' => Auth::id(),
                'metadata' => json_encode([
                    'reconciliation' => true,
                    'reconciliation_date' => $date,
                    'system_balance' => $systemBalance,
                    'actual_balance' => $actualBalance,
                    'difference' => $difference,
                ]),
            ]);

            // Update account balance
            $account->update(['balance' => $actualBalance]);
        }

        // Log activity
        activity()
            ->performedOn($account)
            ->causedBy(Auth::user())
            ->withProperties([
                'system_balance' => $systemBalance,
                'actual_balance' => $actualBalance,
                'difference' => $difference,
            ])
            ->log('account_reconciled');

        return true;
    }

    /**
     * Export report
     */
    public function exportReport(array $filters)
    {
        // This would integrate with Laravel Excel or similar
        // For now, returning a placeholder
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    /**
     * Generate unique reference number
     */
    private function generateReference($prefix)
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "{$prefix}-{$date}-{$random}";
    }
}