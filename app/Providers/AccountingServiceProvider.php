<?php

namespace App\Providers;

use App\Services\Accounting\AccountBalanceService;
use App\Services\Accounting\ChartOfAccountsService;
use App\Services\Accounting\FinancialReportService;
use App\Services\Accounting\FiscalPeriodService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\PostingService;
use App\Services\Accounting\PostingFailureRetryService;
use App\Services\Accounting\RecurringJournalEntryService;
use App\Services\Accounting\VatSettlementService;
use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{
    /**
     * Register accounting services in the DI container.
     * كل الخدمات مُسجَّلة كـ singleton لأنها stateless وتُعاد استخدامها.
     */
    public function register(): void
    {
        // ── Core GL services ─────────────────────────────────
        $this->app->singleton(JournalEntryService::class);
        $this->app->singleton(AccountBalanceService::class);
        $this->app->singleton(FiscalPeriodService::class);
        $this->app->singleton(ChartOfAccountsService::class);
        $this->app->singleton(FinancialReportService::class);

        // ── PostingService يعتمد على JournalEntryService و AccountBalanceService
        $this->app->singleton(PostingService::class, function ($app) {
            return new PostingService(
                $app->make(JournalEntryService::class),
                $app->make(AccountBalanceService::class),
            );
        });

        $this->app->singleton(PostingFailureRetryService::class);
        $this->app->singleton(RecurringJournalEntryService::class);
        $this->app->singleton(VatSettlementService::class);
    }

    public function boot(): void
    {
        //
    }
}
