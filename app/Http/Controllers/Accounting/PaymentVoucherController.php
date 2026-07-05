<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreVoucherRequest;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * PaymentVoucherController — سند صرف (دفع لمورد أو مصروف)
 */
class PaymentVoucherController extends Controller
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    /**
     * قائمة سندات الصرف
     */
    public function index(Request $request): View
    {
        $vouchers = JournalEntry::where('source_type', 'payment_voucher')
            ->with(['lines.account'])
            ->orderByDesc('entry_date')
            ->paginate(20);

        return view('accounting.vouchers.payment.index', compact('vouchers'));
    }

    /**
     * نموذج إنشاء سند صرف
     */
    public function create(): View
    {
        // حسابات الصندوق والبنوك (دائن)
        $cashAccounts = Account::where('is_active', true)
            ->whereHas('accountType', fn ($q) => $q->whereIn('code', ['1100', '1120']))
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        // حسابات الذمم الدائنة والمصروفات (مدين)
        $debitAccounts = Account::where('is_active', true)
            ->whereHas('accountType', fn ($q) => $q->whereIn('code', ['2100', '5000', '6000']))
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.vouchers.payment.create', compact('cashAccounts', 'debitAccounts'));
    }

    /**
     * تخزين سند صرف
     */
    public function store(StoreVoucherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $entry = $this->journalEntryService->createDraft([
            'entry_date'       => $data['entry_date'],
            'description'      => $data['description'] ?? 'سند صرف',
            'source_type'      => 'payment_voucher',
            'source_id'        => null,
            'source_event_key' => 'payment_v_' . uniqid(),
            'reference'        => $data['reference'] ?? null,
            'lines'            => [
                [
                    'account_id'  => (int) $data['debit_account_id'],
                    'debit'       => (float) $data['amount'],
                    'credit'      => 0,
                    'description' => $data['description'] ?? null,
                ],
                [
                    'account_id'  => (int) $data['cash_account_id'],
                    'debit'       => 0,
                    'credit'      => (float) $data['amount'],
                    'description' => $data['description'] ?? null,
                ],
            ],
        ]);

        $this->journalEntryService->post($entry);

        return redirect()
            ->route('accounting.vouchers.payment.index')
            ->with('success', "✅ تم إنشاء سند الصرف [{$entry->entry_number}] بنجاح.");
    }

    /**
     * عرض سند صرف
     */
    public function show(JournalEntry $journalEntry): View
    {
        abort_unless($journalEntry->source_type === 'payment_voucher', 404);
        $journalEntry->load(['lines.account']);

        return view('accounting.vouchers.payment.show', compact('journalEntry'));
    }

    /**
     * طباعة سند الصرف
     */
    public function print(JournalEntry $journalEntry): View
    {
        abort_unless($journalEntry->source_type === 'payment_voucher', 404);
        $journalEntry->load(['lines.account']);

        return view('accounting.vouchers.payment.print', compact('journalEntry'));
    }
}
