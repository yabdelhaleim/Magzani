<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    /**
     * قائمة قيود اليومية مع فلاتر
     */
    public function index(Request $request): View
    {
        $query = JournalEntry::with(['lines.account'])
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('entry_number', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        $stats = [
            'total'   => JournalEntry::count(),
            'posted'  => JournalEntry::where('status', 'posted')->count(),
            'draft'   => JournalEntry::where('status', 'draft')->count(),
        ];

        return view('accounting.journal.index', compact('entries', 'stats'));
    }

    /**
     * نموذج إنشاء قيد يدوي
     */
    public function create(): View
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.journal.create', compact('accounts'));
    }

    /**
     * تخزين قيد يومية يدوي
     */
    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $lines = collect($data['lines'])->map(fn ($l) => [
            'account_id'     => (int) $l['account_id'],
            'debit'          => (float) ($l['debit']  ?? 0),
            'credit'         => (float) ($l['credit'] ?? 0),
            'description'    => $l['description'] ?? null,
            'cost_center_id' => $l['cost_center_id'] ?? null,
        ])->toArray();

        $entry = $this->journalEntryService->createDraft([
            'entry_date'       => $data['entry_date'],
            'description'      => $data['description'],
            'source_type'      => 'manual',
            'source_id'        => null,
            'source_event_key' => 'manual_' . uniqid(),
            'reference'        => $data['reference'] ?? null,
            'lines'            => $lines,
        ]);

        if ($request->boolean('post_immediately')) {
            $this->journalEntryService->post($entry);
            return redirect()
                ->route('accounting.journal.show', $entry)
                ->with('success', "✅ تم إنشاء القيد [{$entry->entry_number}] واعتماده بنجاح.");
        }

        return redirect()
            ->route('accounting.journal.show', $entry)
            ->with('success', "✅ تم حفظ القيد [{$entry->entry_number}] كمسودة.");
    }

    /**
     * عرض تفاصيل قيد واحد
     */
    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account.accountType']);

        $totalDebit  = $journalEntry->lines->sum('debit');
        $totalCredit = $journalEntry->lines->sum('credit');
        $isBalanced  = abs($totalDebit - $totalCredit) < 0.01;

        return view('accounting.journal.show', compact(
            'journalEntry',
            'totalDebit',
            'totalCredit',
            'isBalanced'
        ));
    }

    /**
     * اعتماد قيد مسودة
     * إصلاح #9: مقارنة Enum بـ Enum لا بـ String
     */
    public function post(JournalEntry $journalEntry): RedirectResponse
    {
        // ✅ مقارنة Enum بـ Enum (لأن status مُعرَّف كـ cast لـ JournalEntryStatus)
        if ($journalEntry->status !== JournalEntryStatus::DRAFT) {
            return back()->with('error', '❌ لا يمكن اعتماد قيد غير في حالة مسودة.');
        }

        $this->journalEntryService->post($journalEntry);

        return redirect()
            ->route('accounting.journal.show', $journalEntry)
            ->with('success', "✅ تم اعتماد القيد [{$journalEntry->entry_number}] بنجاح.");
    }

    /**
     * عكس قيد معتمد
     * إصلاح #9: مقارنة Enum بـ Enum
     */
    public function reverse(JournalEntry $journalEntry): RedirectResponse
    {
        // ✅ مقارنة Enum بـ Enum
        if ($journalEntry->status !== JournalEntryStatus::POSTED) {
            return back()->with('error', '❌ لا يمكن عكس قيد غير معتمد.');
        }

        if ($journalEntry->reversed_entry_id) {
            return back()->with('error', '❌ هذا القيد تم عكسه مسبقاً.');
        }

        $reversed = $this->journalEntryService->reverse($journalEntry, 'عكس يدوي');

        return redirect()
            ->route('accounting.journal.show', $reversed)
            ->with('success', "✅ تم إنشاء قيد العكس [{$reversed->entry_number}].");
    }

    /**
     * طباعة قيد اليومية
     */
    public function print(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account.accountType']);

        return view('accounting.journal.print', compact('journalEntry'));
    }
}
