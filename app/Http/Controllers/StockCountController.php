<?php

namespace App\Http\Controllers;

use App\Services\StockCountService;
use App\Http\Requests\StoreStockCountRequest;
use App\Http\Requests\CountItemRequest;
use App\Http\Requests\ApproveItemAdjustmentRequest;
use App\Models\ProductWarehouse;
use Illuminate\Http\Request;

class StockCountController extends Controller
{
    public function __construct(
        private StockCountService $stockCountService
    ) {}

    /**
     * قائمة الجرود
     */
    public function index(Request $request)
    {
        $data = $this->stockCountService->getStockCountsList($request->all());
        
        return view('stock-counts.index', $data);
    }

    /**
     * صفحة إنشاء جرد
     */
    public function create()
    {
        $data = $this->stockCountService->getCreateData();
        
        return view('stock-counts.create', $data);
    }

    /**
     * حفظ جرد جديد
     */
    public function store(StoreStockCountRequest $request)
    {
        try {
            $stockCount = $this->stockCountService->store($request->validated());

            return redirect()
                ->route('stock-counts.show', $stockCount->id)
                ->with('success', 'تم إنشاء الجرد بنجاح ✅');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل الجرد
     */
    public function show(int $id, Request $request)
    {
        try {
            $data = $this->stockCountService->getShowData($id, $request->all());
            return view('stock-counts.show', $data);
            
        } catch (\Exception $e) {
            return redirect()
                ->route('stock-counts.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * بدء الجرد
     */
    public function start(int $id)
    {
        try {
            $this->stockCountService->start($id);

            return redirect()
                ->route('stock-counts.count', $id)
                ->with('success', 'تم بدء الجرد بنجاح 🏁');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * صفحة تنفيذ الجرد (الصفحة اللي فيها الجدول لإدخال الكميات)
     */
    public function count(int $id)
    {
        try {
            $data = $this->stockCountService->getCountPageData($id);
            return view('stock-counts.count', $data);
            
        } catch (\Exception $e) {
            return redirect()
                ->route('stock-counts.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * تسجيل جرد منتج (AJAX)
     */
    public function countItem(CountItemRequest $request, int $countId, int $itemId)
    {
        try {
            $item = $this->stockCountService->countItem($itemId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم جرد المنتج بنجاح ✅',
                'data' => $item,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * الموافقة على تعديل منتج (AJAX)
     */
    public function approveItem(ApproveItemAdjustmentRequest $request, int $countId, int $itemId)
    {
        try {
            $item = $this->stockCountService->approveItemAdjustment($itemId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تمت الموافقة بنجاح ✅',
                'data' => $item,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * الموافقة على جميع الفروقات
     */
    public function approveAll(int $countId)
    {
        try {
            $count = $this->stockCountService->approveAllDiscrepancies($countId);

            return redirect()
                ->route('stock-counts.show', $countId)
                ->with('success', "تمت الموافقة على {$count} منتج ✅");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * إتمام الجرد
     */
    public function complete(int $id)
    {
        try {
            $result = $this->stockCountService->complete($id);

            return redirect()
                ->route('stock-counts.show', $id)
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء الجرد
     */
    public function cancel(Request $request, int $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        
        try {
            $this->stockCountService->cancel($id, $request->reason);

            return redirect()
                ->route('stock-counts.index')
                ->with('success', 'تم إلغاء الجرد 🚫');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * الحصول على منتجات المخزن (AJAX) - للجرد الجزئي
     */
    public function getWarehouseProducts($warehouseId)
    {
        try {
            $products = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->whereHas('product', function ($query) {
                    $query->where('is_active', true);
                })
                ->with(['product' => function ($query) {
                    $query->select('id', 'name', 'code', 'sku', 'barcode');
                }])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                        'sku' => $item->product->sku,
                        'barcode' => $item->product->barcode,
                        'quantity' => $item->quantity,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل المنتجات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * طباعة الجرد
     */
    public function print(int $id)
    {
        try {
            $data = $this->stockCountService->getShowData($id);
            return view('stock-counts.print', $data);
            
        } catch (\Exception $e) {
            return redirect()
                ->route('stock-counts.show', $id)
                ->with('error', $e->getMessage());
        }
    }
}