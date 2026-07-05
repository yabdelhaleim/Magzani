<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\AccountType;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FinancialReportService
 *
 * يُنتج التقارير المالية الأساسية الأربعة:
 *  1. ميزان المراجعة (Trial Balance)
 *  2. قائمة الدخل (Income Statement / P&L)
 *  3. الميزانية العمومية (Balance Sheet)
 *  4. دفتر الأستاذ العام (General Ledger) لحساب معين
 *
 * كل تقرير يُعيد مصفوفة بيانات منظمة يسهل عرضها في View أو تصديرها لـ PDF/Excel.
 */
class FinancialReportService
{
    // ═══════════════════════════════════════════════════════════
    // 1. TRIAL BALANCE
    // ═══════════════════════════════════════════════════════════

    /**
     * ميزان المراجعة — يُجمع مدين/دائن لكل حساب ورقي حتى تاريخ معين
     *
     * @param string|null $asOf  تاريخ نهاية الميزان (الافتراضي: اليوم)
     * @return array{
     *   as_of: string,
     *   accounts: array,
     *   total_debit: float,
     *   total_credit: float,
     *   is_balanced: bool
     * }
     */
    public function trialBalance(?string $asOf = null): array
    {
        $asOf = $asOf ?? now()->toDateString();

        $rows = JournalEntryLine::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOf)
            ->whereNull('accounts.deleted_at')
            ->selectRaw('
                accounts.id           AS account_id,
                accounts.code         AS account_code,
                accounts.name_ar      AS account_name,
                account_types.name_ar AS type_name,
                account_types.normal_balance AS normal_balance,
                SUM(journal_entry_lines.debit)  AS total_debit,
                SUM(journal_entry_lines.credit) AS total_credit
            ')
            ->groupBy(
                'accounts.id',
                'accounts.code',
                'accounts.name_ar',
                'account_types.name_ar',
                'account_types.normal_balance'
            )
            ->orderBy('accounts.code')
            ->get();

        $accounts   = [];
        $totalDebit  = 0.0;
        $totalCredit = 0.0;

        foreach ($rows as $row) {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            // الرصيد الصافي حسب الطبيعة
            if ($row->normal_balance === 'debit') {
                $balance = $debit - $credit;
                $balanceDebit  = $balance >= 0 ? $balance : 0;
                $balanceCredit = $balance < 0  ? abs($balance) : 0;
            } else {
                $balance = $credit - $debit;
                $balanceCredit = $balance >= 0 ? $balance : 0;
                $balanceDebit  = $balance < 0  ? abs($balance) : 0;
            }

            $accounts[] = [
                'account_id'     => $row->account_id,
                'code'           => $row->account_code,
                'name'           => $row->account_name,
                'type'           => $row->type_name,
                'total_debit'    => round($debit, 2),
                'total_credit'   => round($credit, 2),
                'balance_debit'  => round($balanceDebit, 2),
                'balance_credit' => round($balanceCredit, 2),
            ];

            $totalDebit  += $balanceDebit;
            $totalCredit += $balanceCredit;
        }

        return [
            'as_of'        => $asOf,
            'accounts'     => $accounts,
            'total_debit'  => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced'  => abs($totalDebit - $totalCredit) <= 0.01,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // 2. INCOME STATEMENT (P&L)
    // ═══════════════════════════════════════════════════════════

    /**
     * قائمة الدخل (الأرباح والخسائر) لفترة زمنية
     *
     * @return array{
     *   from: string,
     *   to: string,
     *   revenues: array,
     *   expenses: array,
     *   total_revenue: float,
     *   total_expense: float,
     *   net_income: float
     * }
     */
    public function incomeStatement(string $from, string $to): array
    {
        $revenues = $this->sumAccountsByType('revenue', $from, $to);
        $expenses = $this->sumAccountsByType('expense', $from, $to);

        $totalRevenue = collect($revenues)->sum('net_balance');
        $totalExpense = collect($expenses)->sum('net_balance');

        return [
            'from'            => $from,
            'to'              => $to,
            'revenues'        => $revenues,
            'expenses'        => $expenses,
            'total_revenue'   => round($totalRevenue, 2),
            'total_expense'   => round($totalExpense, 2),
            // ✅ Aliases للـ Views التي تستخدم الصيغة الجمع
            'total_revenues'  => round($totalRevenue, 2),
            'total_expenses'  => round($totalExpense, 2),
            'net_income'      => round($totalRevenue - $totalExpense, 2),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // 3. BALANCE SHEET
    // ═══════════════════════════════════════════════════════════

    /**
     * الميزانية العمومية حتى تاريخ معين
     *
     * @return array{
     *   as_of: string,
     *   assets: array,
     *   liabilities: array,
     *   equity: array,
     *   total_assets: float,
     *   total_liabilities_equity: float,
     *   is_balanced: bool
     * }
     */
    public function balanceSheet(?string $asOf = null): array
    {
        $asOf = $asOf ?? now()->toDateString();

        $assets      = $this->sumAccountsByType('asset',     null, $asOf);
        $liabilities = $this->sumAccountsByType('liability', null, $asOf);
        $equity      = $this->sumAccountsByType('equity',    null, $asOf);

        // إضافة صافي الدخل للإيرادات المحتجزة
        $incomeFrom = $this->getCurrentYearStart();
        $income = $this->incomeStatement($incomeFrom, $asOf);
        $retainedEarningsAdj = $income['net_income'];

        $totalAssets      = collect($assets)->sum('net_balance');
        $totalLiabilities = collect($liabilities)->sum('net_balance');
        $totalEquity      = collect($equity)->sum('net_balance') + $retainedEarningsAdj;

        return [
            'as_of'                    => $asOf,
            'assets'                   => $assets,
            'liabilities'              => $liabilities,
            'equity'                   => $equity,
            'net_income_ytd'           => round($retainedEarningsAdj, 2),
            'total_assets'             => round($totalAssets, 2),
            'total_liabilities'        => round($totalLiabilities, 2),
            'total_equity'             => round($totalEquity, 2),
            'total_liabilities_equity' => round($totalLiabilities + $totalEquity, 2),
            'is_balanced'              => abs($totalAssets - ($totalLiabilities + $totalEquity)) <= 0.01,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // 4. GENERAL LEDGER (ACCOUNT LEDGER)
    // ═══════════════════════════════════════════════════════════

    /**
     * دفتر الأستاذ لحساب معين (كشف حركة الحساب)
     *
     * @return array{
     *   account: Account,
     *   from: string,
     *   to: string,
     *   opening_balance: float,
     *   lines: array,
     *   closing_balance: float
     * }
     */
    public function generalLedger(int $accountId, string $from, string $to): array
    {
        $account = Account::with('accountType')->findOrFail($accountId);

        // الرصيد الافتتاحي (كل الحركات قبل from)
        $openingData = JournalEntryLine::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<', $from)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $openDebit  = (float) ($openingData->total_debit  ?? 0);
        $openCredit = (float) ($openingData->total_credit ?? 0);
        $normalBalEnum = $account->accountType?->normal_balance;
        $normalBal = ($normalBalEnum instanceof \App\Enums\NormalBalance) ? $normalBalEnum->value : ($normalBalEnum ?? 'debit');
        $openingBal = $normalBal === 'debit' ? ($openDebit - $openCredit) : ($openCredit - $openDebit);

        // الحركات ضمن الفترة
        $rawLines = JournalEntryLine::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$from, $to])
            ->select(
                'journal_entries.entry_date',
                'journal_entries.entry_number',
                'journal_entries.description AS entry_description',
                'journal_entry_lines.description AS line_description',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entries.source_type',
                'journal_entries.source_id'
            )
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->get();

        $lines      = [];
        $runBalance = $openingBal;

        foreach ($rawLines as $line) {
            $debit  = (float) $line->debit;
            $credit = (float) $line->credit;

            $runBalance += ($normalBal === 'debit')
                ? ($debit - $credit)
                : ($credit - $debit);

            $lines[] = [
                'date'        => $line->entry_date,
                'entry_no'    => $line->entry_number,
                'description' => $line->line_description ?? $line->entry_description,
                'debit'       => $debit,
                'credit'      => $credit,
                'balance'     => round($runBalance, 2),
                'source_type' => $line->source_type,
                'source_id'   => $line->source_id,
            ];
        }

        return [
            'account'         => $account,
            'from'            => $from,
            'to'              => $to,
            'opening_balance' => round($openingBal, 2),
            'lines'           => $lines,
            'closing_balance' => round($runBalance, 2),
            'total_debit'     => round($rawLines->sum('debit'), 2),
            'total_credit'    => round($rawLines->sum('credit'), 2),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // 5. ACCOUNTS RECEIVABLE / PAYABLE AGING
    // ═══════════════════════════════════════════════════════════

    /**
     * تقرير أعمار الذمم المدينة (عملاء)
     */
    public function arAging(?string $asOf = null): array
    {
        $asOf = $asOf ?? now()->toDateString();

        $invoices = \App\Models\SalesInvoice::where('payment_status', '!=', 'paid')
            ->where('status', 'confirmed')
            ->where('invoice_date', '<=', $asOf)
            ->with('customer')
            ->get();

        return $this->buildAging($invoices, $asOf, 'invoice_date', 'remaining');
    }

    /**
     * تقرير أعمار الذمم الدائنة (موردين)
     */
    public function apAging(?string $asOf = null): array
    {
        $asOf = $asOf ?? now()->toDateString();

        $invoices = \App\Models\PurchaseInvoice::where('payment_status', '!=', 'paid')
            ->where('status', 'confirmed')
            ->where('invoice_date', '<=', $asOf)
            ->with('supplier')
            ->get();

        return $this->buildAging($invoices, $asOf, 'invoice_date', 'remaining');
    }

    /**
     * Unified wrapper — يُستخدَم من FinancialReportController
     * إصلاح #5: Controller كان يستدعي agingReport() وهي غير موجودة
     *
     * @param string $type  'receivable' | 'payable'
     */
    public function agingReport(string $type = 'receivable', ?string $asOf = null): array
    {
        return $type === 'payable'
            ? $this->apAging($asOf)
            : $this->arAging($asOf);
    }

    // ═══════════════════════════════════════════════════════════
    // 6. COMPARATIVE INCOME STATEMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * قائمة دخل مقارنة: الفترة الحالية مقابل الفترة السابقة
     */
    public function comparativeIncomeStatement(string $currentFrom, string $currentTo, string $previousFrom, string $previousTo): array
    {
        $current  = $this->incomeStatement($currentFrom, $currentTo);
        $previous = $this->incomeStatement($previousFrom, $previousTo);

        $mergedRevenues = $this->mergeComparative($current['revenues'], $previous['revenues']);
        $mergedExpenses = $this->mergeComparative($current['expenses'], $previous['expenses']);

        $revenueChange = $current['total_revenue'] - $previous['total_revenue'];
        $expenseChange = $current['total_expense'] - $previous['total_expense'];
        $incomeChange  = $current['net_income'] - $previous['net_income'];

        return [
            'current_period'   => ['from' => $currentFrom, 'to' => $currentTo],
            'previous_period'  => ['from' => $previousFrom, 'to' => $previousTo],
            'revenues'         => $mergedRevenues,
            'expenses'         => $mergedExpenses,
            'current_revenue'  => $current['total_revenue'],
            'previous_revenue' => $previous['total_revenue'],
            'revenue_change'   => round($revenueChange, 2),
            'revenue_change_pct' => $previous['total_revenue'] > 0 ? round(($revenueChange / $previous['total_revenue']) * 100, 1) : null,
            'current_expense'  => $current['total_expense'],
            'previous_expense' => $previous['total_expense'],
            'expense_change'   => round($expenseChange, 2),
            'current_income'   => $current['net_income'],
            'previous_income'  => $previous['net_income'],
            'income_change'    => round($incomeChange, 2),
            'income_change_pct'=> $previous['net_income'] != 0 ? round(($incomeChange / abs($previous['net_income'])) * 100, 1) : null,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // 7. FINANCIAL RATIOS
    // ═══════════════════════════════════════════════════════════

    /**
     * النسب المالية الأساسية
     */
    public function financialRatios(?string $asOf = null): array
    {
        $asOf = $asOf ?? now()->toDateString();
        $bs   = $this->balanceSheet($asOf);

        $yearStart = $this->getCurrentYearStart();
        $pl   = $this->incomeStatement($yearStart, $asOf);

        $totalAssets      = $bs['total_assets'];
        $totalLiabilities = $bs['total_liabilities'];
        $totalEquity      = $bs['total_equity'];
        $totalRevenue     = $pl['total_revenue'];
        $totalExpense     = $pl['total_expense'];
        $netIncome        = $pl['net_income'];
        $cogs             = 0;

        // حساب COGS من المصروفات (5100)
        foreach ($pl['expenses'] as $exp) {
            if ($exp['code'] === '5100') {
                $cogs = $exp['net_balance'];
                break;
            }
        }

        // حساب الأصول المتداولة
        $currentAssets = collect($bs['assets'])->filter(fn($a) => str_starts_with($a['code'], '11'))->sum('net_balance');

        // الذمم المدينة
        $receivables = collect($bs['assets'])->filter(fn($a) => str_starts_with($a['code'], '121'))->sum('net_balance');

        // المخزون
        $inventory = collect($bs['assets'])->filter(fn($a) => str_starts_with($a['code'], '131'))->sum('net_balance');

        // الخصوم المتداولة
        $currentLiabilities = collect($bs['liabilities'])->filter(fn($a) => str_starts_with($a['code'], '21'))->sum('net_balance');

        return [
            'as_of'    => $asOf,
            'liquidity' => [
                'current_ratio'    => $currentLiabilities > 0 ? round($currentAssets / $currentLiabilities, 2) : null,
                'quick_ratio'      => $currentLiabilities > 0 ? round(($currentAssets - $inventory) / $currentLiabilities, 2) : null,
            ],
            'profitability' => [
                'gross_margin'     => $totalRevenue > 0 ? round((($totalRevenue - $cogs) / $totalRevenue) * 100, 1) : null,
                'net_margin'       => $totalRevenue > 0 ? round(($netIncome / $totalRevenue) * 100, 1) : null,
                'return_on_assets' => $totalAssets > 0 ? round(($netIncome / $totalAssets) * 100, 1) : null,
                'return_on_equity' => $totalEquity > 0 ? round(($netIncome / $totalEquity) * 100, 1) : null,
            ],
            'leverage' => [
                'debt_to_equity'   => $totalEquity > 0 ? round($totalLiabilities / $totalEquity, 2) : null,
                'debt_to_assets'   => $totalAssets > 0 ? round($totalLiabilities / $totalAssets, 2) : null,
            ],
            'activity' => [
                'receivables_turnover' => $receivables > 0 ? round($totalRevenue / $receivables, 1) : null,
                'inventory_turnover'   => $inventory > 0 ? round($cogs / $inventory, 1) : null,
            ],
            'summary' => [
                'total_assets'      => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity'      => $totalEquity,
                'net_income'        => $netIncome,
                'total_revenue'     => $totalRevenue,
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════

    /**
     * تجميع مدين/دائن لجميع حسابات نوع معين (asset, liability, equity, revenue, expense)
     */
    private function sumAccountsByType(string $typeCode, ?string $from, ?string $to): array
    {
        $query = JournalEntryLine::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->where('journal_entries.status', 'posted')
            ->where('account_types.code', $typeCode)
            ->whereNull('accounts.deleted_at');

        if ($from) {
            $query->where('journal_entries.entry_date', '>=', $from);
        }
        if ($to) {
            $query->where('journal_entries.entry_date', '<=', $to);
        }

        $rows = $query->selectRaw('
                accounts.id           AS account_id,
                accounts.code         AS account_code,
                accounts.name_ar      AS account_name,
                account_types.normal_balance,
                SUM(journal_entry_lines.debit)  AS total_debit,
                SUM(journal_entry_lines.credit) AS total_credit
            ')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name_ar', 'account_types.normal_balance')
            ->orderBy('accounts.code')
            ->get();

        return $rows->map(function ($row) {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;
            $net    = $row->normal_balance === 'debit' ? ($debit - $credit) : ($credit - $debit);

            return [
                'account_id'  => $row->account_id,
                'code'        => $row->account_code,
                'name'        => $row->account_name,
                'total_debit' => round($debit, 2),
                'total_credit'=> round($credit, 2),
                'net_balance' => round($net, 2),
            ];
        })->toArray();
    }

    /**
     * بناء تقرير الأعمار (30/60/90/+90 يوم)
     */
    private function buildAging($invoices, string $asOf, string $dateField, string $amountField): array
    {
        $buckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        $rows    = [];

        foreach ($invoices as $inv) {
            $remaining = (float) ($inv->{$amountField} ?? ($inv->total - $inv->paid));
            if ($remaining <= 0) {
                continue;
            }

            $days = now()->diffInDays($inv->{$dateField}, false);
            $age  = abs($days);

            $bucket = match (true) {
                $age <= 30  => '0-30',
                $age <= 60  => '31-60',
                $age <= 90  => '61-90',
                default     => '90+',
            };

            $buckets[$bucket] += $remaining;

            $rows[] = [
                'id'        => $inv->id,
                'party'     => $inv->customer?->name ?? $inv->supplier?->name ?? '-',
                'invoice_no'=> $inv->invoice_number ?? $inv->id,
                'date'      => $inv->{$dateField},
                'days'      => $age,
                'amount'    => $remaining,
                'bucket'    => $bucket,
            ];
        }

        return [
            'as_of'   => $asOf,
            'rows'    => $rows,
            'buckets' => $buckets,
            'total'   => array_sum($buckets),
        ];
    }

    /**
     * دمج بيانات فترتين للمقارنة (current vs previous)
     */
    private function mergeComparative(array $current, array $previous): array
    {
        $merged = [];
        $prevByCode = collect($previous)->keyBy('code');

        foreach ($current as $item) {
            $prev = $prevByCode->get($item['code']);
            $merged[$item['code']] = [
                'code'             => $item['code'],
                'name'             => $item['name'],
                'current_balance'  => $item['net_balance'],
                'previous_balance' => $prev ? $prev['net_balance'] : 0,
                'change'           => round($item['net_balance'] - ($prev['net_balance'] ?? 0), 2),
                'change_pct'       => ($prev && $prev['net_balance'] != 0) ? round((($item['net_balance'] - $prev['net_balance']) / abs($prev['net_balance'])) * 100, 1) : null,
            ];
        }

        // إضافة حسابات موجودة فقط في الفترة السابقة
        foreach ($previous as $item) {
            if (!isset($merged[$item['code']])) {
                $merged[$item['code']] = [
                    'code'             => $item['code'],
                    'name'             => $item['name'],
                    'current_balance'  => 0,
                    'previous_balance' => $item['net_balance'],
                    'change'           => round(-$item['net_balance'], 2),
                    'change_pct'       => -100.0,
                ];
            }
        }

        return array_values($merged);
    }

    /**
     * جلب بداية السنة المالية الحالية
     */
    private function getCurrentYearStart(): string
    {
        $year = \App\Models\FiscalYear::where('start_date', '<=', now()->toDateString())
            ->where('end_date',   '>=', now()->toDateString())
            ->value('start_date');

        return $year ?? now()->startOfYear()->toDateString();
    }
}
