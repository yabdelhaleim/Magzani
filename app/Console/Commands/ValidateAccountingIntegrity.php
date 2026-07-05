<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use App\Models\JournalEntryLine;
use App\Services\Accounting\AccountBalanceService;
use Illuminate\Support\Facades\DB;

class ValidateAccountingIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:validate-integrity {--fix : إعادة احتساب وإصلاح الأرصدة المتراكمة المتناقضة}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'التحقق من صحة وسلامة البيانات المحاسبية والتوازن الحسابي في النظام';

    public function __construct(
        private AccountBalanceService $balanceService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 جاري فحص سلامة البيانات والقيود المحاسبية...');
        $hasErrors = false;

        // 1. التحقق من توازن القيود المرحلة (Debit = Credit)
        $this->info('1️⃣ فحص القيود المرحلة غير المتوازنة...');
        $unbalancedEntries = JournalEntry::where('status', 'posted')
            ->whereRaw('ABS(total_debit - total_credit) > 0.01')
            ->get(['id', 'entry_number', 'total_debit', 'total_credit', 'entry_date']);

        if ($unbalancedEntries->isNotEmpty()) {
            $this->error('❌ تم العثور على قيود مرحلة غير متوازنة:');
            $headers = ['رقم القيد', 'التاريخ', 'إجمالي المدين', 'إجمالي الدائن', 'الفرق'];
            $data = $unbalancedEntries->map(function ($je) {
                return [
                    $je->entry_number,
                    $je->entry_date,
                    number_format($je->total_debit, 2),
                    number_format($je->total_credit, 2),
                    number_format(abs($je->total_debit - $je->total_credit), 2),
                ];
            })->toArray();
            $this->table($headers, $data);
            $hasErrors = true;
        } else {
            $this->info('✅ جميع القيود المرحلة متوازنة حسابياً (مدين = دائن).');
        }

        // 2. التحقق من سلامة الأرصدة المتراكمة (Materialized Balances vs Journal Lines)
        $this->info('2️⃣ فحص تطابق الأرصدة المتراكمة مع تفاصيل دفتر الأستاذ...');
        $mismatches = [];
        $accounts = Account::where('is_leaf', true)->get();

        foreach ($accounts as $account) {
            $balanceRow = AccountBalance::where('account_id', $account->id)->first();
            $storedBalance = $balanceRow ? (float)$balanceRow->balance : 0.0;
            $storedDebit   = $balanceRow ? (float)$balanceRow->ytd_debit : 0.0;
            $storedCredit  = $balanceRow ? (float)$balanceRow->ytd_credit : 0.0;

            // حساب المجموع الفعلي من سطور القيود المعتمدة
            $actual = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            $actualDebit  = (float)($actual->total_debit ?? 0.0);
            $actualCredit = (float)($actual->total_credit ?? 0.0);

            // حساب الرصيد الصافي الفعلي حسب طبيعة الحساب
            $normalBalance = $account->accountType?->normal_balance;
            $normal = ($normalBalance instanceof \App\Enums\NormalBalance) ? $normalBalance->value : ($normalBalance ?? 'debit');
            $actualBalance = $normal === 'debit' 
                ? round($actualDebit - $actualCredit, 2)
                : round($actualCredit - $actualDebit, 2);

            if (abs($storedBalance - $actualBalance) > 0.01 || 
                abs($storedDebit - $actualDebit) > 0.01 || 
                abs($storedCredit - $actualCredit) > 0.01) {
                
                $mismatches[] = [
                    'code' => $account->code,
                    'name' => $account->name_ar,
                    'stored_bal' => $storedBalance,
                    'actual_bal' => $actualBalance,
                    'stored_deb' => $storedDebit,
                    'actual_deb' => $actualDebit,
                    'stored_crd' => $storedCredit,
                    'actual_crd' => $actualCredit,
                    'account_id' => $account->id
                ];
            }
        }

        if (!empty($mismatches)) {
            $this->error('❌ تم العثور على عدم تطابق في أرصدة ' . count($mismatches) . ' حساب(ات):');
            $headers = ['كود الحساب', 'الاسم', 'الرصيد المخزن', 'الرصيد الفعلي', 'الفرق'];
            $tableData = array_map(function ($m) {
                return [
                    $m['code'],
                    $m['name'],
                    number_format($m['stored_bal'], 2),
                    number_format($m['actual_bal'], 2),
                    number_format(abs($m['stored_bal'] - $m['actual_bal']), 2),
                ];
            }, $mismatches);
            $this->table($headers, $tableData);

            if ($this->option('fix')) {
                $this->info('🔄 جاري إصلاح وإعادة حساب الأرصدة المتراكمة...');
                foreach ($mismatches as $m) {
                    $this->balanceService->recalculateAccount($m['account_id']);
                    $this->info("🔧 تم إصلاح رصيد الحساب: {$m['code']} - {$m['name']}");
                }
                $this->info('✅ تم إصلاح جميع الأرصدة المتناقضة بنجاح.');
            } else {
                $this->warn('💡 تشغيل الأمر مع الخيار --fix سيقوم بإصلاح الأرصدة المخزنة وتعديلها لتطابق تفاصيل الحركات.');
                $hasErrors = true;
            }
        } else {
            $this->info('✅ الأرصدة المتراكمة مطابقة بالكامل لتفاصيل دفتر الأستاذ.');
        }

        // 3. التحقق من المعادلة الميزانية الأساسية (الأصول = الخصوم + حقوق الملكية)
        $this->info('3️⃣ فحص توازن المعادلة الميزانية (الميزانية العمومية)...');
        $assetsType = 1; // الأصول
        $liabilitiesType = 2; // الخصوم
        $equityType = 3; // حقوق الملكية

        $totalAssets = AccountBalance::whereHas('account', fn($q) => $q->where('account_type_id', $assetsType)->whereNull('parent_id'))->sum('balance');
        $totalLiabilities = AccountBalance::whereHas('account', fn($q) => $q->where('account_type_id', $liabilitiesType)->whereNull('parent_id'))->sum('balance');
        $totalEquity = AccountBalance::whereHas('account', fn($q) => $q->where('account_type_id', $equityType)->whereNull('parent_id'))->sum('balance');
        
        // الأرباح والخسائر غير المرحلة (Income Statement Net Income) تؤثر على الميزانية
        // لمعادلة الميزانية بدقة: الأصول = الخصوم + حقوق الملكية + صافي دخل الفترة الحالية
        $postedRevenues = JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type_id', 4))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit') - JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type_id', 4))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');

        $postedExpenses = JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type_id', 5))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit') - JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type_id', 5))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit');

        $currentNetIncome = $postedRevenues - $postedExpenses;

        $rightSide = $totalAssets;
        $leftSide  = $totalLiabilities + $totalEquity + $currentNetIncome;
        $difference = abs($rightSide - $leftSide);

        if ($difference > 0.01) {
            $this->error('❌ خلل في المعادلة الميزانية الأساسية!');
            $this->error("   إجمالي الأصول (الأطراف المدينة): " . number_format($rightSide, 2));
            $this->error("   إجمالي الخصوم وحقوق الملكية وصافي الدخل (الأطراف الدائنة): " . number_format($leftSide, 2));
            $this->error("   قيمة الفرق/الخلل: " . number_format($difference, 2));
            $hasErrors = true;
        } else {
            $this->info("✅ المعادلة الميزانية متوازنة تماماً (الأصول = الخصوم + حقوق الملكية).");
            $this->info("   الأصول: " . number_format($rightSide, 2) . " | الخصوم والملكيات: " . number_format($leftSide, 2));
        }

        // 4. التحقق من الفترات المالية والسنوات
        $this->info('4️⃣ فحص سلامة الفترات والسنوات المالية...');
        $overlappingYears = DB::select("
            SELECT a.id AS id1, b.id AS id2, a.name AS name1, b.name AS name2
            FROM fiscal_years a
            JOIN fiscal_years b ON a.id < b.id
            WHERE (a.start_date <= b.end_date AND a.end_date >= b.start_date)
        ");

        if (!empty($overlappingYears)) {
            $this->error('❌ تم اكتشاف تداخل بين السنوات المالية التالية:');
            foreach ($overlappingYears as $row) {
                $this->error("   - السنة [{$row->name1}] تتداخل مع السنة [{$row->name2}]");
            }
            $hasErrors = true;
        } else {
            $this->info('✅ لا يوجد تداخل بين السنوات المالية.');
        }

        // قيود خارج الفترات المالية أو بدون فترة مالية
        $orphanedEntries = JournalEntry::join('fiscal_periods', 'fiscal_periods.id', '=', 'journal_entries.fiscal_period_id')
            ->where(function ($q) {
                $q->whereColumn('journal_entries.entry_date', '<', 'fiscal_periods.start_date')
                  ->orWhereColumn('journal_entries.entry_date', '>', 'fiscal_periods.end_date');
            })
            ->select([
                'journal_entries.id',
                'journal_entries.entry_number',
                'journal_entries.entry_date',
                'fiscal_periods.name as period_name'
            ])
            ->get();

        $noPeriodEntries = JournalEntry::whereNull('fiscal_period_id')
            ->get(['id', 'entry_number', 'entry_date']);

        if ($orphanedEntries->isNotEmpty() || $noPeriodEntries->isNotEmpty()) {
            $this->error('❌ تم اكتشاف مشاكل في الفترات المالية للقيود:');
            
            foreach ($orphanedEntries as $je) {
                $this->error("   - القيد [{$je->entry_number}] بتاريخ [{$je->entry_date->toDateString()}] يقع خارج نطاق فترته المالية [{$je->period_name}].");
            }

            foreach ($noPeriodEntries as $je) {
                $this->error("   - القيد [{$je->entry_number}] بتاريخ [{$je->entry_date->toDateString()}] ليس له فترة مالية محددة (fiscal_period_id is NULL).");
            }
            $hasErrors = true;
        } else {
            $this->info('✅ جميع القيود تقع ضمن فترات مالية صالحة ومحددة.');
        }

        // 5. التحقق من عدم وجود source_event_key مكرر
        $this->info('5️⃣ فحص مفاتيح الترحيل المكررة (duplicate source_event_key)...');
        $duplicateKeys = JournalEntry::select('source_event_key')
            ->whereNotNull('source_event_key')
            ->where('status', 'posted')
            ->groupBy('source_event_key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('source_event_key');

        if ($duplicateKeys->isNotEmpty()) {
            $this->error('❌ تم اكتشاف ' . $duplicateKeys->count() . ' مفتاح ترحيل مكرر:');
            foreach ($duplicateKeys as $key) {
                $count = JournalEntry::where('source_event_key', $key)->where('status', 'posted')->count();
                $this->error("   - [{$key}] → {$count} قيد مُعتمَد");
            }
            $hasErrors = true;
        } else {
            $this->info('✅ لا توجد مفاتيح ترحيل مكررة.');
        }

        // 6. فحص ميزان المراجعة (Trial Balance)
        $this->info('6️⃣ فحص ميزان المراجعة (مجموع الأرصدة المدينة = مجموع الأرصدة الدائنة)...');
        $trialTotals = JournalEntryLine::whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $trialDebit  = (float) ($trialTotals->total_debit ?? 0);
        $trialCredit = (float) ($trialTotals->total_credit ?? 0);
        $trialDiff   = abs($trialDebit - $trialCredit);

        if ($trialDiff > 0.01) {
            $this->error("❌ ميزان المراجعة غير متوازن!");
            $this->error("   مجموع المدين: " . number_format($trialDebit, 2));
            $this->error("   مجموع الدائن: " . number_format($trialCredit, 2));
            $this->error("   الفرق: " . number_format($trialDiff, 2));
            $hasErrors = true;
        } else {
            $this->info("✅ ميزان المراجعة متوازن — المدين: " . number_format($trialDebit, 2) . " | الدائن: " . number_format($trialCredit, 2));
        }

        // 7. فحص الحلقات الدائرية في شجرة الحسابات
        $this->info('7️⃣ فحص الحلقات الدائرية في شجرة الحسابات...');
        $circularFound = false;
        $allAccounts = Account::whereNotNull('parent_id')->get(['id', 'code', 'name_ar', 'parent_id']);

        foreach ($allAccounts as $acct) {
            $visited = [$acct->id];
            $current = $acct->parent_id;
            $depth = 0;

            while ($current !== null && $depth < 20) {
                if (in_array($current, $visited)) {
                    $this->error("   ❌ حلقة دائرية في الحساب [{$acct->code}] {$acct->name_ar}");
                    $circularFound = true;
                    $hasErrors = true;
                    break;
                }
                $visited[] = $current;
                $parent = $allAccounts->firstWhere('id', $current);
                $current = $parent?->parent_id;
                $depth++;
            }
        }

        if (!$circularFound) {
            $this->info('✅ لا توجد حلقات دائرية في شجرة الحسابات.');
        }

        // 8. فحص تسلسل أرقام القيود (فجوات)
        $this->info('8️⃣ فحص تسلسل أرقام القيود...');
        $entryNumbers = JournalEntry::whereYear('created_at', now()->year)
            ->orderBy('id')
            ->pluck('entry_number');

        $gaps = 0;
        $lastSeq = 0;

        foreach ($entryNumbers as $num) {
            if (preg_match('/(\d+)$/', $num, $m)) {
                $seq = (int) $m[1];
                if ($lastSeq > 0 && $seq !== $lastSeq + 1) {
                    $gaps++;
                }
                $lastSeq = $seq;
            }
        }

        if ($gaps > 0) {
            $this->warn("⚠️ تم اكتشاف {$gaps} فجوة في تسلسل أرقام القيود للسنة الحالية.");
        } else {
            $this->info('✅ تسلسل أرقام القيود سليم (لا فجوات).');
        }

        // 9. فشل ترحيل غير مُعالَج
        $this->info('9️⃣ فحص فشل الترحيل المعلّق...');
        $pendingFailures = \App\Models\AccountingPostingFailure::where('resolved', false)->count();

        if ($pendingFailures > 0) {
            $this->warn("⚠️ يوجد {$pendingFailures} عملية ترحيل فاشلة لم تُعالَج بعد.");
            $hasErrors = true;
        } else {
            $this->info('✅ لا توجد عمليات ترحيل فاشلة معلّقة.');
        }

        $this->newLine();

        if ($hasErrors) {
            $this->error('❌ فشل فحص السلامة المحاسبية. يرجى مراجعة وتصحيح المشاكل أعلاه.');
            return 1;
        }

        $this->info('🎉 فحص السلامة انتهى بنجاح! البيانات والقيود متكاملة 100%.');
        return 0;
    }
}
