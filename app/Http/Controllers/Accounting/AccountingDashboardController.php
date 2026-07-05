<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\FinancialReportService;
use App\Services\Accounting\FiscalPeriodService;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\AccountBalance;
use App\Models\AccountingPostingFailure;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Support\Facades\Artisan;

class AccountingDashboardController extends Controller
{
    public function __construct(
        private FinancialReportService $reportService,
        private FiscalPeriodService $fiscalPeriodService
    ) {}

    /**
     * لوحة تحكم المحاسبة الرئيسية
     *
     * إصلاح #10:
     * - FiscalYear لا يملك is_current → نستخدم is_closed=false + أقرب تاريخ
     * - FiscalPeriod لا يملك status → يملك is_closed (boolean)
     * - JournalEntry relation اسمها createdBy غير موجود → نستخدم creator() أو نتجنبها
     */
    public function index(): View
    {
        // السنة المالية الحالية = غير مغلقة وتحتوي التاريخ الحالي
        $currentYear = FiscalYear::where('is_closed', false)
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date',   '>=', now()->toDateString())
            ->orderByDesc('start_date')
            ->first();

        // الفترة الحالية = غير مغلقة وتحتوي التاريخ الحالي
        $currentPeriod = $currentYear
            ? FiscalPeriod::where('fiscal_year_id', $currentYear->id)
                ->where('is_closed', false)           // ✅ is_closed بدلاً من status
                ->where('start_date', '<=', now()->toDateString())
                ->where('end_date',   '>=', now()->toDateString())
                ->first()
            : null;

        // إحصاءات سريعة
        $stats = [
            'total_entries'   => JournalEntry::count(),
            'posted_entries'  => JournalEntry::where('status', 'posted')->count(),
            'draft_entries'   => JournalEntry::where('status', 'draft')->count(),
            'unbalanced'      => JournalEntry::where('status', 'draft')
                ->whereRaw('ABS(total_debit - total_credit) > 0.01')
                ->count(),
        ];

        // أرصدة الحسابات الرئيسية (Level 1)
        $keyBalances = AccountBalance::with('account')
            ->whereHas('account', fn ($q) => $q->whereNull('parent_id'))
            ->get()
            ->keyBy('account.code');

        // آخر 10 قيود — بدون eagerly loading createdBy (غير موجودة)
        $recentEntries = JournalEntry::with('lines.account')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // تحذيرات: قيود مسودة أقدم من 3 أيام
        $staleDrafts = JournalEntry::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        // ترحيلات فاشلة معلّقة
        $pendingPostingFailures = AccountingPostingFailure::where('resolved', false)->count();

        // نسب مالية سريعة
        $ratios = $this->reportService->financialRatios();

        return view('accounting.dashboard', compact(
            'currentYear',
            'currentPeriod',
            'stats',
            'keyBalances',
            'recentEntries',
            'staleDrafts',
            'pendingPostingFailures',
            'ratios',
        ));
    }

    /**
     * التحقق من سلامة التوازن (API endpoint للـ health check)
     */
    public function integrityCheck(): \Illuminate\Http\JsonResponse
    {
        $unbalanced = JournalEntry::where('status', 'posted')
            ->whereRaw('ABS(total_debit - total_credit) > 0.01')
            ->select('id', 'entry_number', 'total_debit', 'total_credit')
            ->limit(20)
            ->get();

        return response()->json([
            'is_balanced'  => $unbalanced->isEmpty(),
            'issues_count' => $unbalanced->count(),
            'issues'       => $unbalanced,
            'checked_at'   => now()->toIso8601String(),
        ]);
    }

    /**
     * تشغيل أداة الإصلاح التلقائي للأرصدة
     */
    public function runIntegrityFix(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            Artisan::call('accounting:validate-integrity', ['--fix' => true]);
            $output = Artisan::output();

            return redirect()->route('accounting.dashboard')
                ->with('success', '✅ تم تشغيل أداة الإصلاح بنجاح! تفاصيل العملية: ' . nl2br($output));
        } catch (\Throwable $e) {
            return redirect()->route('accounting.dashboard')
                ->with('error', '❌ فشل تشغيل أداة الإصلاح: ' . $e->getMessage());
        }
    }
}
