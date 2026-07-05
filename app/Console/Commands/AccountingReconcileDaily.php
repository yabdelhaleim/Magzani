<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\JournalEntryLine;
use App\Services\Accounting\AccountBalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AccountingReconcileDaily extends Command
{
    protected $signature = 'accounting:reconcile-daily
                            {--fix : إصلاح الفروقات تلقائياً}
                            {--notify : إرسال إشعار في حال وجود فروقات}';

    protected $description = 'مطابقة يومية: مقارنة الأرصدة المتراكمة مع القيود الفعلية والتحقق من السلامة';

    public function __construct(
        private AccountBalanceService $balanceService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[' . now()->toDateTimeString() . '] بدء المطابقة اليومية...');

        $drifts  = $this->checkBalanceDrift();
        $unbalanced = $this->checkUnbalancedEntries();

        $totalIssues = $drifts + $unbalanced;

        if ($totalIssues > 0) {
            $msg = "المطابقة اليومية: {$totalIssues} مشكلة";

            if ($this->option('fix') && $drifts > 0) {
                $this->info('إصلاح الأرصدة المنحرفة...');
                $this->balanceService->recalculateAll();
                $msg .= ' (تم إصلاح الأرصدة)';
            }

            if ($this->option('notify')) {
                Log::channel('slack')->warning($msg);
            }

            Log::warning("[AccountingReconcile] {$msg}");
            $this->warn($msg);

            return self::FAILURE;
        }

        $this->info('المطابقة اليومية ناجحة — لا فروقات.');
        return self::SUCCESS;
    }

    private function checkBalanceDrift(): int
    {
        $drifts = 0;
        $accounts = Account::where('is_leaf', true)->where('is_active', true)->get();

        foreach ($accounts as $account) {
            $stored = AccountBalance::where('account_id', $account->id)->first();
            $storedDebit  = (float) ($stored->ytd_debit ?? 0);
            $storedCredit = (float) ($stored->ytd_credit ?? 0);

            $actual = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c')
                ->first();

            $actualDebit  = (float) $actual->d;
            $actualCredit = (float) $actual->c;

            if (abs($storedDebit - $actualDebit) > 0.01 || abs($storedCredit - $actualCredit) > 0.01) {
                $this->error("  انحراف: [{$account->code}] {$account->name_ar} — مخزن: D={$storedDebit}/C={$storedCredit} | فعلي: D={$actualDebit}/C={$actualCredit}");
                $drifts++;
            }
        }

        if ($drifts === 0) {
            $this->info('  الأرصدة المتراكمة مطابقة.');
        }

        return $drifts;
    }

    private function checkUnbalancedEntries(): int
    {
        $count = \App\Models\JournalEntry::where('status', 'posted')
            ->whereRaw('ABS(total_debit - total_credit) > 0.01')
            ->count();

        if ($count > 0) {
            $this->error("  {$count} قيد مُعتمَد غير متوازن!");
        } else {
            $this->info('  جميع القيود المعتمدة متوازنة.');
        }

        return $count;
    }
}
