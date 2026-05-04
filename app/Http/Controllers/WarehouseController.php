<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Services\WarehouseService;
use App\Services\InventoryMovementService;
use App\Http\Requests\StoreWarehouseRequest;  // ✅ للـ Create
use App\Http\Requests\UpdateWarehouseRequest; // ✅ للـ Update
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    public function __construct(
        private WarehouseService $warehouseService,
        private InventoryMovementService $movementService
    ) {}

    /**
     * ✅ قائمة المخازن
     */
    public function index()
    {
        $warehouses = Warehouse::forDashboard()->paginate(20);

        $totalWarehouses = Warehouse::count();
        $activeWarehouses = Warehouse::where('is_active', true)->count();
        $totalProducts = Product::count();
        $totalValue = ProductWarehouse::selectRaw('COALESCE(SUM(quantity * average_cost), 0) as total')->value('total') ?? 0;

        return view('warehouses.index', compact('warehouses', 'totalWarehouses', 'activeWarehouses', 'totalProducts', 'totalValue'));
    }

    /**
     * ✅ صفحة إنشاء مخزن
     */
    public function create()
    {
        return view('warehouses.create');
    }

    /**
     * ✅ حفظ مخزن جديد - استخدم StoreWarehouseRequest
     */
    public function store(StoreWarehouseRequest $request)
    {
        try {
            Log::info('📝 محاولة إنشاء مخزن', $request->validated());

            $warehouse = $this->warehouseService->create(array_merge(
                $request->validated(),
                ['created_by' => auth()->id()]
            ));

            Log::info('✅ تم إنشاء المخزن', ['id' => $warehouse->id]);

            return redirect()
                ->route('warehouses.index')
                ->with('success', '✅ تم إنشاء المخزن بنجاح: ' . $warehouse->name);

        } catch (\Exception $e) {
            Log::error('❌ خطأ في إنشاء المخزن', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ تفاصيل المخزن
     */
    public function show($id)
    {
        try {
            $data = $this->warehouseService->getWarehouseDetails($id);
            $data['company'] = \App\Models\Company::first();
            return view('warehouses.show', $data);

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', 'المخزن غير موجود');
        }
    }

    /**
     * ✅ صفحة التعديل
     */
    public function edit($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            return view('warehouses.edit', compact('warehouse'));

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', 'المخزن غير موجود');
        }
    }

    /**
     * ✅ تحديث المخزن - استخدم UpdateWarehouseRequest
     */
    public function update(UpdateWarehouseRequest $request, $id)
    {
        try {
            $this->warehouseService->update($id, array_merge(
                $request->validated(),
                ['updated_by' => auth()->id()]
            ));

            return redirect()
                ->route('warehouses.index')
                ->with('success', '✅ تم التحديث بنجاح');

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ حذف المخزن
     */
    public function destroy($id)
    {
        try {
            $this->warehouseService->delete($id);

            return redirect()
                ->route('warehouses.index')
                ->with('success', '✅ تم الحذف بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ تقرير المخزون المنخفض
     */
    public function lowStock($id)
    {
        try {
            $data = $this->warehouseService->getLowStockReport($id);
            return view('warehouses.low-stock', $data);

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ سجل الحركات
     */
    public function movements($id, Request $request)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $productId = $request->input('product_id');
            $movements = $this->movementService->getWarehouseMovements(
                $id,
                $productId,
                $request->except('product_id')
            );

            $warehouses = Warehouse::active()->select('id', 'name', 'code')->get();
            $products = Product::where('is_active', true)->select('id', 'name', 'sku', 'barcode')->orderBy('name')->get();
            
            return view('warehouses.movements', compact('warehouse', 'warehouses', 'products', 'movements'));

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ إضافة منتج للمخزن - صفحة النموذج
     */
    public function createProduct($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $products = Product::where('is_active', true)
                ->select('id', 'name', 'sku', 'barcode', 'code')
                ->orderBy('name')
                ->get();

            return view('warehouses.add-product', compact('warehouse', 'products'));

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ إضافة منتج للمخزن - حفظ
     */
    public function addProduct(Request $request, $id)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|numeric|min:0.001',
                'cost_price' => 'nullable|numeric|min:0',
                'min_stock' => 'nullable|numeric|min:0',
            ]);

            $warehouse = Warehouse::findOrFail($id);
            $warehouse->products()->syncWithoutDetaching([
                $request->product_id => [
                    'quantity' => $request->quantity,
                    'average_cost' => $request->cost_price ?? 0,
                    'min_stock' => $request->min_stock ?? 0,
                ],
            ]);

            return redirect()
                ->route('warehouses.show', $id)
                ->with('success', 'تم إضافة المنتج للمخزن بنجاح');

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ البحث في المخزن
     */
    public function search(Request $request, $id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $query = $warehouse->products();

            if ($search = $request->input('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%")
                      ->orWhere('barcode', 'LIKE', "%{$search}%");
                });
            }

            $products = $query->paginate(20);

            return view('warehouses.search', compact('warehouse', 'products'));

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.show', $id)
                ->with('error', $e->getMessage());
        }
    }
}