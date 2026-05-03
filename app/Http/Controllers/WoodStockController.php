<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWoodStockRequest;
use App\Models\WoodStock;
use App\Models\Supplier;
use App\Services\WoodStockService;
use Illuminate\Http\Request;

class WoodStockController extends Controller
{
    public function __construct(
        private WoodStockService $woodStockService
    ) {}

    public function index()
    {
        $woodStocks = WoodStock::with('supplier')->latest()->paginate(20);
        $summary = $this->woodStockService->getStockSummary();

        return view('manufacturing.wood-stocks.index', compact('woodStocks', 'summary'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        return view('manufacturing.wood-stocks.create', compact('suppliers', 'warehouses'));
    }

    public function store(StoreWoodStockRequest $request)
    {
        try {
            $this->woodStockService->createStock($request->validated());
            return redirect()
                ->route('manufacturing.wood-stocks.index')
                ->with('success', 'تم إضافة الدفعة بنجاح');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'خطأ أثناء الإضافة: ' . $e->getMessage());
        }
    }
}
