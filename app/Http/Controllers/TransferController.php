<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Services\TransferService;
use App\Services\WarehouseStockService;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        try {
            $transfers  = $this->transferService->getAllTransfers($request->all());
            $warehouses = Warehouse::active()->select('id', 'name', 'code')->get();

            return view('transfers.index', compact('transfers', 'warehouses'));

        } catch (\Exception $e) {
            Log::error('❌ فشل عرض التحويلات', ['error' => $e->getMessage()]);

            return view('transfers.index', [
                'transfers'  => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'warehouses' => collect(),
            ])->with('error', 'حدث خطأ في تحميل البيانات');
        }
    }

    /**
     * عرض صفحة إنشاء تحويل
     */
    public function create()
    {
        try {
            $warehouses = Warehouse::active()
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();

            if ($warehouses->isEmpty()) {
                return redirect()->route('transfers.index')
                    ->with('warning', 'لا توجد مخازن نشطة متاحة');
            }

            $products = Product::where('is_active', true)
                ->select('id', 'name', 'sku', 'barcode', 'code')
                ->orderBy('name')
                ->get();

            if ($products->isEmpty()) {
                return redirect()->route('transfers.index')
                    ->with('warning', 'لا توجد منتجات نشطة متاحة');
            }

            $warehousesStock = $this->stockService->getAllWarehousesStock(
                $warehouses->pluck('id')->toArray()
            );

            return view('transfers.create', compact('warehouses', 'products', 'warehousesStock'));

        } catch (\Exception $e) {
            Log::error('❌ فشل تحميل صفحة التحويل', ['error' => $e->getMessage()]);

            return redirect()->route('transfers.index')
                ->with('error', 'حدث خطأ في تحميل الصفحة: ' . $e->getMessage());
        }
    }

    /**
     * حفظ تحويل جديد
     */
    public function store(TransferRequest $request)
    {
        try {
            $transfer = $this->transferService->createTransfer($request->validated());

            return redirect()->route('transfers.index')
                ->with('success', "✅ تم إنشاء التحويل #{$transfer->transfer_number} وتنفيذه بنجاح");

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('❌ فشل التحويل', ['error' => $e->getMessage()]);

            return back()->withInput()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل التحويل
     */
    public function show($id)
    {
        try {
            $transfer = $this->transferService->getTransferDetails($id);

            return view('transfers.show', compact('transfer'));

        } catch (\Exception $e) {
            Log::error('❌ فشل عرض تفاصيل التحويل', ['error' => $e->getMessage()]);

            return redirect()->route('transfers.index')
                ->with('error', 'حدث خطأ في تحميل التحويل');
        }
    }

    /**
     * عكس التحويل
     */
    public function reverse($id)
    {
        try {
            $transfer = $this->transferService->reverseTransfer($id);

            return redirect()->route('transfers.show', $transfer->id)
                ->with('success', 'تم عكس التحويل بنجاح وإرجاع المنتجات للمخزن المصدر.');

        } catch (\Exception $e) {
            Log::error('❌ فشل عكس التحويل', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء عكس التحويل: ' . $e->getMessage());
        }
    }

    /**
     * إلغاء التحويل
     */
    public function cancel($id)
    {
        try {
            $transfer = $this->transferService->cancelTransfer($id);

            return redirect()->route('transfers.show', $transfer->id)
                ->with('success', 'تم إلغاء التحويل بنجاح.');

        } catch (\Exception $e) {
            Log::error('❌ فشل إلغاء التحويل', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء إلغاء التحويل: ' . $e->getMessage());
        }
    }

    /**
     * التحويلات المعلقة
     */
    public function pending()
    {
        try {
            $transfers = $this->transferService->getPendingTransfers();

            return view('transfers.pending', compact('transfers'));

        } catch (\Exception $e) {
            Log::error('❌ فشل عرض التحويلات المعلقة', ['error' => $e->getMessage()]);

            return redirect()->route('transfers.index')
                ->with('error', 'حدث خطأ في تحميل البيانات');
        }
    }

    /**
     * تصدير التحويلات
     */
    public function export(Request $request)
    {
        try {
            return back()->with('info', 'ميزة التصدير قيد التطوير');

        } catch (\Exception $e) {
            Log::error('❌ فشل تصدير التحويلات', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ في التصدير');
        }
    }
}