<?php

namespace App\Services;

use App\Models\ProductWarehouse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarehouseStockService
{
    private const CACHE_TTL = 300; // 5 دقائق
    private const BATCH_SIZE = 500;

    /**
     * ✅ جلب مخزون مخزن واحد - مع كاش
     */
    public function getWarehouseProductsWithStock(int $warehouseId): array
    {
        $cacheKey = "warehouse_stock_{$warehouseId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($warehouseId) {
            return ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('quantity', '>', 0)
                ->select('product_id', 'quantity', 'reserved_quantity', 'min_stock')
                ->get()
                ->mapWithKeys(function ($stock) {
                    return [
                        $stock->product_id => [
                            'quantity' => $stock->quantity,
                            'reserved' => $stock->reserved_quantity ?? 0,
                            'available' => $stock->quantity - ($stock->reserved_quantity ?? 0),
                            'min_stock' => $stock->min_stock ?? 0,
                        ]
                    ];
                })
                ->toArray();
        });
    }

    /**
     * ✅ جلب مخزون عدة مخازن - محسّن
     */
    public function getAllWarehousesStock(array $warehouseIds): array
    {
        if (empty($warehouseIds)) {
            return [];
        }

        $result = [];

        foreach ($warehouseIds as $warehouseId) {
            $result[$warehouseId] = $this->getWarehouseProductsWithStock($warehouseId);
        }

        return $result;
    }

    /**
     * ✅ جلب المنتجات المتاحة في مخزن معين
     */
    public function getAvailableProducts(int $warehouseId, array $filters = []): \Illuminate\Support\Collection
    {
        $query = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->with(['product' => function ($q) {
                $q->select('id', 'name', 'sku', 'code', 'barcode')
                  ->where('is_active', true);
            }]);

        // فلترة حسب الكمية الدنيا
        if (isset($filters['min_quantity'])) {
            $query->where('quantity', '>=', $filters['min_quantity']);
        }

        // فلترة المنتجات تحت الحد الأدنى
        if (isset($filters['below_min_stock']) && $filters['below_min_stock']) {
            $query->whereRaw('quantity < min_stock');
        }

        return $query->get()->map(function ($stock) {
            return [
                'product_id' => $stock->product_id,
                'product_name' => $stock->product?->name ?? 'غير معروف',
                'sku' => $stock->product?->sku ?? '',
                'quantity' => $stock->quantity,
                'available' => $stock->quantity - ($stock->reserved_quantity ?? 0),
                'reserved' => $stock->reserved_quantity ?? 0,
                'min_stock' => $stock->min_stock ?? 0,
            ];
        });
    }

    /**
     * ✅ التحقق من توفر كمية منتج في مخزن
     */
    public function checkAvailability(int $warehouseId, int $productId, float $quantity): bool
    {
        $stock = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        if (!$stock) {
            return false;
        }

        $available = $stock->quantity - ($stock->reserved_quantity ?? 0);
        
        return $available >= $quantity;
    }

    /**
     * ✅ التحقق من توفر عدة منتجات - Batch
     */
    public function checkBulkAvailability(int $warehouseId, array $items): array
    {
        $productIds = array_column($items, 'product_id');

        $stocks = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $results = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            if (!isset($stocks[$productId])) {
                $results[$productId] = [
                    'available' => false,
                    'current_stock' => 0,
                    'required' => $quantity,
                    'shortage' => $quantity,
                ];
                continue;
            }

            $stock = $stocks[$productId];
            $available = $stock->quantity - ($stock->reserved_quantity ?? 0);

            $results[$productId] = [
                'available' => $available >= $quantity,
                'current_stock' => $available,
                'required' => $quantity,
                'shortage' => max(0, $quantity - $available),
            ];
        }

        return $results;
    }

    /**
     * ✅ حجز كمية (للطلبات)
     */
    public function reserveQuantity(int $warehouseId, int $productId, float $quantity): bool
    {
        try {
            DB::beginTransaction();

            $stock = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new \Exception('المنتج غير موجود في المخزن');
            }

            $available = $stock->quantity - ($stock->reserved_quantity ?? 0);

            if ($available < $quantity) {
                throw new \Exception('الكمية غير متوفرة');
            }

            $stock->increment('reserved_quantity', $quantity);

            DB::commit();

            $this->clearCache($warehouseId);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ فشل حجز الكمية', [
                'warehouse' => $warehouseId,
                'product' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * ✅ إلغاء حجز كمية
     */
    public function releaseQuantity(int $warehouseId, int $productId, float $quantity): bool
    {
        try {
            DB::beginTransaction();

            $stock = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new \Exception('المنتج غير موجود في المخزن');
            }

            $newReserved = max(0, ($stock->reserved_quantity ?? 0) - $quantity);
            
            $stock->update(['reserved_quantity' => $newReserved]);

            DB::commit();

            $this->clearCache($warehouseId);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ فشل إلغاء حجز الكمية', [
                'warehouse' => $warehouseId,
                'product' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * ✅ الحصول على إحصائيات مخزن
     */
    public function getWarehouseStatistics(int $warehouseId): array
    {
        $cacheKey = "warehouse_stats_{$warehouseId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($warehouseId) {
            
            $stats = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->selectRaw('
                    COUNT(*) as total_products,
                    SUM(quantity) as total_quantity,
                    SUM(reserved_quantity) as total_reserved,
                    SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as products_in_stock,
                    SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as products_out_of_stock,
                    SUM(CASE WHEN quantity < min_stock THEN 1 ELSE 0 END) as products_below_min
                ')
                ->first();

            return [
                'total_products' => $stats->total_products ?? 0,
                'total_quantity' => $stats->total_quantity ?? 0,
                'total_reserved' => $stats->total_reserved ?? 0,
                'total_available' => ($stats->total_quantity ?? 0) - ($stats->total_reserved ?? 0),
                'products_in_stock' => $stats->products_in_stock ?? 0,
                'products_out_of_stock' => $stats->products_out_of_stock ?? 0,
                'products_below_min' => $stats->products_below_min ?? 0,
            ];
        });
    }

    /**
     * ✅ المنتجات تحت الحد الأدنى
     */
    public function getProductsBelowMinStock(int $warehouseId): \Illuminate\Support\Collection
    {
        return ProductWarehouse::where('warehouse_id', $warehouseId)
            ->whereRaw('quantity < min_stock')
            ->with('product:id,name,sku,code')
            ->get()
                ->map(function ($stock) {
                    return [
                        'product_id' => $stock->product_id,
                        'product_name' => $stock->product?->name ?? 'غير معروف',
                        'sku' => $stock->product?->sku ?? '',
                        'current_quantity' => $stock->quantity,
                        'min_stock' => $stock->min_stock ?? 0,
                        'shortage' => ($stock->min_stock ?? 0) - $stock->quantity,
                    ];
                });
    }

    /**
     * ✅ مسح الكاش
     */
    public function clearCache(int $warehouseId): void
    {
        Cache::forget("warehouse_stock_{$warehouseId}");
        Cache::forget("warehouse_stats_{$warehouseId}");
        Cache::forget("warehouse_data_{$warehouseId}");
    }

    /**
     * ✅ مسح كاش عدة مخازن
     */
    public function clearMultipleCache(array $warehouseIds): void
    {
        foreach ($warehouseIds as $id) {
            $this->clearCache($id);
        }
    }
}