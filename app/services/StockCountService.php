<?php

namespace App\Services;

use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use App\Traits\ManagesStockCountStatus;
use App\Traits\OptimizesStockCountQueries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class StockCountService
{
    use ManagesStockCountStatus, OptimizesStockCountQueries;

    public function __construct(
        private InventoryMovementService $movementService
    ) {}

    /**
     * جلب قائمة الجرود مع الفلاتر
     */
    public function getStockCountsList(array $filters = []): array
    {
        $query = StockCount::query()
            ->select([
                'id',
                'count_number',
                'warehouse_id',
                'count_date',
                'count_type',
                'status',
                'total_items',
                'items_counted',
                'discrepancies',
                'created_at',
            ])
            ->with([
                'warehouse:id,name,code',
                'creator:id,name',
            ]);

        // الفلاتر
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['count_type'])) {
            $query->where('count_type', $filters['count_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('count_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('count_date', '<=', $filters['date_to']);
        }

        $query->orderByDesc('count_date')->orderByDesc('id');

        $stockCounts = $query->paginate($filters['per_page'] ?? 20);

        $warehouses = Warehouse::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return [
            'stockCounts' => $stockCounts,
            'warehouses' => $warehouses,
            'filters' => $filters,
        ];
    }

    /**
     * جلب بيانات صفحة إنشاء الجرد
     */
    public function getCreateData(): array
    {
        $warehouses = Warehouse::where('is_active', true)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return [
            'warehouses' => $warehouses,
        ];
    }

    /**
     * إنشاء جرد جديد
     */
    public function store(array $data): StockCount
    {
        DB::beginTransaction();

        try {
            // التحقق من عدم وجود جرد نشط (من الـ Trait)
            $this->validateNoActiveCount($data['warehouse_id']);

            // إنشاء الجرد
            $stockCount = StockCount::create([
                'count_number' => $this->generateCountNumber(),
                'warehouse_id' => $data['warehouse_id'],
                'count_date' => $data['count_date'] ?? now(),
                'count_type' => $data['count_type'] ?? 'full',
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // جلب المنتجات بشكل محسّن (من الـ Trait)
            $filters = [
                'product_ids' => $data['product_ids'] ?? null,
                'random_count' => $data['random_count'] ?? null,
                'with_stock_only' => true,
            ];

            $products = $this->getProductsForCount($data['warehouse_id'], $filters);

            if ($products->isEmpty()) {
                throw new Exception('❌ لا يوجد منتجات للجرد في هذا المخزن');
            }

            // إنشاء العناصر بشكل دفعي
            $itemsData = $this->prepareItemsData($stockCount->id, $products);
            
            DB::table('stock_count_items')->insert($itemsData);

            $stockCount->update(['total_items' => count($itemsData)]);

            DB::commit();

            Log::info('✅ تم إنشاء جرد جديد', [
                'count_id' => $stockCount->id,
                'count_number' => $stockCount->count_number,
                'warehouse_id' => $data['warehouse_id'],
                'total_items' => count($itemsData),
                'user_id' => auth()->id(),
            ]);

            return $stockCount->load('warehouse');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('❌ خطأ في إنشاء الجرد', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * جلب بيانات صفحة عرض الجرد
     */
/**
 * جلب بيانات صفحة عرض الجرد
 */
/**
 * جلب بيانات صفحة تنفيذ الجرد
 */
public function getCountPageData(int $countId): array
{
    $stockCount = StockCount::with([
        'warehouse:id,name,code',
        'creator:id,name',
        'items' => function($query) {
            $query->with('product:id,name,sku,barcode')
                  ->orderBy('status', 'asc')
                  ->orderByRaw('ABS(variance) DESC');
        }
    ])->findOrFail($countId);

    // التحقق من الحالة
    if ($stockCount->status !== 'in_progress') {
        throw new \Exception('❌ يجب أن يكون الجرد في حالة "جاري التنفيذ"');
    }

    return [
        'stockCount' => $stockCount,
    ];
}
public function getShowData(int $countId, array $filters = []): array
{
    $stockCount = StockCount::with([
        'warehouse:id,name,code',
        'creator:id,name',
        'completer:id,name',
        'items.product:id,name,sku,barcode',  // ✅ ضيف الـ items مع الـ product
    ])->findOrFail($countId);

    // الإحصائيات (من الـ Trait)
    $summary = $this->getStockCountStats($countId);

    // جلب العناصر مع الفلاتر (من الـ Trait)
    $perPage = $filters['per_page'] ?? 50;
    
    // ✅ لو عايز الـ items كاملة (مش paginated)
    $items = $stockCount->items;

    return [
        'stock_count' => $stockCount,  // ✅ غيّرت من stockCount لـ stock_count
        'summary' => $summary,
        'items' => $items,
        'filters' => $filters,
    ];
}    /**
     * بدء الجرد
     */
    public function start(int $countId): void
    {
        DB::beginTransaction();

        try {
            $stockCount = StockCount::lockForUpdate()->findOrFail($countId);

            // التحقق من الحالة (من الـ Trait)
            $this->validateStockCountStatus($stockCount, 'draft');

            // تغيير الحالة (من الـ Trait)
            $this->changeStockCountStatus($stockCount, 'in_progress', [
                'started_at' => now(),
            ]);

            DB::commit();

            Log::info('🏁 تم بدء الجرد', [
                'count_id' => $countId,
                'count_number' => $stockCount->count_number,
                'user_id' => auth()->id(),
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('❌ خطأ في بدء الجرد', [
                'count_id' => $countId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * جرد منتج
     */
    public function countItem(int $itemId, array $data): StockCountItem
    {
        DB::beginTransaction();

        try {
            $item = StockCountItem::with('stockCount:id,status,count_number')
                ->lockForUpdate()
                ->findOrFail($itemId);

            // التحقق من الحالة (من الـ Trait)
            $this->validateStockCountStatus($item->stockCount, 'in_progress');

            if ($item->status !== 'pending') {
                throw new Exception('❌ تم جرد هذا المنتج مسبقاً');
            }

            $variance = round($data['actual_quantity'] - $item->system_quantity, 3);

            $item->update([
                'actual_quantity' => $data['actual_quantity'],
                'variance' => $variance,
                'status' => 'counted',
                'notes' => $data['notes'] ?? null,
                'counted_by' => auth()->id(),
                'counted_at' => now(),
                'adjustment_approved' => false,
            ]);

            // تحديث الإحصائيات (من الـ Trait)
            $this->updateCountStats($item->stock_count_id);

            DB::commit();

            Log::info('📊 تم جرد منتج', [
                'item_id' => $itemId,
                'product_id' => $item->product_id,
                'variance' => $variance,
                'user_id' => auth()->id(),
            ]);

            $this->clearCountCache($item->stock_count_id);

            return $item->fresh(['product', 'counter']);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('❌ خطأ في جرد المنتج', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * الموافقة على تعديل
     */
    public function approveItemAdjustment(int $itemId, array $data): StockCountItem
    {
        DB::beginTransaction();

        try {
            $item = StockCountItem::lockForUpdate()->findOrFail($itemId);

            if ($item->variance == 0) {
                throw new Exception('❌ هذا المنتج ليس به فروقات');
            }

            if ($item->status === 'pending') {
                throw new Exception('❌ يجب جرد المنتج أولاً');
            }

            $item->update([
                'adjustment_approved' => $data['approved'],
                'approval_notes' => $data['approval_notes'] ?? null,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            $message = $data['approved'] ? '✅ تمت الموافقة' : '❌ تم الرفض';

            Log::info($message, [
                'item_id' => $itemId,
                'variance' => $item->variance,
                'user_id' => auth()->id(),
            ]);

            $this->clearCountCache($item->stock_count_id);

            return $item->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * الموافقة على جميع الفروقات
     */
    public function approveAllDiscrepancies(int $countId): int
    {
        DB::beginTransaction();

        try {
            $stockCount = StockCount::lockForUpdate()->findOrFail($countId);

            $updated = DB::table('stock_count_items')
                ->where('stock_count_id', $countId)
                ->where('variance', '!=', 0)
                ->where('adjustment_approved', false)
                ->whereIn('status', ['counted', 'adjusted'])
                ->update([
                    'adjustment_approved' => true,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            Log::info('✅ موافقة جماعية', [
                'count_id' => $countId,
                'approved_count' => $updated,
                'user_id' => auth()->id(),
            ]);

            $this->clearCountCache($countId);

            return $updated;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * إتمام الجرد
     */
    public function complete(int $countId): array
    {
        DB::beginTransaction();

        try {
            $stockCount = StockCount::lockForUpdate()->findOrFail($countId);

            // التحقق من الحالة (من الـ Trait)
            $this->validateStockCountStatus($stockCount, 'in_progress');
            $this->validateCountComplete($stockCount);

            // جلب العناصر المعتمدة
            $approvedItems = $this->getApprovedItemsForAdjustment($countId);

            $appliedCount = 0;

            // تطبيق التسويات
            foreach ($approvedItems as $item) {
                $this->applyStockAdjustment($stockCount, $item);
                $appliedCount++;
            }

            // تحديث حالات العناصر (Bulk Update من الـ Trait)
            $this->updateItemsStatusAfterComplete($countId);

            $skippedCount = DB::table('stock_count_items')
                ->where('stock_count_id', $countId)
                ->where('status', 'skipped')
                ->count();

            // تغيير حالة الجرد (من الـ Trait)
            $this->changeStockCountStatus($stockCount, 'completed', [
                'completed_by' => auth()->id(),
                'completed_at' => now(),
                'adjustments_applied' => $appliedCount,
                'adjustments_skipped' => $skippedCount,
            ]);

            DB::commit();

            Log::info('✅ تم إتمام الجرد', [
                'count_id' => $countId,
                'applied' => $appliedCount,
                'skipped' => $skippedCount,
                'user_id' => auth()->id(),
            ]);

            $this->clearCountCache($countId);

            return [
                'message' => "تم إتمام الجرد بنجاح ✅\nتطبيق {$appliedCount} تعديل - تخطي {$skippedCount} تعديل",
                'applied' => $appliedCount,
                'skipped' => $skippedCount,
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('❌ خطأ في إتمام الجرد', [
                'count_id' => $countId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * إلغاء الجرد
     */
    public function cancel(int $countId, ?string $reason = null): void
    {
        DB::beginTransaction();

        try {
            $stockCount = StockCount::lockForUpdate()->findOrFail($countId);

            if ($stockCount->status === 'completed') {
                throw new Exception('❌ لا يمكن إلغاء جرد مكتمل');
            }

            // تغيير الحالة (من الـ Trait)
            $this->changeStockCountStatus($stockCount, 'cancelled', [
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'notes' => $reason ? "سبب الإلغاء: {$reason}" : $stockCount->notes,
            ]);

            DB::commit();

            Log::info('🚫 تم إلغاء الجرد', [
                'count_id' => $countId,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            $this->clearCountCache($countId);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * جلب منتجات المخزن (AJAX)
     */
    public function getWarehouseProducts(int $warehouseId): array
    {
        $products = DB::table('product_warehouses as pw')
            ->join('products as p', 'pw.product_id', '=', 'p.id')
            ->where('pw.warehouse_id', $warehouseId)
            ->where('p.is_active', true)
            ->where('pw.quantity', '>', 0)
            ->select([
                'p.id',
                'p.name',
                'p.sku',
                'p.barcode',
                'pw.quantity',
            ])
            ->orderBy('p.name')
            ->limit(1000)
            ->get()
            ->toArray();

        return $products;
    }

    // ==================== Private Helper Methods ====================

    /**
     * تجهيز بيانات العناصر للـ Bulk Insert
     */
    private function prepareItemsData(int $stockCountId, $products): array
    {
        $itemsData = [];
        $now = now();

        foreach ($products as $product) {
            $itemsData[] = [
                'stock_count_id' => $stockCountId,
                'product_id' => $product->product_id,
                'system_quantity' => $product->system_quantity,
                'actual_quantity' => null,
                'variance' => 0,
                'status' => 'pending',
                'adjustment_approved' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $itemsData;
    }

    /**
     * جلب العناصر المعتمدة للتسوية
     */
    private function getApprovedItemsForAdjustment(int $countId)
    {
        return DB::table('stock_count_items as sci')
            ->join('products as p', 'sci.product_id', '=', 'p.id')
            ->where('sci.stock_count_id', $countId)
            ->where('sci.variance', '!=', 0)
            ->where('sci.adjustment_approved', true)
            ->select([
                'sci.id',
                'sci.product_id',
                'sci.variance',
                'sci.actual_quantity',
                'sci.notes',
                'p.name as product_name',
            ])
            ->get();
    }

    /**
     * تطبيق التسوية على المخزون
     */
    private function applyStockAdjustment(StockCount $stockCount, $item): void
    {
        $movementType = $item->variance > 0 ? 'adjustment_in' : 'adjustment_out';

        $this->movementService->recordMovement([
            'warehouse_id' => $stockCount->warehouse_id,
            'product_id' => $item->product_id,
            'movement_type' => $movementType,
            'quantity_change' => $item->variance,
            'notes' => "تسوية جرد #{$stockCount->count_number}" .
                       ($item->notes ? " - {$item->notes}" : ''),
            'reference_type' => StockCount::class,
            'reference_id' => $stockCount->id,
            'movement_date' => $stockCount->count_date,
        ]);

        // تحديث ProductWarehouse
        DB::table('product_warehouses')
            ->where('product_id', $item->product_id)
            ->where('warehouse_id', $stockCount->warehouse_id)
            ->update([
                'last_count_quantity' => $item->actual_quantity,
                'last_count_date' => $stockCount->count_date,
                'adjustment_total' => DB::raw("adjustment_total + {$item->variance}"),
                'updated_at' => now(),
            ]);
    }

    /**
     * تحديث حالات العناصر بعد الإتمام
     */
    private function updateItemsStatusAfterComplete(int $countId): void
    {
        DB::table('stock_count_items')
            ->where('stock_count_id', $countId)
            ->where('variance', '!=', 0)
            ->update([
                'status' => DB::raw("IF(adjustment_approved = 1, 'adjusted', 'skipped')"),
                'updated_at' => now(),
            ]);
    }

    /**
     * توليد رقم جرد
     */
    private function generateCountNumber(): string
    {
        $date = now()->format('Ymd');

        $lastSequence = DB::table('stock_counts')
            ->whereDate('created_at', today())
            ->max(DB::raw('CAST(SUBSTRING(count_number, -4) AS UNSIGNED)'));

        $sequence = ($lastSequence ?? 0) + 1;

        return 'CNT' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * مسح الكاش
     */
    private function clearCountCache(int $countId): void
    {
        Cache::forget("stock_count_report_{$countId}");
        Cache::forget("stock_count_stats_{$countId}");
    }
}