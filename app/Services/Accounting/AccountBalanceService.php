<?php

namespace App\Services\Accounting;

use App\Models\AccountBalance;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AccountBalanceService
 *
 * مسؤول عن إدارة جدول account_balances (الأرصدة المتراكمة / Materialized Balances).
 *
 * الحقول الفعلية في AccountBalance model:
 *   period_debit, period_credit, ytd_debit, ytd_credit, balance, last_entry_id, last_entry_date
 *
 * نحن نستخدم ytd_debit / ytd_credit كمجموع تراكمي (Year-To-Date)
 * و balance = ytd_debit - ytd_credit أو العكس حسب طبيعة الحساب
 */
class AccountBalanceService
{
    /**
     * تحديث رصيد حسابٍ معين بعد قيد بأرقام delta (تحديث تراكمي سريع)
     *
     * @param int   $accountId
     * @param float $debitDelta   مقدار الزيادة في المدين
     * @param float $creditDelta  مقدار الزيادة في الدائن
     * @param int|null $lastEntryId  آخر قيد أثّر على الحساب
     */
    public function applyDelta(int $accountId, float $debitDelta, float $creditDelta, ?int $lastEntryId = null): void
    {
        DB::transaction(function () use ($accountId, $debitDelta, $creditDelta, $lastEntryId) {
            $balance = AccountBalance::firstOrCreate(
                ['account_id' => $accountId],
                [
                    'period_debit'  => 0,
                    'period_credit' => 0,
                    'ytd_debit'     => 0,
                    'ytd_credit'    => 0,
                    'balance'       => 0,
                ]
            );

            $newYtdDebit  = (float)$balance->ytd_debit  + $debitDelta;
            $newYtdCredit = (float)$balance->ytd_credit + $creditDelta;
            $net          = $this->calculateNet($accountId, $newYtdDebit, $newYtdCredit);

            $updateData = [
                'ytd_debit'    => max(0, $newYtdDebit),
                'ytd_credit'   => max(0, $newYtdCredit),
                'period_debit'  => max(0, (float)$balance->period_debit  + $debitDelta),
                'period_credit' => max(0, (float)$balance->period_credit + $creditDelta),
                'balance'      => $net,
            ];

            if ($lastEntryId) {
                $updateData['last_entry_id']   = $lastEntryId;
                $updateData['last_entry_date'] = now()->toDateString();
            }

            $balance->update($updateData);
        });
    }

    /**
     * تطبيق تأثير أسطر قيد مُعتمَد على الأرصدة المتراكمة
     *
     * @param \Illuminate\Support\Collection $lines  أسطر JournalEntryLine
     */
    public function applyLines($lines): void
    {
        // تجميع deltas لكل حساب أولاً لتقليل عدد الطلبات
        $deltas = [];

        foreach ($lines as $line) {
            $id = $line->account_id;
            if (!isset($deltas[$id])) {
                $deltas[$id] = ['debit' => 0.0, 'credit' => 0.0, 'entry_id' => null];
            }
            $deltas[$id]['debit']    += (float)$line->debit;
            $deltas[$id]['credit']   += (float)$line->credit;
            $deltas[$id]['entry_id']  = $line->journal_entry_id;
        }

        foreach ($deltas as $accountId => $delta) {
            $this->applyDelta($accountId, $delta['debit'], $delta['credit'], $delta['entry_id']);
        }
    }

    /**
     * عكس تأثير أسطر قيد (يُستخدَم عند عكس قيد)
     * نفس منطق applyLines لكن بإشارة معكوسة
     */
    public function reverseLines($lines): void
    {
        $deltas = [];

        foreach ($lines as $line) {
            $id = $line->account_id;
            if (!isset($deltas[$id])) {
                $deltas[$id] = ['debit' => 0.0, 'credit' => 0.0, 'entry_id' => null];
            }
            $deltas[$id]['debit']   -= (float)$line->debit;
            $deltas[$id]['credit']  -= (float)$line->credit;
            $deltas[$id]['entry_id'] = $line->journal_entry_id;
        }

        foreach ($deltas as $accountId => $delta) {
            $this->applyDelta($accountId, $delta['debit'], $delta['credit'], $delta['entry_id']);
        }
    }

    /**
     * إعادة حساب رصيد حساب واحد من الصفر من سطور القيود المعتمدة
     * (للتدقيق أو الإصلاح بعد اكتشاف تناقض)
     */
    public function recalculateAccount(int $accountId): AccountBalance
    {
        return DB::transaction(function () use ($accountId) {
            $totals = JournalEntryLine::where('account_id', $accountId)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit, MAX(journal_entry_id) as last_je_id')
                ->first();

            $totalDebit  = (float) ($totals->total_debit  ?? 0);
            $totalCredit = (float) ($totals->total_credit ?? 0);
            $net         = $this->calculateNet($accountId, $totalDebit, $totalCredit);

            return AccountBalance::updateOrCreate(
                ['account_id' => $accountId],
                [
                    'ytd_debit'     => $totalDebit,
                    'ytd_credit'    => $totalCredit,
                    'period_debit'  => $totalDebit,
                    'period_credit' => $totalCredit,
                    'balance'       => $net,
                    'last_entry_id'   => $totals->last_je_id ?? null,
                    'last_entry_date' => now()->toDateString(),
                ]
            );
        });
    }

    /**
     * إعادة حساب جميع الأرصدة لجميع الحسابات الورقية (leaf accounts)
     * (أمر صيانة - يُشغَّل بشكل دوري أو عند الطلب)
     */
    public function recalculateAll(): int
    {
        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->pluck('id');

        $count = 0;
        foreach ($accounts as $accountId) {
            try {
                $this->recalculateAccount($accountId);
                $count++;
            } catch (\Throwable $e) {
                Log::error("[AccountBalanceService] Failed to recalculate account #{$accountId}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * جلب الرصيد الصافي لحساب معين
     */
    public function getBalance(int $accountId): float
    {
        return (float) AccountBalance::where('account_id', $accountId)->value('balance') ?? 0.0;
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────────────────

    /**
     * حساب الرصيد الصافي وفق طبيعة الحساب
     * للأصول والمصروفات: Net = Debit - Credit
     * للخصوم وحقوق الملكية والإيرادات: Net = Credit - Debit
     */
    private function calculateNet(int $accountId, float $debit, float $credit): float
    {
        $account = Account::with('accountType')->find($accountId);

        if (!$account || !$account->accountType) {
            return round($debit - $credit, 2);
        }

        $normalBalance = $account->accountType->normal_balance;
        $normalBalanceVal = ($normalBalance instanceof \App\Enums\NormalBalance) ? $normalBalance->value : $normalBalance;

        return match ($normalBalanceVal) {
            'debit'  => round($debit - $credit, 2),
            'credit' => round($credit - $debit, 2),
            default  => round($debit - $credit, 2),
        };
    }
}
