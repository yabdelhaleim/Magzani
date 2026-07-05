<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreVoucherRequest;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\AccountBalanceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * ReceiptVoucherController — سند قبض (استلام من عميل أو إيداع)
 */
class ReceiptVoucherController extends Controller
{
    public function __construct(
        private JournalEntryService $journalEntryService,
        private AccountBalanceService $balanceService
    ) {}

    /**
     * قائمة سندات القبض
     */
    public function index(Request $request): View
    {
        $vouchers = JournalEntry::where('source_type', 'receipt_voucher')
            ->with(['lines.account'])
            ->orderByDesc('entry_date')
            ->paginate(20);

        return view('accounting.vouchers.receipt.index', compact('vouchers'));
    }

    /**
     * نموذج إنشاء سند قبض
     */
    public function create(): View
    {
        // حسابات الصندوق والبنوك (مدين)
        $cashAccounts = Account::where('is_active', true)
            ->whereHas('accountType', fn ($q) => $q->where('code', '1100'))
            ->orWhereHas('accountType', fn ($q) => $q->where('code', '1120'))
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        // حسابات الذمم المدينة والإيرادات (دائن)
        $creditAccounts = Account::where('is_active', true)
            ->whereHas('accountType', fn ($q) => $q->whereIn('code', ['1200', '4000']))
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.vouchers.receipt.create', compact('cashAccounts', 'creditAccounts'));
    }

    /**
     * تخزين سند قبض
     */
    public function store(StoreVoucherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $entry = $this->journalEntryService->createDraft([
            'entry_date'       => $data['entry_date'],
            'description'      => $data['description'] ?? 'سند قبض',
            'source_type'      => 'receipt_voucher',
            'source_id'        => null,
            'source_event_key' => 'receipt_' . uniqid(),
            'reference'        => $data['reference'] ?? null,
            'lines'            => [
                [
                    'account_id'  => (int) $data['cash_account_id'],
                    'debit'       => (float) $data['amount'],
                    'credit'      => 0,
                    'description' => $data['description'] ?? null,
                ],
                [
                    'account_id'  => (int) $data['credit_account_id'],
                    'debit'       => 0,
                    'credit'      => (float) $data['amount'],
                    'description' => $data['description'] ?? null,
                ],
            ],
        ]);

        $this->journalEntryService->post($entry);

        return redirect()
            ->route('accounting.vouchers.receipt.index')
            ->with('success', "✅ تم إنشاء سند القبض [{$entry->entry_number}] بنجاح.");
    }

    /**
     * عرض سند قبض
     */
    public function show(JournalEntry $journalEntry): View
    {
        abort_unless($journalEntry->source_type === 'receipt_voucher', 404);
        $journalEntry->load(['lines.account']);

        return view('accounting.vouchers.receipt.show', compact('journalEntry'));
    }

    /**
     * طباعة سند القبض
     */
    public function print(JournalEntry $journalEntry): View
    {
        abort_unless($journalEntry->source_type === 'receipt_voucher', 404);
        $journalEntry->load(['lines.account']);

        return view('accounting.vouchers.receipt.print', compact('journalEntry'));
    }
}
