<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait OptimizesStockCountQueries
{
    /**
     * تحميل العلاقات الضرورية بكفاءة
     */
    protected function loadStockCountRelations(Builder $query): Builder
    {
        return $query->with([
            'warehouse:id,name,code',
            'creator:id,name',
            'items' => function ($q) {
                $q->select([
                    'id',
                    'stock_count_id',
                    'product_id',
                    'system_quantity',
                    'actual_quantity',
                    'variance',
                    'status',
                    'adjustment_approved',
                ])
                ->with('product:id,name,sku,barcode')
                ->orderBy('status', 'asc') // pending أولاً
                ->orderBy('variance', 'desc'); // الفروقات الأكبر أولاً
            },
        ]);
    }
    /**
 * جلب عناصر مع الفلاتر بشكل محسّن
 */
protected function getPaginatedItemsWithFilters(int $stockCountId, array $filters, int $perPage = 50)
{
    $query = DB::table('stock_count_items as sci')
        ->join('products as p', 'sci.product_id', '=', 'p.id')
        ->leftJoin('users as u', 'sci.counted_by', '=', 'u.id')
        ->leftJoin('users as approver', 'sci.approved_by', '=', 'approver.id')
        ->where('sci.stock_count_id', $stockCountId)
        ->select([
            'sci.id',
            'sci.product_id',
            'p.name as product_name',
            'p.sku',
            'p.barcode',
            'sci.system_quantity',
            'sci.actual_quantity',
            'sci.variance',
            'sci.status',
            'sci.adjustment_approved',
            'sci.notes',
            'sci.approval_notes',
            'u.name as counted_by_name',
            'approver.name as approved_by_name',
            'sci.counted_at',
            'sci.approved_at',
        ]);

    // فلاتر العناصر
    if (!empty($filters['status'])) {
        $query->where('sci.status', $filters['status']);
    }

    if (!empty($filters['has_variance'])) {
        if ($filters['has_variance'] === 'yes') {
            $query->where('sci.variance', '!=', 0);
        } else {
            $query->where('sci.variance', '=', 0);
        }
    }

    if (!empty($filters['approval_status'])) {
        if ($filters['approval_status'] === 'approved') {
            $query->where('sci.adjustment_approved', true);
        } elseif ($filters['approval_status'] === 'pending') {
            $query->where('sci.variance', '!=', 0)
                  ->where('sci.adjustment_approved', false);
        }
    }

    // الترتيب
    $query->orderByRaw("FIELD(sci.status, 'pending', 'counted', 'adjusted', 'skipped')")
          ->orderByDesc(DB::raw('ABS(sci.variance)'));

    return $query->paginate($perPage);
}

    /**
     * استعلام محسّن لجلب عناصر الجرد بالـ pagination
     */
    protected function getPaginatedItems(int $stockCountId, int $perPage = 50)
    {
        return DB::table('stock_count_items as sci')
            ->join('products as p', 'sci.product_id', '=', 'p.id')
            ->leftJoin('users as u', 'sci.counted_by', '=', 'u.id')
            ->where('sci.stock_count_id', $stockCountId)
            ->select([
                'sci.id',
                'sci.product_id',
                'p.name as product_name',
                'p.sku',
                'p.barcode',
                'sci.system_quantity',
                'sci.actual_quantity',
                'sci.variance',
                'sci.status',
                'sci.adjustment_approved',
                'sci.notes',
                'u.name as counted_by_name',
                'sci.counted_at',
            ])
            ->orderByRaw("FIELD(sci.status, 'pending', 'counted', 'adjusted', 'skipped')")
            ->orderByDesc(DB::raw('ABS(sci.variance)'))
            ->paginate($perPage);
    }

    /**
     * استعلام مُحسّن لإحصائيات الجرد
     */
    protected function getStockCountStats(int $stockCountId): array
    {
        $stats = DB::table('stock_count_items')
            ->where('stock_count_id', $stockCountId)
            ->selectRaw('
                COUNT(*) as total_items,
                SUM(CASE WHEN status = "counted" OR status = "adjusted" THEN 1 ELSE 0 END) as items_counted,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as items_pending,
                SUM(CASE WHEN variance != 0 THEN 1 ELSE 0 END) as items_with_variance,
                SUM(CASE WHEN variance = 0 AND status != "pending" THEN 1 ELSE 0 END) as items_matched,
                SUM(CASE WHEN variance > 0 THEN variance ELSE 0 END) as total_surplus,
                SUM(CASE WHEN variance < 0 THEN ABS(variance) ELSE 0 END) as total_shortage,
                SUM(CASE WHEN variance != 0 AND adjustment_approved = 1 THEN 1 ELSE 0 END) as approved_adjustments,
                SUM(CASE WHEN variance != 0 AND adjustment_approved = 0 THEN 1 ELSE 0 END) as pending_approvals
            ')
            ->first();

        return [
            'total_items' => $stats->total_items ?? 0,
            'items_counted' => $stats->items_counted ?? 0,
            'items_pending' => $stats->items_pending ?? 0,
            'items_with_variance' => $stats->items_with_variance ?? 0,
            'items_matched' => $stats->items_matched ?? 0,
            'total_surplus' => round($stats->total_surplus ?? 0, 3),
            'total_shortage' => round($stats->total_shortage ?? 0, 3),
            'approved_adjustments' => $stats->approved_adjustments ?? 0,
            'pending_approvals' => $stats->pending_approvals ?? 0,
            'progress_percentage' => $stats->total_items > 0 
                ? round(($stats->items_counted / $stats->total_items) * 100, 2) 
                : 0,
        ];
    }

    /**
     * استعلام مُحسّن لجلب المنتجات للجرد
     */
// ✅ الـ Trait المُعدّل
protected function getProductsForCount(int $warehouseId, array $filters = [])
{
    $query = DB::table('product_warehouse as pw')
        ->join('products as p', 'pw.product_id', '=', 'p.id')
        ->where('pw.warehouse_id', $warehouseId)
        ->where('p.is_active', true)
        ->select([
            'pw.product_id',
            'p.name',
            'p.sku',
            'p.barcode',
            'pw.quantity as system_quantity',
        ]);

    if (!empty($filters['product_ids'])) {
        $query->whereIn('pw.product_id', $filters['product_ids']);
    }

    if (!empty($filters['with_stock_only'])) {
        $query->where('pw.quantity', '>', 0);
    }

    if (!empty($filters['random_count'])) {
        $query->inRandomOrder()->limit($filters['random_count']);
    }

    return $query->get();
}
    /**
     * تحديث إحصائيات الجرد بكفاءة
     */
    protected function updateCountStats(int $stockCountId): void
    {
        $stats = $this->getStockCountStats($stockCountId);

        DB::table('stock_counts')
            ->where('id', $stockCountId)
            ->update([
                'total_items' => $stats['total_items'],
                'items_counted' => $stats['items_counted'],
                'discrepancies' => $stats['items_with_variance'],
                'updated_at' => now(),
            ]);
    }

    /**
     * تحديث دفعي لحالات العناصر (Bulk Update)
     */
    protected function bulkUpdateItemStatus(array $itemIds, string $status, array $additionalData = []): int
    {
        $updateData = array_merge(['status' => $status], $additionalData);
        
        return DB::table('stock_count_items')
            ->whereIn('id', $itemIds)
            ->update($updateData);
    }

    /**
     * حذف الجرد بكفاءة (مع العناصر)
     */
    protected function deleteStockCountEfficiently(int $stockCountId): void
    {
        DB::transaction(function () use ($stockCountId) {
            // حذف العناصر أولاً (CASCADE في DB لكن نفضل التحكم)
            DB::table('stock_count_items')
                ->where('stock_count_id', $stockCountId)
                ->delete();

            // حذف الجرد
            DB::table('stock_counts')
                ->where('id', $stockCountId)
                ->delete();
        });
    }
}