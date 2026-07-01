<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialTemplate;
use App\Models\Warehouse;
use App\Services\RawMaterialTemplateInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RawMaterialTemplateController extends Controller
{
    public function __construct(
        private RawMaterialTemplateInventoryService $rawMaterialInventory
    ) {}

    public function index()
    {
        $templates = RawMaterialTemplate::with('warehouse:id,name,code')
            ->latest()
            ->paginate(20);

        return view('manufacturing-orders.raw-materials.index', compact('templates'));
    }

    public function create()
    {
        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('manufacturing-orders.raw-materials.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'buy_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $template = RawMaterialTemplate::create([
                'name' => $validated['name'],
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => $validated['quantity'],
                'sale_price' => $validated['sale_price'],
                'buy_price' => $validated['buy_price'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $this->rawMaterialInventory->sync($template->fresh());
        });

        return redirect()->route('manufacturing-orders.raw-materials.index')
            ->with('success', 'تم إنشاء الخامة بنجاح وربطها بالمخزن');
    }

    public function show(string $id)
    {
        $template = RawMaterialTemplate::with('warehouse:id,name,code')->findOrFail($id);

        return view('manufacturing-orders.raw-materials.show', compact('template'));
    }

    public function edit(string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);
        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('manufacturing-orders.raw-materials.edit', compact('template', 'warehouses'));
    }

    public function update(Request $request, string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'buy_price' => 'required|numeric|min:0',
        ]);

        $previousWarehouseId = (int) $template->warehouse_id;

        DB::transaction(function () use ($template, $validated, $previousWarehouseId) {
            $template->update([
                'name' => $validated['name'],
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => $validated['quantity'],
                'sale_price' => $validated['sale_price'],
                'buy_price' => $validated['buy_price'],
                'updated_by' => Auth::id(),
            ]);
            $newWarehouseId = (int) $validated['warehouse_id'];
            $this->rawMaterialInventory->sync(
                $template->fresh(),
                $previousWarehouseId !== $newWarehouseId ? $previousWarehouseId : null
            );
        });

        return redirect()->route('manufacturing-orders.raw-materials.index')
            ->with('success', 'تم تحديث الخامة بنجاح وتحديث المخزن');
    }

    public function destroy(string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);
        if ($template->warehouse_id) {
            $this->rawMaterialInventory->forgetWarehouseCache((int) $template->warehouse_id);
        }
        $template->delete();

        return redirect()->route('manufacturing-orders.raw-materials.index')
            ->with('success', 'تم حذف الخامة بنجاح');
    }
}
