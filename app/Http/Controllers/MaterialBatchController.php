<?php

namespace App\Http\Controllers;

use App\Models\MaterialBatch;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use App\Services\MaterialStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaterialBatchController extends Controller
{
    public function __construct(
        private MaterialStockService $stockService
    ) {}

    public function index(Request $request)
    {
        $batches = MaterialBatch::with(['product', 'warehouse', 'supplier', 'uom'])
            ->latest()
            ->paginate(20);

        return view('manufacturing.material-batches.index', compact('batches'));
    }

    public function create()
    {
        $products = Product::where('product_type', 'raw_material')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $units = UnitOfMeasure::where('is_active', true)->get();

        return view('manufacturing.material-batches.create', compact('products', 'warehouses', 'suppliers', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'uom_id' => 'required|exists:units_of_measure,id',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'required|numeric|min:0',
            'purchase_reference' => 'nullable|string|max:255',
            'received_at' => 'required|date',
        ]);

        try {
            $this->stockService->createStock($validated);
            return redirect()->route('material-batches.index')->with('success', 'تم تسجيل دفعة المواد الخام بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating material batch: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
