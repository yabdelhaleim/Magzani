<?php

namespace App\Services;

use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AccountingService
{
    /**
     * Get current cash balance
     */
    public function getCashBalance(): float
    {
        try {
            $deposits = CashTransaction::deposits()->sum('amount');
            $withdrawals = CashTransaction::withdrawals()->sum('amount');

            return round($deposits - $withdrawals, 2);
        } catch (Exception $e) {
            Log::error('Error calculating cash balance: ' . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Get bank balance
     */
    public function getBankBalance(): float
    {
        return 0.00;
    }

    /**
     * Add a cash deposit
     */
    public function addDeposit(
        float $amount,
        ?string $description = null,
        ?string $category = null,
        $transactionDate = null,
        ?string $reference = null
    ): CashTransaction {
        try {
            DB::beginTransaction();

            $transaction = CashTransaction::create([
                'transaction_type' => CashTransaction::TYPE_DEPOSIT,
                'amount' => abs($amount),
                'description' => $description,
                'category' => $category,
                'transaction_date' => $transactionDate ?? now(),
                'reference' => $reference,
                'created_by' => null, // مؤقتاً null بدل auth()->id()
            ]);

            DB::commit();

            Log::info('Deposit added successfully', [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
            ]);

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error adding deposit: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add a cash withdrawal
     */
    public function addWithdrawal(
        float $amount,
        ?string $description = null,
        ?string $category = null,
        $transactionDate = null,
        ?string $reference = null
    ): CashTransaction {
        try {
            DB::beginTransaction();

            // Check if sufficient balance exists
            $currentBalance = $this->getCashBalance();
            if ($currentBalance < $amount) {
                throw new Exception('الرصيد غير كافٍ لإتمام عملية السحب');
            }

            $transaction = CashTransaction::create([
                'transaction_type' => CashTransaction::TYPE_WITHDRAWAL,
                'amount' => abs($amount),
                'description' => $description,
                'category' => $category,
                'transaction_date' => $transactionDate ?? now(),
                'reference' => $reference,
                'created_by' => null, // مؤقتاً null بدل auth()->id()
            ]);

            DB::commit();

            Log::info('Withdrawal added successfully', [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
            ]);

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error adding withdrawal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get cash statistics
     */
    public function getCashStatistics($startDate = null, $endDate = null): array
    {
        try {
            $query = CashTransaction::query();

            if ($startDate) {
                $query->where('transaction_date', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('transaction_date', '<=', $endDate);
            }

            $deposits = (clone $query)->deposits()->sum('amount');
            $withdrawals = (clone $query)->withdrawals()->sum('amount');
            $transactionCount = $query->count();
            $depositCount = (clone $query)->deposits()->count();
            $withdrawalCount = (clone $query)->withdrawals()->count();

            return [
                'total_deposits' => round($deposits, 2),
                'total_withdrawals' => round($withdrawals, 2),
                'net_balance' => round($deposits - $withdrawals, 2),
                'transaction_count' => $transactionCount,
                'deposit_count' => $depositCount,
                'withdrawal_count' => $withdrawalCount,
            ];
        } catch (Exception $e) {
            Log::error('Error calculating cash statistics: ' . $e->getMessage());
            return [
                'total_deposits' => 0,
                'total_withdrawals' => 0,
                'net_balance' => 0,
                'transaction_count' => 0,
                'deposit_count' => 0,
                'withdrawal_count' => 0,
            ];
        }
    }

    /**
     * Get transactions by category
     */
    public function getTransactionsByCategory(string $category, $startDate = null, $endDate = null)
    {
        $query = CashTransaction::byCategory($category);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Get today's transactions
     */
    public function getTodayTransactions()
    {
        return CashTransaction::today()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get this month's transactions
     */
    public function getMonthTransactions()
    {
        return CashTransaction::thisMonth()
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Delete a transaction
     */
    public function deleteTransaction(int $transactionId): bool
    {
        try {
            DB::beginTransaction();

            $transaction = CashTransaction::findOrFail($transactionId);
            $result = $transaction->delete();

            DB::commit();

            Log::info('Transaction deleted successfully', [
                'transaction_id' => $transactionId,
            ]);

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a transaction
     */
    public function updateTransaction(int $transactionId, array $data): CashTransaction
    {
        try {
            DB::beginTransaction();

            $transaction = CashTransaction::findOrFail($transactionId);
            $transaction->update($data);

            DB::commit();

            Log::info('Transaction updated successfully', [
                'transaction_id' => $transactionId,
            ]);

            return $transaction->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all available categories
     */
    public function getCategories(): array
    {
        return CashTransaction::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    /**
 * Treasury main data for dashboard
 */
public function getTreasuryData(): array
{
    return [
        'cash_balance'   => $this->getCashBalance(),
        'bank_balance'   => $this->getBankBalance(),
        'statistics'     => $this->getCashStatistics(),
        'today_transactions' => $this->getTodayTransactions(),
        'categories'     => $this->getCategories(),
    ];
}

}