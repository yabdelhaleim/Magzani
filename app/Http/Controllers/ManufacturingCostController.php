<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManufacturingCostRequest;
use App\Http\Requests\UpdateManufacturingCostRequest;
use App\Models\ManufacturingCost;
use App\Services\ManufacturingCostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManufacturingCostController extends Controller
{
    public function __construct(
        private ManufacturingCostService $costService
    ) {}

    public function index(Request $request)
    {
        $query = ManufacturingCost::with(['product', 'components', 'creator'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where('product_name', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $costs = $query->paginate(20)->withQueryString();

        return view('manufacturing.index', compact('costs'));
    }

    public function create()
    {
        return view('manufacturing.create');
    }

    public function store(StoreManufacturingCostRequest $request)
    {
        try {
            $cost = $this->costService->createManufacturingCost($request->validated());

            return redirect()
                ->route('manufacturing.show', $cost)
                ->with('success', 'تم حفظ حساب التكلفة بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing cost creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }

    public function show(ManufacturingCost $manufacturingCost)
    {
        $manufacturingCost->load(['components', 'product', 'creator', 'updater']);

        return view('manufacturing.show', compact('manufacturingCost'));
    }

    public function edit(ManufacturingCost $manufacturingCost)
    {
        $manufacturingCost->load('components');

        return view('manufacturing.edit', compact('manufacturingCost'));
    }

    public function update(UpdateManufacturingCostRequest $request, ManufacturingCost $manufacturingCost)
    {
        try {
            $cost = $this->costService->updateManufacturingCost($manufacturingCost, $request->validated());

            return redirect()
                ->route('manufacturing.show', $cost)
                ->with('success', 'تم تحديث حساب التكلفة بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing cost update failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    public function destroy(ManufacturingCost $manufacturingCost)
    {
        try {
            $this->costService->deleteManufacturingCost($manufacturingCost);

            return redirect()
                ->route('manufacturing.index')
                ->with('success', 'تم حذف حساب التكلفة بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing cost deletion failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    public function calculateAjax(Request $request)
    {
        $request->validate([
            'components' => 'required|array|min:1',
            'components.*.component_name' => 'required|string',
            'components.*.quantity' => 'required|numeric|min:0.01',
            'components.*.length_cm' => 'required|numeric|min:0.01',
            'components.*.width_cm' => 'required|numeric|min:0.01',
            'components.*.thickness_cm' => 'required|numeric|min:0.01',
            'price_per_cubic_meter' => 'required|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'nails_hardware_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'tips_misc_cost' => 'nullable|numeric|min:0',
            'fumigation_cost' => 'nullable|numeric|min:0',
            'profit_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $result = $this->costService->calculateCosts($request->all());

        return response()->json($result);
    }

    public function confirm(ManufacturingCost $manufacturingCost)
    {
        if ($manufacturingCost->status !== 'draft') {
            return back()->with('error', 'تم تأكيد هذا الحساب بالفعل');
        }

        try {
            $this->costService->confirmCost($manufacturingCost);

            return redirect()
                ->route('manufacturing.show', $manufacturingCost)
                ->with('success', 'تم تأكيد حساب التكلفة بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing cost confirmation failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء التأكيد: ' . $e->getMessage());
        }
    }

    /* ============================================================
     * GAP 2 — Standard Costing on the BOM
     * ============================================================ */

    /**
     * Update the standard cost on the BOM header. Independent of the
     * actual-cost revision flow — accountants set the expected per-unit
     * cost they expect production to run at. Setting zero in any of the
     * three sub-fields disables standard costing for this BOM.
     */
    public function updateStandardCost(Request $request, ManufacturingCost $manufacturingCost)
    {
        $validated = $request->validate([
            'standard_material_cost'      => 'required|numeric|min:0',
            'standard_labor_cost'         => 'required|numeric|min:0',
            'standard_overhead_cost'      => 'required|numeric|min:0',
            'standard_cost_effective_from'=> 'nullable|date',
        ]);

        $standardTotal = round(
            (float) $validated['standard_material_cost']
            + (float) $validated['standard_labor_cost']
            + (float) $validated['standard_overhead_cost'],
            4
        );

        $manufacturingCost->update([
            'standard_material_cost'        => $validated['standard_material_cost'],
            'standard_labor_cost'           => $validated['standard_labor_cost'],
            'standard_overhead_cost'        => $validated['standard_overhead_cost'],
            'standard_cost'                 => $standardTotal,
            'standard_cost_effective_from'  => $validated['standard_cost_effective_from'] ?? now()->toDateString(),
            'standard_cost_updated_by'      => Auth::id(),
            'standard_cost_updated_at'      => now(),
        ]);

        Log::info('[StandardCosting] BOM standard cost updated', [
            'manufacturing_cost_id' => $manufacturingCost->id,
            'product_id'            => $manufacturingCost->product_id,
            'standard_total'        => $standardTotal,
            'updated_by'            => Auth::id(),
        ]);

        return redirect()
            ->route('manufacturing.show', $manufacturingCost)
            ->with('success', "✅ تم حفظ التكلفة المعيارية (الإجمالي: {$standardTotal}).");
    }
}
