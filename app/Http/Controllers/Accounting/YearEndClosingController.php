<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\AccountBalance;
use App\Models\FiscalYear;
use App\Models\JournalEntryLine;
use App\Services\Accounting\FiscalPeriodService;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class YearEndClosingController extends Controller
{
    public function __construct(
        private FiscalPeriodService $fiscalPeriodService,
        private JournalEntryService $journalService
    ) {}

    /**
     * الخطوة 1: اختيار السنة وعرض حالة الإقفال
     */
    public function wizard(Request $request): View
    {
        $fiscalYears = FiscalYear::orderByDesc('start_date')->get();
        $yearId      = $request->get('year_id', $fiscalYears->first()?->id);
        $year        = $yearId ? FiscalYear::with('periods')->find($yearId) : null;

        $preview = $year ? $this->buildPreview($year) : null;

        return view('accounting.fiscal.year-end-wizard', compact('fiscalYears', 'year', 'preview'));
    }

    /**
     * الخطوة 2: تنفيذ الإقفال + إنشاء سنة جديدة اختيارياً
     */
    public function execute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fiscal_year_id'     => 'required|exists:fiscal_years,id',
            'create_next_year'     => 'boolean',
            'create_opening_entry' => 'boolean',
        ]);

        $year = FiscalYear::with('periods')->findOrFail($validated['fiscal_year_id']);

        if ($year->is_closed) {
            return back()->with('error', '❌ هذه السنة مغلقة مسبقاً.');
        }

        try {
            DB::transaction(function () use ($year, $validated) {
                $this->fiscalPeriodService->closeFiscalYear($year, Auth::id());

                if ($request->boolean('create_next_year')) {
                    $nextYearNum = (int) $year->end_date->copy()->addDay()->year;
                    $newYear = $this->fiscalPeriodService->createFiscalYear($nextYearNum);

                    if ($request->boolean('create_opening_entry')) {
                        $this->createOpeningBalances($year, $newYear);
                    }
                }
            });

            return redirect()->route('accounting.fiscal.year-end')
                ->with('success', "✅ تم إقفال السنة المالية [{$year->name}] بنجاح.");
        } catch (\Throwable $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    private function buildPreview(FiscalYear $year): array
    {
        $openPeriods = $year->periods->where('is_closed', false)->count();
        $draftCount  = DB::table('journal_entries')
            ->where('status', 'draft')
            ->whereBetween('entry_date', [$year->start_date, $year->end_date])
            ->count();

        $revenueTotal = 0;
        $expenseTotal = 0;

        $revenueAccounts = Account::where('account_type_id', 4)->where('is_leaf', true)->get();
        foreach ($revenueAccounts as $acct) {
            $credit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$year->start_date, $year->end_date]))
                ->sum('credit');
            $debit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$year->start_date, $year->end_date]))
                ->sum('debit');
            $revenueTotal += ($credit - $debit);
        }

        $expenseAccounts = Account::where('account_type_id', 5)->where('is_leaf', true)->get();
        foreach ($expenseAccounts as $acct) {
            $debit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$year->start_date, $year->end_date]))
                ->sum('debit');
            $credit = (float) JournalEntryLine::where('account_id', $acct->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$year->start_date, $year->end_date]))
                ->sum('credit');
            $expenseTotal += ($debit - $credit);
        }

        return [
            'open_periods'  => $openPeriods,
            'draft_entries' => $draftCount,
            'total_revenue' => round($revenueTotal, 2),
            'total_expenses'=> round($expenseTotal, 2),
            'net_income'    => round($revenueTotal - $expenseTotal, 2),
            'can_close'     => $openPeriods === 0 || true, // periods will be auto-closed
            'has_drafts'    => $draftCount > 0,
        ];
    }

    private function createOpeningBalances(FiscalYear $closedYear, FiscalYear $newYear): void
    {
        $settings = AccountingSetting::first();
        if (!$settings) {
            throw new RuntimeException('الإعدادات المحاسبية غير مُعدّة.');
        }

        $lines = [];
        $balances = AccountBalance::with('account')
            ->whereHas('account', fn ($q) => $q->where('is_leaf', true)->where('is_active', true))
            ->get();

        foreach ($balances as $bal) {
            $debitBal  = (float) $bal->ytd_debit;
            $creditBal = (float) $bal->ytd_credit;
            $netDebit  = round($debitBal - $creditBal, 2);
            $netCredit = round($creditBal - $debitBal, 2);

            if (in_array($bal->account->account_type_id, [4, 5])) {
                continue;
            }

            if ($netDebit > 0.01) {
                $lines[] = [
                    'account_id'  => $bal->account_id,
                    'debit'       => $netDebit,
                    'credit'      => 0,
                    'description' => "رصيد افتتاحي — {$bal->account->name_ar}",
                ];
            } elseif ($netCredit > 0.01) {
                $lines[] = [
                    'account_id'  => $bal->account_id,
                    'debit'       => 0,
                    'credit'      => $netCredit,
                    'description' => "رصيد افتتاحي — {$bal->account->name_ar}",
                ];
            }
        }

        if (empty($lines)) {
            return;
        }

        $this->journalService->createAndPost([
            'entry_date'       => $newYear->start_date->toDateString(),
            'description'      => "أرصدة افتتاحية — {$newYear->name}",
            'source_type'      => 'opening_balance',
            'source_id'        => $newYear->id,
            'source_event_key' => "opening_balance:{$newYear->id}",
            'lines'            => $lines,
        ]);
    }
}
