<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Services\TransferService;
use App\Services\WarehouseStockService;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
        private WarehouseStockService $stockService
    ) {}

    /**
     * عرض قائمة التحويلات
     */
    public function index(Request $request)
    {
        $transfers = $this->transferService->getAllTransfers($request->all());
        $warehouses = Warehouse::active()->get();
        
        return view('transfers.index', compact('transfers', 'warehouses'));
    }

    /**
     * عرض صفحة إنشاء تحويل
     */
    public function create()
    {
        $warehouses = Warehouse::active()->get();
        
        $products = Product::where('is_active', true)
            ->select('id', 'name', 'sku', 'barcode')
            ->orderBy('name')
            ->get();
        
        $warehousesStock = [];
        foreach ($warehouses as $warehouse) {
            $warehousesStock[$warehouse->id] = $this->stockService->getWarehouseProductsWithStock($warehouse->id);
        }
        
        return view('transfers.create', compact('warehouses', 'products', 'warehousesStock'));
    }

    /**
     * حفظ تحويل جديد
     */
    public function store(TransferRequest $request)
    {
        try {
            $transfer = $this->transferService->createTransfer($request->validated());

            return redirect()
                ->route('transfers.show', $transfer->id)
                ->with('success', 'تم إنشاء التحويل وتنفيذه بنجاح ✅');

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل التحويل
     */
    public function show($id)
    {
        try {
            $transfer = $this->transferService->getTransferDetails($id);
            
            // ✅ الحل - ارجع transfers.show مش index
            return view('transfers.show', compact('transfer'));

        } catch (\Exception $e) {
            return redirect()
                ->route('transfers.index')
                ->with('error', 'التحويل غير موجود');
        }
    }

    /**
     * عكس التحويل
     */
    public function reverse($id)
    {
        try {
            $transfer = $this->transferService->reverseTransfer($id);

            return redirect()
                ->route('transfers.show', $id)
                ->with('success', 'تم عكس التحويل بنجاح ✅');

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء التحويل
     */
    public function cancel($id)
    {
        try {
            $transfer = $this->transferService->cancelTransfer($id);

            return redirect()
                ->route('transfers.index')
                ->with('success', 'تم إلغاء التحويل');

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * التحويلات المعلقة
     */
    public function pending()
    {
        $transfers = $this->transferService->getPendingTransfers();
        
        // ✅ الحل - ارجع transfers.pending مش index
        return view('transfers.pending', compact('transfers'));
    }
}
