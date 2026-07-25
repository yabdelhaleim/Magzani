<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Services\InventoryMovementService;
use App\Exports\InventoryMovementExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryMovementController extends Controller
{
    public function __construct(
        private InventoryMovementService $movementService
    ) {}

    /**
     * عرض جميع حركات المخزون
     */
    public function index(Request $request)
    {
        $query = \App\Models\InventoryMovement::with(['warehouse', 'product', 'creator'])
            ->orderByDesc('movement_date');

        // الفلاتر
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        $movements = $query->paginate(50);
        $warehouses = Warehouse::active()->get();
        $products = Product::where('is_active', true)->get();

        return view('warehouses.movements', compact('movements', 'warehouses', 'products'));
    }

    /**
     * حركات منتج معين
     */
    public function productMovements($productId, Request $request)
    {
        try {
            $product = Product::findOrFail($productId);
            $warehouseId = $request->get('warehouse_id');
            
            $movements = $this->movementService->getProductMovements($productId, $warehouseId);

            return view('warehouses.movements', compact('product', 'warehouses', 'movements'));

        } catch (\Exception $e) {
            return redirect()
                ->route('products.index')
                ->with('error', 'المنتج غير موجود');
        }
    }
    /**
 * حركات مخزن معين
 */
public function warehouseMovements($warehouseId, Request $request)
{
    try {
        $warehouse = Warehouse::findOrFail($warehouseId);
        
        $query = \App\Models\InventoryMovement::with(['product', 'creator'])
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('movement_date');

        // الفلاتر
        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        $movements = $query->paginate(50);
        $products = Product::where('is_active', true)->get();

        return view('warehouses.movements', compact('warehouse', 'movements', 'products'));

    } catch (\Exception $e) {
        return redirect()
            ->route('warehouses.index')
            ->with('error', 'المخزن غير موجود');
    }
}

    /**
     * تصدير حركات المخزون إلى Excel.
     *
     * GET /movements/export?...filters
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only([
            'warehouse_id', 'product_id', 'movement_type', 'date_from', 'date_to',
        ]);

        $filename = 'inventory_movements_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new InventoryMovementExport($filters), $filename);
    }
}