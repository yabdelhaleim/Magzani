<?php

namespace App\Console\Commands;

use App\Models\AccountingPostingFailure;
use App\Services\Accounting\PostingFailureRetryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AccountingRetryFailedPostings extends Command
{
    protected $signature = 'accounting:retry-failures
                            {--max-attempts=3 : الحد الأقصى للمحاولات قبل التوقف}
                            {--limit=50 : عدد السجلات المعالجة في كل تشغيل}';

    protected $description = 'إعادة محاولة ترحيل العمليات الفاشلة (exponential backoff)';

    public function handle(PostingFailureRetryService $retryService): int
    {
        $maxAttempts = (int) $this->option('max-attempts');
        $limit       = (int) $this->option('limit');

        $failures = AccountingPostingFailure::where('resolved', false)
            ->where('attempts', '<', $maxAttempts)
            ->orderBy('failed_at')
            ->limit($limit)
            ->get();

        if ($failures->isEmpty()) {
            $this->info('لا توجد ترحيلات فاشلة تحتاج إعادة محاولة.');
            return self::SUCCESS;
        }

        $this->info("معالجة {$failures->count()} ترحيل فاشل...");
        $success = 0;
        $failed  = 0;

        foreach ($failures as $failure) {
            // exponential backoff: skip if last failure was too recent
            $waitMinutes = min(60, (int) pow(2, max(0, (int) $failure->attempts - 1)));
            if ($failure->failed_at && $failure->failed_at->gt(now()->subMinutes($waitMinutes))) {
                continue;
            }

            $result = $retryService->retry($failure);

            if ($result['success']) {
                $success++;
                $this->line("  ✓ #{$failure->id}: {$result['message']}");
            } else {
                $failed++;
                $this->line("  ✗ #{$failure->id}: {$result['message']}");
            }
        }

        $this->newLine();
        $this->info("اكتمل — نجح: {$success} / فشل: {$failed}");

        if ($failed > 0) {
            Log::warning("[AccountingRetry] {$failed} posting retries failed in scheduled run.");
        }

        return self::SUCCESS;
    }
}
