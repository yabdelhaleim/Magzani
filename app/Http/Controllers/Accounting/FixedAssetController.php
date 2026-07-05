<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Models\Account;
use App\Services\Accounting\FixedAssetService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FixedAssetController extends Controller
{
    public function __construct(
        private FixedAssetService $assetService
    ) {}

    /**
     * List all fixed assets and summary cards.
     */
    public function index(): View
    {
        $assets = FixedAsset::with(['assetAccount', 'accumulatedDepreciationAccount'])
            ->orderBy('code')
            ->paginate(15);

        // Stats calculation
        $totalCost = FixedAsset::whereIn('status', ['active', 'fully_depreciated'])->sum('purchase_cost');
        
        $totalAccumulated = 0;
        $activeAssets = FixedAsset::whereIn('status', ['active', 'fully_depreciated'])->get();
        foreach ($activeAssets as $a) {
            $totalAccumulated += $a->accumulated_depreciation;
        }
        
        $netBookValue = $totalCost - $totalAccumulated;
        $fullyDepreciatedCount = FixedAsset::where('status', 'fully_depreciated')->count();

        return view('accounting.fixed-assets.index', compact(
            'assets', 'totalCost', 'totalAccumulated', 'netBookValue', 'fullyDepreciatedCount'
        ));
    }

    /**
     * Show form to register a new fixed asset.
     */
    public function create(): View
    {
        // 1. Fixed Asset Accounts (typically starts with 15)
        $assetAccounts = Account::where('is_active', true)
            ->where('is_leaf', true)
            ->whereHas('accountType', fn($q) => $q->where('code', 'asset'))
            ->where('code', 'like', '15%')
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        // 2. Accumulated Depreciation Accounts (typically 1590 or similar)
        $accumDepAccounts = Account::where('is_active', true)
            ->where('is_leaf', true)
            ->whereHas('accountType', fn($q) => $q->where('code', 'asset'))
            ->where('code', 'like', '159%')
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        // Fallback: if no specific accumDepAccounts, allow any asset account starting with 15
        if ($accumDepAccounts->isEmpty()) {
            $accumDepAccounts = $assetAccounts;
        }

        // 3. Depreciation Expense Accounts (Operating Expenses - code starts with 5)
        $expenseAccounts = Account::where('is_active', true)
            ->where('is_leaf', true)
            ->whereHas('accountType', fn($q) => $q->where('code', 'expense'))
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.fixed-assets.create', compact(
            'assetAccounts', 'accumDepAccounts', 'expenseAccounts'
        ));
    }

    /**
     * Register a new fixed asset.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                                => 'required|string|max:200',
            'code'                                => 'required|string|max:50|unique:fixed_assets,code',
            'purchase_date'                       => 'required|date',
            'purchase_cost'                       => 'required|numeric|min:0.01',
            'scrap_value'                         => 'nullable|numeric|min:0',
            'useful_life'                         => 'required|integer|min:1',
            'asset_account_id'                    => 'required|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'required|exists:accounts,id',
            'depreciation_expense_account_id'     => 'required|exists:accounts,id',
        ]);

        $this->assetService->register($validated);

        return redirect()->route('accounting.fixed-assets.index')
            ->with('success', '✅ تم تسجيل الأصل الثابت بنجاح.');
    }

    /**
     * Show asset details and depreciation history.
     */
    public function show(FixedAsset $fixedAsset): View
    {
        $fixedAsset->load([
            'assetAccount',
            'accumulatedDepreciationAccount',
            'depreciationExpenseAccount',
            'depreciations.journalEntry',
            'disposalEntry',
        ]);

        // Cash/Bank Accounts (starts with 11)
        $cashAccounts = Account::where('is_active', true)
            ->where('is_leaf', true)
            ->whereHas('accountType', fn($q) => $q->where('code', 'asset'))
            ->where('code', 'like', '11%')
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.fixed-assets.show', compact('fixedAsset', 'cashAccounts'));
    }

    /**
     * Form to run depreciation.
     */
    public function depreciateForm(): View
    {
        return view('accounting.fixed-assets.depreciate', [
            'defaultDate' => now()->endOfMonth()->toDateString(),
        ]);
    }

    /**
     * Execute depreciation run.
     */
    public function runDepreciation(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $results = $this->assetService->postDepreciationRun($request->date);
        $count = count($results);

        if ($count > 0) {
            return redirect()->route('accounting.fixed-assets.index')
                ->with('success', "✅ تم احتساب وإهلاك {$count} أصل/أصول بنجاح لشهر " . date('m/Y', strtotime($request->date)));
        }

        return redirect()->route('accounting.fixed-assets.index')
            ->with('success', '💡 لم يتم احتساب أي إهلاك (إما لعدم وجود أصول نشطة أو لإجراء الإهلاك مسبقاً لهذا الشهر).');
    }

    /**
     * Dispose of an asset.
     */
    public function dispose(Request $request, FixedAsset $fixedAsset): RedirectResponse
    {
        $validated = $request->validate([
            'disposal_value' => 'required|numeric|min:0',
            'disposed_at'    => 'required|date',
            'cash_account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $entry = $this->assetService->dispose(
                $fixedAsset,
                (float) $validated['disposal_value'],
                $validated['disposed_at'],
                (int) $validated['cash_account_id']
            );

            return redirect()->route('accounting.fixed-assets.show', $fixedAsset->id)
                ->with('success', "✅ تم استبعاد/بيع الأصل بنجاح وتسجيل قيد اليومية رقم {$entry->entry_number}");
        } catch (\Throwable $e) {
            return back()->with('error', '❌ فشل استبعاد الأصل: ' . $e->getMessage());
        }
    }
}
