<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
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
        return view('warehouses.index', compact('warehouses'));
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
            $movements = $this->movementService->getWarehouseMovements(
                $id,
                $request->all()
            );
            
            return view('warehouses.movements', compact('warehouse', 'movements'));

        } catch (\Exception $e) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', $e->getMessage());
        }
    }
}