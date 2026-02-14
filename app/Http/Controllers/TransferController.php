<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\WarehouseTransfer;
use App\Services\TransferService;
use App\Services\WarehouseStockService;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
        private WarehouseStockService $stockService
    ) {
        // ✅ لا يوجد أي middleware
    }

    /**
     * عرض قائمة التحويلات
     */
    public function index(Request $request)
    {
        try {
            $transfers = $this->transferService->getAllTransfers($request->all());
            $warehouses = Warehouse::active()->select('id', 'name', 'code')->get();
            
            return view('transfers.index', compact('transfers', 'warehouses'));
            
        } catch (\Exception $e) {
            Log::error('❌ فشل عرض التحويلات', [
                'error' => $e->getMessage()
            ]);
            
            return view('transfers.index', [
                'transfers' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'warehouses' => collect()
            ])->with('error', 'حدث خطأ في تحميل البيانات');
        }
    }

    /**
     * عرض صفحة إنشاء تحويل
     */
    public function create()
    {
        try {
            // جلب المخازن النشطة فقط
            $warehouses = Warehouse::active()
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();

            if ($warehouses->isEmpty()) {
                return redirect()
                    ->route('transfers.index')
                    ->with('warning', 'لا توجد مخازن نشطة متاحة');
            }

            // جلب المنتجات النشطة فقط
            $products = Product::where('is_active', true)
                ->select('id', 'name', 'sku', 'barcode', 'code')
                ->orderBy('name')
                ->get();

            if ($products->isEmpty()) {
                return redirect()
                    ->route('transfers.index')
                    ->with('warning', 'لا توجد منتجات نشطة متاحة');
            }

            // جلب المخزون لكل مخزن - محسّن
            $warehousesStock = $this->stockService->getAllWarehousesStock(
                $warehouses->pluck('id')->toArray()
            );

            return view('transfers.create', compact('warehouses', 'products', 'warehousesStock'));
            
        } catch (\Exception $e) {
            Log::error('❌ فشل تحميل صفحة التحويل', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->route('transfers.index')
                ->with('error', 'حدث خطأ في تحميل الصفحة: ' . $e->getMessage());
        }
    }

    /**
     * حفظ تحويل جديد
     */
public function store(TransferRequest $request)
{
    try {
        // ✅ اطبع الداتا المستلمة
        Log::info('📥 Request Data', [
            'from' => $request->from_warehouse_id,
            'to' => $request->to_warehouse_id,
            'items' => $request->items,
            'items_count' => count($request->items ?? [])
        ]);
        
        DB::beginTransaction();
        
        $transfer = $this->transferService->createTransfer($request->validated());

        DB::commit();

        Log::info('✅ تم التحويل بنجاح', [
            'transfer_id' => $transfer->id,
            'transfer_number' => $transfer->transfer_number
        ]);

        return redirect()
            ->route('transfers.index')
            ->with('success', "✅ تم إنشاء التحويل #{$transfer->transfer_number} وتنفيذه بنجاح");

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        
        Log::warning('⚠️ Validation Failed', [
            'errors' => $e->errors(),
            'input' => $request->except(['_token'])
        ]);
        
        throw $e;
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('❌ فشل التحويل', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()
            ->withInput()
            ->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

    /**
     * عرض تفاصيل التحويل
     */
  public function show($id)
    {
        $transfer = WarehouseTransfer::with([
            'fromWarehouse:id,name,code,location',
            'toWarehouse:id,name,code,location',
            'items.product:id,name,sku,unit,purchase_price,selling_price',
            'createdBy:id,name',
            'confirmedBy:id,name',
            'receivedBy:id,name',
            'reversedBy:id,name',
            'cancelledBy:id,name',
        ])->findOrFail($id);

        // ✅ جلب الكميات الحالية والتاريخية لكل منتج في كلا المخزنين
        foreach ($transfer->items as $item) {
            // الكميات الحالية في المخازن
$item->current_from_qty = DB::table('product_warehouse')
    ->where('warehouse_id', $transfer->from_warehouse_id)
    ->where('product_id', $item->product_id)
    ->value('quantity') ?? 0;

$item->current_to_qty = DB::table('product_warehouse')
    ->where('warehouse_id', $transfer->to_warehouse_id)
    ->where('product_id', $item->product_id)
    ->value('quantity') ?? 0;

            // ✅ حساب الكميات قبل وبعد التحويل بناءً على الحالة
            if ($transfer->status === 'received') {
                // التحويل تم - المخزن المصدر نقص والمخزن الوجهة زاد
                $item->before_from_qty = $item->current_from_qty + $item->quantity;
                $item->after_from_qty = $item->current_from_qty;
                
                $item->before_to_qty = $item->current_to_qty - $item->quantity;
                $item->after_to_qty = $item->current_to_qty;
                
            } elseif ($transfer->status === 'reversed') {
                // التحويل معكوس - المخزون رجع لحالته الأصلية
                $item->before_from_qty = $item->current_from_qty - $item->quantity;
                $item->after_from_qty = $item->current_from_qty;
                
                $item->before_to_qty = $item->current_to_qty + $item->quantity;
                $item->after_to_qty = $item->current_to_qty;
                
            } else {
                // pending, draft, أو cancelled - لا تغيير في المخزون
                $item->before_from_qty = $item->current_from_qty;
                $item->after_from_qty = $item->current_from_qty;
                
                $item->before_to_qty = $item->current_to_qty;
                $item->after_to_qty = $item->current_to_qty;
            }
        }

        return view('transfers.show', compact('transfer'));
    }

    /**
     * عكس التحويل
     */
    public function reverse($id)
    {
        try {
            DB::beginTransaction();
            
            $transfer = WarehouseTransfer::findOrFail($id);
            
            // التحقق من أن التحويل مستلم
            if ($transfer->status !== 'received') {
                return back()->with('error', 'لا يمكن عكس هذا التحويل. يجب أن يكون التحويل في حالة "مستلم".');
            }

            // عكس الكميات في المخازن
            foreach ($transfer->items as $item) {
                // إرجاع الكمية للمخزن المصدر
                DB::table('StockCounts')
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->increment('quantity', $item->quantity);

                // خصم الكمية من المخزن الوجهة
                DB::table('StockCounts')
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('quantity', $item->quantity);

                // تسجيل حركة المخزون
                DB::table('inventory_movements')->insert([
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'product_id' => $item->product_id,
                    'movement_type' => 'reverse_transfer',
                    'quantity' => $item->quantity,
                    'reference_type' => 'warehouse_transfer',
                    'reference_id' => $transfer->id,
                    'notes' => 'عكس تحويل رقم ' . $transfer->transfer_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // تحديث حالة التحويل
            $transfer->update([
                'status' => 'reversed',
                'reversed_at' => now(),
                'reversed_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('transfers.show', $transfer->id)
                ->with('success', 'تم عكس التحويل بنجاح وإرجاع المنتجات للمخزن المصدر.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'حدث خطأ أثناء عكس التحويل: ' . $e->getMessage());
        }
    }


    /**
     * إلغاء التحويل
     */
    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            
            $transfer = WarehouseTransfer::findOrFail($id);
            
            // التحقق من أن التحويل قابل للإلغاء
            if (!in_array($transfer->status, ['draft', 'pending', 'received'])) {
                return back()->with('error', 'لا يمكن إلغاء هذا التحويل.');
            }

            // إذا كان التحويل مستلم، أرجع المنتجات
            if ($transfer->status === 'received') {
                foreach ($transfer->items as $item) {
                    // إرجاع الكمية للمخزن المصدر
                    DB::table('StockCounts')
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', $item->quantity);

                    // خصم الكمية من المخزن الوجهة
                    DB::table('StockCounts')
                        ->where('warehouse_id', $transfer->to_warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('quantity', $item->quantity);
                }
            }

            // تحديث حالة التحويل
            $transfer->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('transfers.show', $transfer->id)
                ->with('success', 'تم إلغاء التحويل بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            
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
            Log::error('❌ فشل عرض التحويلات المعلقة', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()
                ->route('transfers.index')
                ->with('error', 'حدث خطأ في تحميل البيانات');
        }
    }

    /**
     * طباعة التحويل
     */
    // public function print($id)
    // {
    //     try {
    //         $transfer = $this->transferService->getTransferDetails($id);
            
    //         return view('transfers.print', compact('transfer'));
            
    //     } catch (\Exception $e) {
    //         Log::error('❌ فشل طباعة التحويل', [
    //             'id' => $id,
    //             'error' => $e->getMessage()
    //         ]);
            
    //         return back()->with('error', 'حدث خطأ في الطباعة');
    //     }
    // }

    /**
     * تصدير التحويلات إلى Excel
     */
    public function export(Request $request)
    {
        try {
            return back()->with('info', 'ميزة التصدير قيد التطوير');
            
        } catch (\Exception $e) {
            Log::error('❌ فشل تصدير التحويلات', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'حدث خطأ في التصدير');
        }
    }
}