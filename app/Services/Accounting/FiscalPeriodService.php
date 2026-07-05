<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use App\Models\AccountingSetting;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * FiscalPeriodService
 *
 * مسؤول عن:
 *  - إنشاء سنة مالية وأشهرها الـ 12 تلقائياً
 *  - فتح وإغلاق الفترات المالية
 *  - البحث عن الفترة المفتوحة لتاريخ معين
 *  - التحقق من إمكانية الترحيل لتاريخ
 */
class FiscalPeriodService
{
    /**
     * إنشاء سنة مالية جديدة مع فتراتها الشهرية الـ 12 تلقائياً
     */
    public function createFiscalYear(int $year, ?string $name = null): FiscalYear
    {
        return DB::transaction(function () use ($year, $name) {
            $settings = AccountingSetting::first();
            $startMonth = $settings?->fiscal_year_start_month ?? 1;

            $startDate = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
            $endDate   = (clone $startDate)->addMonths(12)->subDay()->endOfDay();

            // التحقق من عدم التداخل مع سنة مالية موجودة
            $conflict = FiscalYear::where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->toDateString())
                  ->where('end_date',   '>=', $startDate->toDateString());
            })->exists();

            if ($conflict) {
                throw new RuntimeException("يوجد سنة مالية مسجلة تتداخل مع الفترة المطلوبة.");
            }

            $fiscalYear = FiscalYear::create([
                'name'       => $name ?? "السنة المالية {$year}",
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'is_closed'  => false,
            ]);

            // إنشاء 12 فترة شهرية
            $current = (clone $startDate);
            for ($i = 1; $i <= 12; $i++) {
                $periodEnd = (clone $current)->endOfMonth();

                FiscalPeriod::create([
                    'fiscal_year_id' => $fiscalYear->id,
                    'name'           => $current->locale('ar')->isoFormat('MMMM YYYY'),
                    'period_number'  => $i,
                    'start_date'     => $current->toDateString(),
                    'end_date'       => $periodEnd->toDateString(),
                    'is_closed'      => false,
                ]);

                $current = (clone $current)->addMonth()->startOfMonth();
            }

            return $fiscalYear->load('periods');
        });
    }

    /**
     * إنشاء السنة المالية الحالية إذا لم تكن موجودة
     */
    public function ensureCurrentYearExists(): FiscalYear
    {
        $now  = now();
        $year = $now->year;

        $existing = FiscalYear::whereYear('start_date', '<=', $now->toDateString())
            ->whereYear('end_date', '>=', $now->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createFiscalYear($year);
    }

    /**
     * إغلاق فترة مالية (لا يمكن الترحيل إليها بعد الإغلاق)
     */
    public function closePeriod(FiscalPeriod $period, int $closedBy): void
    {
        if ($period->is_closed) {
            throw new RuntimeException("الفترة [{$period->name}] مغلقة بالفعل.");
        }

        // التحقق من عدم وجود قيود مسودة في هذه الفترة
        $draftCount = $period->journalEntries()
            ->where('status', 'draft')
            ->count();

        if ($draftCount > 0) {
            throw new RuntimeException(
                "لا يمكن إغلاق الفترة لوجود {$draftCount} قيد في حالة مسودة. يجب اعتمادها أو حذفها أولاً."
            );
        }

        $period->update([
            'is_closed'  => true,
            'closed_at'  => now(),
            'closed_by'  => $closedBy,
        ]);
    }

    /**
     * إعادة فتح فترة مالية (للتصحيح فقط - تتطلب صلاحيات عالية)
     */
    public function reopenPeriod(FiscalPeriod $period): void
    {
        $period->update([
            'is_closed'  => false,
            'closed_at'  => null,
            'closed_by'  => null,
        ]);
    }

    /**
     * إغلاق سنة مالية بالكامل:
     *  1. إغلاق جميع الفترات
     *  2. إنشاء قيود الإقفال الثلاثة عبر ملخص الدخل (3250)
     *  3. تحديث حالة السنة
     */
    public function closeFiscalYear(FiscalYear $fiscalYear, int $closedBy): void
    {
        DB::transaction(function () use ($fiscalYear, $closedBy) {
            // إغلاق الفترات المفتوحة
            foreach ($fiscalYear->periods as $period) {
                if (!$period->is_closed) {
                    $this->closePeriod($period, $closedBy);
                }
            }

            // إنشاء قيود الإقفال
            $this->createClosingEntries($fiscalYear);

            $fiscalYear->update([
                'is_closed'  => true,
                'closed_at'  => now(),
                'closed_by'  => $closedBy,
            ]);
        });
    }

    /**
     * إنشاء قيود إقفال نهاية السنة المالية (3 قيود عبر ملخص الدخل):
     *
     * قيد 1: إقفال الإيرادات → ملخص الدخل
     * قيد 2: إقفال المصروفات → ملخص الدخل
     * قيد 3: ترحيل صافي الدخل → الأرباح المحتجزة
     */
    private function createClosingEntries(FiscalYear $fiscalYear): void
    {
        $settings = AccountingSetting::first();
        if (!$settings || !$settings->income_summary_account_id || !$settings->retained_earnings_id) {
            return;
        }

        $journalService = app(JournalEntryService::class);
        $closingDate = $fiscalYear->end_date;

        // ── قيد 1: إقفال الإيرادات ──
        $revenueAccounts = Account::where('account_type_id', 4)
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->get();

        $revenueLines = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $acct) {
            $credit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date]))
                ->sum('credit');

            $debit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date]))
                ->sum('debit');

            $balance = $credit - $debit;
            if (abs($balance) > 0.01) {
                $revenueLines[] = [
                    'account_id'  => $acct->id,
                    'debit'       => $balance > 0 ? $balance : 0,
                    'credit'      => $balance < 0 ? abs($balance) : 0,
                    'description' => "إقفال {$acct->name_ar}",
                ];
                $totalRevenue += $balance;
            }
        }

        if (!empty($revenueLines) && abs($totalRevenue) > 0.01) {
            $revenueLines[] = [
                'account_id'  => $settings->income_summary_account_id,
                'debit'       => 0,
                'credit'      => $totalRevenue,
                'description' => 'ملخص الدخل — إقفال الإيرادات',
            ];

            $journalService->createAndPost([
                'entry_date'       => $closingDate,
                'description'      => "إقفال الإيرادات — {$fiscalYear->name}",
                'source_type'      => 'year_closing',
                'source_id'        => $fiscalYear->id,
                'source_event_key' => "year_close:{$fiscalYear->id}:revenue",
                'lines'            => $revenueLines,
            ]);
        }

        // ── قيد 2: إقفال المصروفات ──
        $expenseAccounts = Account::where('account_type_id', 5)
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->get();

        $expenseLines = [];
        $totalExpenses = 0;

        foreach ($expenseAccounts as $acct) {
            $debit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date]))
                ->sum('debit');

            $credit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date]))
                ->sum('credit');

            $balance = $debit - $credit;
            if (abs($balance) > 0.01) {
                $expenseLines[] = [
                    'account_id'  => $acct->id,
                    'debit'       => 0,
                    'credit'      => $balance > 0 ? $balance : 0,
                    'description' => "إقفال {$acct->name_ar}",
                ];
                $totalExpenses += $balance;
            }
        }

        if (!empty($expenseLines) && abs($totalExpenses) > 0.01) {
            $expenseLines[] = [
                'account_id'  => $settings->income_summary_account_id,
                'debit'       => $totalExpenses,
                'credit'      => 0,
                'description' => 'ملخص الدخل — إقفال المصروفات',
            ];

            $journalService->createAndPost([
                'entry_date'       => $closingDate,
                'description'      => "إقفال المصروفات — {$fiscalYear->name}",
                'source_type'      => 'year_closing',
                'source_id'        => $fiscalYear->id,
                'source_event_key' => "year_close:{$fiscalYear->id}:expense",
                'lines'            => $expenseLines,
            ]);
        }

        // ── قيد 3: ترحيل صافي الدخل للأرباح المحتجزة ──
        $netIncome = $totalRevenue - $totalExpenses;
        if (abs($netIncome) > 0.01) {
            $isProfit = $netIncome > 0;

            $journalService->createAndPost([
                'entry_date'       => $closingDate,
                'description'      => ($isProfit ? 'ترحيل صافي الربح' : 'ترحيل صافي الخسارة') . " — {$fiscalYear->name}",
                'source_type'      => 'year_closing',
                'source_id'        => $fiscalYear->id,
                'source_event_key' => "year_close:{$fiscalYear->id}:net_income",
                'lines'            => [
                    [
                        'account_id'  => $settings->income_summary_account_id,
                        'debit'       => $isProfit ? $netIncome : 0,
                        'credit'      => $isProfit ? 0 : abs($netIncome),
                        'description' => 'إقفال ملخص الدخل',
                    ],
                    [
                        'account_id'  => $settings->retained_earnings_id,
                        'debit'       => $isProfit ? 0 : abs($netIncome),
                        'credit'      => $isProfit ? $netIncome : 0,
                        'description' => $isProfit ? 'صافي ربح الفترة' : 'صافي خسارة الفترة',
                    ],
                ],
            ]);
        }
    }

    /**
     * جلب الفترة المفتوحة لتاريخ معين (لاستخدامها في PostingService)
     */
    public function getOpenPeriodForDate(Carbon|string $date): ?FiscalPeriod
    {
        $dateStr = Carbon::parse($date)->toDateString();

        return FiscalPeriod::where('start_date', '<=', $dateStr)
            ->where('end_date',   '>=', $dateStr)
            ->where('is_closed',  false)
            ->first();
    }

    /**
     * التحقق من أن تاريخاً معيناً يقع ضمن فترة مالية مفتوحة
     */
    public function canPostOnDate(Carbon|string $date): bool
    {
        return $this->getOpenPeriodForDate($date) !== null;
    }

    /**
     * جلب الفترة الحالية المفتوحة
     */
    public function getCurrentPeriod(): ?FiscalPeriod
    {
        return $this->getOpenPeriodForDate(now()->toDateString());
    }

    /**
     * قائمة بكل السنوات المالية مع فتراتها
     */
    public function getAllFiscalYears()
    {
        return FiscalYear::with('periods')
            ->orderByDesc('start_date')
            ->get();
    }
}
