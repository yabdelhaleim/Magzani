<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreAccountRequest;
use App\Http\Requests\Accounting\UpdateAccountRequest;
use App\Models\Account;
use App\Models\AccountType;
use App\Services\Accounting\ChartOfAccountsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChartOfAccountsController extends Controller
{
    public function __construct(
        private ChartOfAccountsService $coaService
    ) {}

    /**
     * عرض شجرة الحسابات الكاملة
     * إصلاح #4: استدعاء getTree() بدلاً من buildTree() (غير موجودة)
     */
    public function index(Request $request): View
    {
        $this->authorize('accounting.coa.read');
        $accountTypes = AccountType::with('accounts')->orderBy('code')->get();
        $tree         = $this->coaService->getTree(); // ✅ الاسم الصحيح
        $stats        = [
            'total_accounts'  => Account::count(),
            'active_accounts' => Account::where('is_active', true)->count(),
            'parent_accounts' => Account::whereNull('parent_id')->count(),
        ];

        return view('accounting.coa.index', compact('accountTypes', 'tree', 'stats'));
    }

    /**
     * نموذج إنشاء حساب جديد
     */
    public function create(): View
    {
        $this->authorize('accounting.coa.create');
        $accountTypes = AccountType::orderBy('code')->get();
        $parents      = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.coa.create', compact('accountTypes', 'parents'));
    }

    /**
     * تخزين حساب جديد
     * إصلاح #14: استخدام name_ar بدلاً من name
     * إصلاح #16: تمرير البيانات بأسماء الحقول الصحيحة للـ Service
     */
    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->authorize('accounting.coa.create');
        $account = $this->coaService->create($request->validated());

        return redirect()
            ->route('accounting.coa.index')
            ->with('success', "✅ تم إنشاء الحساب [{$account->code}] {$account->name_ar} بنجاح.");
    }

    /**
     * عرض تفاصيل حساب مع حركاته
     * إصلاح #15: استخدام lines() بدلاً من journalEntryLines() (غير موجودة في Model)
     */
    public function show(Account $account, Request $request): View
    {
        $this->authorize('accounting.coa.read');
        $account->load(['accountType', 'parent', 'children']);

        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $lines = $account->lines() // ✅ الاسم الصحيح للـ relation
            ->with(['journalEntry'])
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from, $to]))
            ->orderByDesc('id')
            ->paginate(25);

        $balance = $account->balance?->balance ?? 0;

        return view('accounting.coa.show', compact('account', 'lines', 'balance', 'from', 'to'));
    }

    /**
     * نموذج تعديل حساب
     */
    public function edit(Account $account): View
    {
        $this->authorize('accounting.coa.update');
        $accountTypes = AccountType::orderBy('code')->get();
        $parents      = Account::where('is_active', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.coa.edit', compact('account', 'accountTypes', 'parents'));
    }

    /**
     * تحديث بيانات الحساب
     * إصلاح #14: استخدام name_ar
     */
    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('accounting.coa.update');
        $this->coaService->update($account, $request->validated());

        return redirect()
            ->route('accounting.coa.index')
            ->with('success', "✅ تم تحديث الحساب [{$account->code}] {$account->name_ar}.");
    }

    /**
     * حذف حساب (فقط إذا لم تكن له حركات)
     * إصلاح #14+#15: name_ar + lines()
     */
    public function destroy(Account $account): RedirectResponse
    {
        if ($account->lines()->exists()) { // ✅ الاسم الصحيح
            return back()->with('error', '❌ لا يمكن حذف حساب له حركات في دفتر الأستاذ.');
        }

        if ($account->children()->exists()) {
            return back()->with('error', '❌ لا يمكن حذف حساب له حسابات فرعية.');
        }

        $code = $account->code;
        $name = $account->name_ar;
        $this->coaService->delete($account);

        return redirect()
            ->route('accounting.coa.index')
            ->with('success', "✅ تم حذف الحساب [{$code}] {$name}.");
    }

    /**
     * تصدير دليل الحسابات بصيغة JSON
     */
    public function export(): \Illuminate\Http\JsonResponse
    {
        $accounts = Account::with(['accountType', 'parent'])
            ->orderBy('code')
            ->get()
            ->map(fn ($a) => [
                'code'        => $a->code,
                'name_ar'     => $a->name_ar,   // ✅ name_ar
                'name_en'     => $a->name_en,
                'type'        => $a->accountType?->code,
                'parent_code' => $a->parent?->code,
                'is_active'   => $a->is_active,
                'normal_balance' => $a->accountType?->normal_balance,
            ]);

        return response()->json(['accounts' => $accounts]);
    }
}
