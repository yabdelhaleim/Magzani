<?php

namespace App\Services\Accounting;

use App\Models\Expense;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpenseService
{
    /**
     * Get expenses list with filters
     */
    public function getExpensesList(array $filters = [])
    {
        $query = Expense::with(['user', 'account'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('date', '<=', $filters['to_date']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['beneficiary'])) {
            $query->where('beneficiary', 'LIKE', '%' . $filters['beneficiary'] . '%');
        }

        $expenses = $query->paginate(20);

        // Calculate statistics
        $todayExpenses = Expense::whereDate('date', Carbon::today())->sum('amount');
        $monthExpenses = Expense::whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('amount');
        $totalExpenses = Expense::sum('amount');

        return compact('expenses', 'todayExpenses', 'monthExpenses', 'totalExpenses');
    }

    /**
     * Create new expense
     */
    public function createExpense(array $data)
    {
        // Determine account based on payment method
        $accountType = in_array($data['payment_method'], ['cash']) ? 'cash' : 'bank';
        $account = Account::where('type', $accountType)->first();

        if (!$account) {
            throw new \Exception('الحساب غير موجود');
        }

        // Check balance
        if ($account->balance < $data['amount']) {
            throw new \Exception('الرصيد غير كافٍ في الحساب');
        }

        // Create expense
        $expense = Expense::create([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'date' => $data['date'],
            'payment_method' => $data['payment_method'],
            'beneficiary' => $data['beneficiary'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'account_id' => $account->id,
            'user_id' => Auth::id(),
            'status' => 'approved', // or 'pending' if approval workflow is needed
            'metadata' => json_encode([
                'created_by' => Auth::user()->name,
                'created_at_time' => Carbon::now()->toDateTimeString(),
            ]),
        ]);

        // Deduct from account balance
        $account->decrement('balance', $data['amount']);

        // Create transaction record
        $this->createExpenseTransaction($expense, $account);

        // Log activity
        activity()
            ->performedOn($expense)
            ->causedBy(Auth::user())
            ->withProperties(['amount' => $data['amount'], 'type' => $data['type']])
            ->log('expense_created');

        return $expense;
    }

    /**
     * Get expense details
     */
    public function getExpenseDetails($id)
    {
        return Expense::with(['user', 'account'])->find($id);
    }

    /**
     * Update expense
     */
    public function updateExpense($id, array $data)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return null;
        }

        // If amount changed, we need to adjust account balance
        if (isset($data['amount']) && $data['amount'] != $expense->amount) {
            $difference = $data['amount'] - $expense->amount;
            $account = $expense->account;

            if ($difference > 0 && $account->balance < $difference) {
                throw new \Exception('الرصيد غير كافٍ لزيادة المبلغ');
            }

            if ($difference > 0) {
                $account->decrement('balance', $difference);
            } else {
                $account->increment('balance', abs($difference));
            }
        }

        // Update expense
        $expense->update([
            'type' => $data['type'] ?? $expense->type,
            'amount' => $data['amount'] ?? $expense->amount,
            'date' => $data['date'] ?? $expense->date,
            'payment_method' => $data['payment_method'] ?? $expense->payment_method,
            'beneficiary' => $data['beneficiary'] ?? $expense->beneficiary,
            'invoice_number' => $data['invoice_number'] ?? $expense->invoice_number,
            'notes' => $data['notes'] ?? $expense->notes,
        ]);

        // Log activity
        activity()
            ->performedOn($expense)
            ->causedBy(Auth::user())
            ->log('expense_updated');

        return $expense;
    }

    /**
     * Delete expense
     */
    public function deleteExpense($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return false;
        }

        // Return money to account
        $account = $expense->account;
        $account->increment('balance', $expense->amount);

        // Log activity
        activity()
            ->performedOn($expense)
            ->causedBy(Auth::user())
            ->withProperties(['amount' => $expense->amount])
            ->log('expense_deleted');

        $expense->delete();

        return true;
    }

    /**
     * Get statistics
     */
    public function getStatistics($period = 'month')
    {
        $query = Expense::query();

        switch ($period) {
            case 'day':
                $query->whereDate('date', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('date', Carbon::now()->year);
                break;
        }

        $total = $query->sum('amount');
        $count = $query->count();
        $average = $count > 0 ? $total / $count : 0;

        // Get breakdown by type
        $byType = $query->select('type', \DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        // Get breakdown by payment method
        $byMethod = $query->select('payment_method', \DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method');

        return [
            'total' => $total,
            'count' => $count,
            'average' => $average,
            'by_type' => $byType,
            'by_method' => $byMethod,
        ];
    }

    /**
     * Get expenses by type
     */
    public function getExpensesByType($type, $from = null, $to = null)
    {
        $query = Expense::where('type', $type);

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Bulk delete expenses
     */
    public function bulkDelete(array $expenseIds)
    {
        $expenses = Expense::whereIn('id', $expenseIds)->get();

        foreach ($expenses as $expense) {
            $this->deleteExpense($expense->id);
        }

        return $expenses->count();
    }

    /**
     * Get recurring expenses
     */
    public function getRecurringExpenses()
    {
        // Identify expenses that repeat monthly (same type and similar amount)
        return Expense::select('type', 'beneficiary', \DB::raw('AVG(amount) as avg_amount'), \DB::raw('COUNT(*) as frequency'))
            ->where('date', '>=', Carbon::now()->subMonths(6))
            ->groupBy('type', 'beneficiary')
            ->having('frequency', '>=', 3)
            ->get();
    }

    /**
     * Approve expense
     */
    public function approveExpense($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return null;
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);

        // Log activity
        activity()
            ->performedOn($expense)
            ->causedBy(Auth::user())
            ->log('expense_approved');

        return $expense;
    }

    /**
     * Reject expense
     */
    public function rejectExpense($id, $reason)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return null;
        }

        // Return money to account if it was already deducted
        if ($expense->status === 'approved') {
            $account = $expense->account;
            $account->increment('balance', $expense->amount);
        }

        $expense->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_by' => Auth::id(),
            'rejected_at' => Carbon::now(),
        ]);

        // Log activity
        activity()
            ->performedOn($expense)
            ->causedBy(Auth::user())
            ->withProperties(['reason' => $reason])
            ->log('expense_rejected');

        return $expense;
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
     * Create expense transaction record
     */
    private function createExpenseTransaction($expense, $account)
    {
        $transaction = new \App\Models\Transaction();
        $transaction->account_id = $account->id;
        $transaction->type = 'expense';
        $transaction->reference = "EXP-{$expense->id}";
        $transaction->description = $expense->type . ' - ' . ($expense->notes ?? '');
        $transaction->out_amount = $expense->amount;
        $transaction->in_amount = 0;
        $transaction->balance_before = $account->balance + $expense->amount;
        $transaction->balance_after = $account->balance;
        $transaction->user_id = Auth::id();
        $transaction->metadata = json_encode([
            'expense_id' => $expense->id,
            'expense_type' => $expense->type,
        ]);
        $transaction->save();

        return $transaction;
    }
}