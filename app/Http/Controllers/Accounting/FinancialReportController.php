<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\FinancialReportService;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Services\Accounting\PartnerLedgerService;
use App\Services\Accounting\AccountingAuditService;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;

class FinancialReportController extends Controller
{
    public function __construct(
        private FinancialReportService $reportService,
        private \App\Services\Accounting\VatSettlementService $vatService,
        private PartnerLedgerService $partnerLedgerService,
        private AccountingAuditService $auditService
    ) {}

    /**
     * ميزان المراجعة
     *
     * إصلاح #13: trialBalance() تتوقع ?string $asOf (تاريخ) وليس period_id
     * نُحوّل period_id إلى تاريخ نهاية الفترة
     */
    public function trialBalance(Request $request): View
    {
        $fiscalYears = FiscalYear::orderByDesc('start_date')->get();
        $periods     = FiscalPeriod::with('fiscalYear')->orderByDesc('start_date')->get();
        $periodId    = $request->get('period_id');
        $period      = $periodId ? FiscalPeriod::find($periodId) : null;

        // ✅ نحوّل period_id إلى تاريخ نهاية الفترة (أو نستخدم as_of مباشرة)
        $asOf = $request->get('as_of');
        if (!$asOf && $period) {
            $asOf = $period->end_date->toDateString();
        }

        $data = $this->reportService->trialBalance($asOf); // ✅ string|null

        return view('accounting.reports.trial-balance', compact('data', 'periods', 'period', 'fiscalYears', 'asOf'));
    }

    /**
     * قائمة الدخل (Income Statement)
     */
    public function incomeStatement(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $data = $this->reportService->incomeStatement($from, $to);

        return view('accounting.reports.income-statement', compact('data', 'from', 'to'));
    }

    /**
     * الميزانية العمومية (Balance Sheet)
     */
    public function balanceSheet(Request $request): View
    {
        $asOf = $request->get('as_of', now()->toDateString());

        $data = $this->reportService->balanceSheet($asOf);

        return view('accounting.reports.balance-sheet', compact('data', 'asOf'));
    }

    /**
     * دفتر الأستاذ العام (General Ledger)
     */
    public function generalLedger(Request $request): View
    {
        $from      = $request->get('from', now()->startOfMonth()->toDateString());
        $to        = $request->get('to',   now()->toDateString());
        $accountId = $request->get('account_id');

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']); // ✅ name_ar

        $ledger  = $accountId
            ? $this->reportService->generalLedger((int) $accountId, $from, $to)
            : null;

        $account = $accountId ? Account::findOrFail($accountId) : null;

        return view('accounting.reports.general-ledger', compact(
            'accounts', 'ledger', 'account', 'from', 'to'
        ));
    }

    /**
     * تقرير تقادم الديون (Aging Report)
     */
    public function agingReport(Request $request): View
    {
        $type = $request->get('type', 'receivable');
        $asOf = $request->get('as_of', now()->toDateString());

        $data = $this->reportService->agingReport($type, $asOf);

        return view('accounting.reports.aging', compact('data', 'type', 'asOf'));
    }

    /**
     * النسب المالية (Financial Ratios)
     */
    public function financialRatios(Request $request): View
    {
        $asOf = $request->get('as_of', now()->toDateString());

        $ratios = $this->reportService->financialRatios($asOf);

        return view('accounting.reports.financial-ratios', compact('ratios', 'asOf'));
    }

    /**
     * قائمة دخل مقارنة
     */
    public function comparativeIncome(Request $request): View
    {
        $currentFrom  = $request->get('current_from', now()->startOfMonth()->toDateString());
        $currentTo    = $request->get('current_to', now()->toDateString());
        $previousFrom = $request->get('previous_from', now()->subMonth()->startOfMonth()->toDateString());
        $previousTo   = $request->get('previous_to', now()->subMonth()->endOfMonth()->toDateString());

        $data = $this->reportService->comparativeIncomeStatement(
            $currentFrom, $currentTo, $previousFrom, $previousTo
        );

        return view('accounting.reports.comparative-income', compact('data', 'currentFrom', 'currentTo', 'previousFrom', 'previousTo'));
    }

    /**
     * تسوية ضريبة القيمة المضافة
     */
    public function vatSettlement(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $from = $request->get('from', now()->startOfQuarter()->toDateString());
        $to   = $request->get('to', now()->endOfQuarter()->toDateString());

        $preview = null;
        try {
            $preview = $this->vatService->calculate($from, $to);
        } catch (\Throwable $e) {
            $preview = ['error' => $e->getMessage()];
        }

        if ($request->isMethod('post') && $request->boolean('settle')) {
            try {
                $entry = $this->vatService->settle($from, $to);
                return redirect()->route('accounting.reports.vat-settlement', compact('from', 'to'))
                    ->with('success', "✅ تم إنشاء قيد التسوية #{$entry->entry_number}");
            } catch (\Throwable $e) {
                return back()->with('error', '❌ ' . $e->getMessage());
            }
        }

        return view('accounting.reports.vat-settlement', compact('preview', 'from', 'to'));
    }

    /**
     * كشف حساب شريك تفصيلي (عميل أو مورد)
     */
    public function partnerLedger(Request $request): View
    {
        $partnerType = $request->get('partner_type', 'customer');
        $partnerId   = $request->get('partner_id');
        $from        = $request->get('from', now()->startOfMonth()->toDateString());
        $to          = $request->get('to',   now()->toDateString());

        // Fetch active customers or suppliers
        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        $ledger = null;
        if ($partnerId) {
            $ledger = $this->partnerLedgerService->getLedger($partnerType, (int) $partnerId, $from, $to);
        }

        return view('accounting.reports.partner-ledger', compact(
            'partnerType', 'partnerId', 'from', 'to', 'customers', 'suppliers', 'ledger'
        ));
    }

    /**
     * سجل التدقيق والرقابة المحاسبية (Audit Trail)
     */
    public function auditTrail(Request $request): View
    {
        $filters = [
            'from'    => $request->get('from', now()->startOfMonth()->toDateString()),
            'to'      => $request->get('to',   now()->toDateString()),
            'action'  => $request->get('action'),
            'user_id' => $request->get('user_id'),
        ];

        $users = User::orderBy('name')->get(['id', 'name']);
        $logs  = $this->auditService->getLogs($filters);

        return view('accounting.reports.audit-trail', compact('logs', 'users', 'filters'));
    }
}
