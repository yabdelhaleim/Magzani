<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\RecurringJournalEntry;
use App\Services\Accounting\RecurringJournalEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringJournalEntryController extends Controller
{
    public function __construct(
        private RecurringJournalEntryService $service
    ) {}

    public function index(): View
    {
        $templates = RecurringJournalEntry::with('lines.account', 'creator')
            ->orderByDesc('id')
            ->paginate(20);

        return view('accounting.recurring.index', compact('templates'));
    }

    public function create(): View
    {
        $accounts = Account::where('is_active', true)->where('is_leaf', true)
            ->orderBy('code')->get(['id', 'code', 'name_ar']);

        return view('accounting.recurring.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        $this->service->create($validated);

        return redirect()->route('accounting.recurring.index')
            ->with('success', '✅ تم إنشاء قالب القيد المتكرر بنجاح.');
    }

    public function edit(RecurringJournalEntry $recurring): View
    {
        $recurring->load('lines.account');
        $accounts = Account::where('is_active', true)->where('is_leaf', true)
            ->orderBy('code')->get(['id', 'code', 'name_ar']);

        return view('accounting.recurring.edit', compact('recurring', 'accounts'));
    }

    public function update(Request $request, RecurringJournalEntry $recurring): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        $this->service->update($recurring, $validated);

        return redirect()->route('accounting.recurring.index')
            ->with('success', '✅ تم تحديث القالب بنجاح.');
    }

    public function destroy(RecurringJournalEntry $recurring): RedirectResponse
    {
        $recurring->delete();

        return back()->with('success', '✅ تم حذف القالب.');
    }

    public function runNow(RecurringJournalEntry $recurring): RedirectResponse
    {
        try {
            $entry = $this->service->processTemplate($recurring);
            return back()->with('success', "✅ تم توليد القيد #{$entry?->entry_number} بنجاح.");
        } catch (\Throwable $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'template_name'        => 'required|string|max:200',
            'description'          => 'required|string|max:500',
            'frequency'            => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'next_run_date'        => 'required|date',
            'end_date'             => 'nullable|date|after_or_equal:next_run_date',
            'is_active'            => 'boolean',
            'auto_post'            => 'boolean',
            'lines'                => 'required|array|min:2',
            'lines.*.account_id'   => 'required|exists:accounts,id',
            'lines.*.debit'        => 'nullable|numeric|min:0',
            'lines.*.credit'       => 'nullable|numeric|min:0',
            'lines.*.description'  => 'nullable|string|max:500',
        ], [], [
            'template_name' => 'اسم القالب',
            'description'   => 'البيان',
            'frequency'     => 'التكرار',
            'next_run_date' => 'تاريخ التشغيل القادم',
        ]);
    }
}
