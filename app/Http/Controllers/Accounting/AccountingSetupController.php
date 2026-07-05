<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\AccountType;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingSetupController extends Controller
{
    public function __construct(
        private JournalEntryService $journalService,
    ) {}

    /**
     * عرض الخطوة الحالية من المعالج
     */
    public function index()
    {
        $settings = AccountingSetting::first();
        $step = $this->detectCurrentStep($settings);

        return redirect()->route('accounting.setup.step', $step);
    }

    /**
     * عرض خطوة محددة
     */
    public function step(int $step)
    {
        $settings = AccountingSetting::first();

        return match ($step) {
            1 => $this->stepChart($settings),
            2 => $this->stepFiscalYear($settings),
            3 => $this->stepOpeningBalances($settings),
            4 => $this->stepAutoPosting($settings),
            5 => $this->stepVerify($settings),
            default => redirect()->route('accounting.setup.step', 1),
        };
    }

    // ── Step 1: مراجعة دليل الحسابات ──

    private function stepChart(?AccountingSetting $settings)
    {
        $accounts = Account::with('accountType', 'parent')
            ->orderBy('code')
            ->get();

        $types = AccountType::all();

        return view('accounting.setup.step1-chart', compact('accounts', 'types', 'settings'));
    }

    public function saveChart(Request $request)
    {
        // تأكيد أن الدليل مزروع — لو لم يُزرع نزرعه
        if (Account::count() === 0) {
            \Artisan::call('db:seed', ['--class' => 'DefaultChartOfAccountsSeeder', '--force' => true]);
        }

        return redirect()
            ->route('accounting.setup.step', 2)
            ->with('success', 'تم تأكيد دليل الحسابات بنجاح.');
    }

    // ── Step 2: إعداد السنة المالية ──

    private function stepFiscalYear(?AccountingSetting $settings)
    {
        $fiscalYears = FiscalYear::with('periods')->orderByDesc('start_date')->get();

        return view('accounting.setup.step2-fiscal-year', compact('fiscalYears', 'settings'));
    }

    public function saveFiscalYear(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        DB::transaction(function () use ($request) {
            $year = FiscalYear::create([
                'name'       => $request->name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'is_closed'  => false,
                'is_current' => true,
            ]);

            // إزالة is_current من السنوات السابقة
            FiscalYear::where('id', '!=', $year->id)->update(['is_current' => false]);

            // إنشاء فترات شهرية تلقائياً
            $start = Carbon::parse($request->start_date);
            $end   = Carbon::parse($request->end_date);

            $periodStart = $start->copy();
            $periodNumber = 1;

            while ($periodStart->lt($end)) {
                $periodEnd = $periodStart->copy()->endOfMonth();
                if ($periodEnd->gt($end)) {
                    $periodEnd = $end->copy();
                }

                FiscalPeriod::create([
                    'fiscal_year_id' => $year->id,
                    'name'           => $periodStart->translatedFormat('F Y'),
                    'period_number'  => $periodNumber,
                    'start_date'     => $periodStart->toDateString(),
                    'end_date'       => $periodEnd->toDateString(),
                    'is_closed'      => false,
                ]);

                $periodStart = $periodEnd->copy()->addDay()->startOfDay();
                $periodNumber++;
            }
        });

        return redirect()
            ->route('accounting.setup.step', 3)
            ->with('success', 'تم إنشاء السنة المالية والفترات الشهرية بنجاح.');
    }

    // ── Step 3: إدخال أرصدة افتتاحية ──

    private function stepOpeningBalances(?AccountingSetting $settings)
    {
        $accounts = Account::with('accountType', 'balance')
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $hasOpeningEntry = JournalEntry::where('source_event_key', 'opening_balance:initial')->exists();

        return view('accounting.setup.step3-opening-balances', compact('accounts', 'settings', 'hasOpeningEntry'));
    }

    public function saveOpeningBalances(Request $request)
    {
        $request->validate([
            'balances'          => 'required|array',
            'balances.*.debit'  => 'nullable|numeric|min:0',
            'balances.*.credit' => 'nullable|numeric|min:0',
        ]);

        $lines = [];

        foreach ($request->balances as $accountId => $amounts) {
            $debit  = (float) ($amounts['debit'] ?? 0);
            $credit = (float) ($amounts['credit'] ?? 0);

            if ($debit > 0 || $credit > 0) {
                $lines[] = [
                    'account_id'  => $accountId,
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'description' => 'رصيد افتتاحي',
                ];
            }
        }

        if (empty($lines)) {
            return redirect()
                ->route('accounting.setup.step', 4)
                ->with('info', 'لم يتم إدخال أرصدة افتتاحية — تم التخطي.');
        }

        $this->journalService->createAndPost([
            'entry_date'       => FiscalYear::where('is_current', true)->value('start_date') ?? now()->toDateString(),
            'description'      => 'قيد الأرصدة الافتتاحية',
            'source_type'      => 'opening_balance',
            'source_id'        => null,
            'source_event_key' => 'opening_balance:initial',
            'lines'            => $lines,
        ]);

        return redirect()
            ->route('accounting.setup.step', 4)
            ->with('success', 'تم حفظ الأرصدة الافتتاحية بنجاح.');
    }

    // ── Step 4: إعدادات الترحيل التلقائي ──

    private function stepAutoPosting(?AccountingSetting $settings)
    {
        return view('accounting.setup.step4-auto-posting', compact('settings'));
    }

    public function saveAutoPosting(Request $request)
    {
        $settings = AccountingSetting::firstOrCreate(['id' => 1]);

        $settings->update([
            'auto_post_invoices'      => $request->boolean('auto_post_invoices'),
            'auto_post_payments'      => $request->boolean('auto_post_payments'),
            'auto_post_expenses'      => $request->boolean('auto_post_expenses'),
            'auto_post_manufacturing' => $request->boolean('auto_post_manufacturing'),
        ]);

        return redirect()
            ->route('accounting.setup.step', 5)
            ->with('success', 'تم حفظ إعدادات الترحيل التلقائي.');
    }

    // ── Step 5: التحقق والتفعيل ──

    private function stepVerify(?AccountingSetting $settings)
    {
        $checks = [
            'chart'   => Account::count() > 0,
            'fiscal'  => FiscalYear::where('is_current', true)->exists(),
            'periods' => FiscalPeriod::where('is_closed', false)->exists(),
            'settings' => $settings !== null,
        ];

        return view('accounting.setup.step5-verify', compact('checks', 'settings'));
    }

    public function complete()
    {
        return redirect()
            ->route('accounting.dashboard')
            ->with('success', 'تم إعداد النظام المحاسبي بنجاح! مرحباً بك.');
    }

    // ── Helpers ──

    private function detectCurrentStep(?AccountingSetting $settings): int
    {
        if (Account::count() === 0) return 1;
        if (!FiscalYear::where('is_current', true)->exists()) return 2;
        if (!JournalEntry::where('source_event_key', 'opening_balance:initial')->exists()) return 3;
        if (!$settings) return 4;

        return 5;
    }
}
