<?php

namespace App\Http\Controllers;

use App\Models\MaterialBatch;
use App\Models\MaterialDispensing;
use App\Models\ManufacturingOrder;
use App\Services\MaterialStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaterialDispensingController extends Controller
{
    public function __construct(
        private MaterialStockService $stockService
    ) {}

    public function index(Request $request)
    {
        $dispensings = MaterialDispensing::with(['batch.product', 'batch.uom', 'manufacturingOrder'])
            ->latest()
            ->paginate(20);

        return view('manufacturing.material-dispensings.index', compact('dispensings'));
    }

    public function create(MaterialBatch $batch)
    {
        $orders = ManufacturingOrder::where('status', 'draft')->get();
        return view('manufacturing.material-dispensings.create', compact('batch', 'orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_batch_id' => 'required|exists:material_batches,id',
            'manufacturing_order_id' => 'nullable|exists:manufacturing_orders,id',
            'quantity_taken' => 'required|numeric|min:0.0001',
            'dispensed_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $batch = MaterialBatch::findOrFail($validated['material_batch_id']);
            $this->stockService->dispense($batch, $validated);
            return redirect()->route('material-dispensings.index')->with('success', 'تم صرف المواد الخام بنجاح');
        } catch (\Exception $e) {
            Log::error('Error dispensing material batch: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
