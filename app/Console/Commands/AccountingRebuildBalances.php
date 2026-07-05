<?php

namespace App\Console\Commands;

use App\Services\Accounting\AccountBalanceService;
use Illuminate\Console\Command;

class AccountingRebuildBalances extends Command
{
    protected $signature = 'accounting:rebuild-balances
                            {--account= : إعادة حساب رصيد حساب واحد فقط (Account ID)}
                            {--dry-run : عرض النتائج بدون تحديث}';

    protected $description = 'إعادة حساب جميع الأرصدة المتراكمة (materialized balances) من القيود المعتمدة';

    public function handle(AccountBalanceService $balanceService): int
    {
        $accountId = $this->option('account');
        $dryRun    = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('وضع المعاينة (dry-run) — لن يتم تحديث أي بيانات.');
        }

        if ($accountId) {
            return $this->rebuildSingle($balanceService, (int) $accountId, $dryRun);
        }

        return $this->rebuildAll($balanceService, $dryRun);
    }

    private function rebuildSingle(AccountBalanceService $balanceService, int $accountId, bool $dryRun): int
    {
        $this->info("إعادة حساب رصيد الحساب #{$accountId}...");

        if ($dryRun) {
            $this->info('✓ سيتم إعادة حساب الرصيد عند التشغيل الفعلي.');
            return self::SUCCESS;
        }

        try {
            $balance = $balanceService->recalculateAccount($accountId);
            $this->info("✓ تم — الرصيد: {$balance->balance} (مدين: {$balance->ytd_debit} / دائن: {$balance->ytd_credit})");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✗ فشل: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function rebuildAll(AccountBalanceService $balanceService, bool $dryRun): int
    {
        $this->info('بدء إعادة حساب جميع الأرصدة المتراكمة...');
        $this->newLine();

        if ($dryRun) {
            $accounts = \App\Models\Account::where('is_leaf', true)
                ->where('is_active', true)
                ->count();
            $this->info("✓ سيتم إعادة حساب {$accounts} حساب ورقي نشط عند التشغيل الفعلي.");
            return self::SUCCESS;
        }

        $startTime = microtime(true);

        $bar = $this->output->createProgressBar(
            \App\Models\Account::where('is_leaf', true)->where('is_active', true)->count()
        );
        $bar->start();

        $success = 0;
        $failed  = 0;

        $accounts = \App\Models\Account::where('is_leaf', true)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($accounts as $id) {
            try {
                $balanceService->recalculateAccount($id);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("  ✗ حساب #{$id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info("✓ اكتمل في {$elapsed} ثانية — نجح: {$success} / فشل: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
