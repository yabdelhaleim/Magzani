<?php

namespace App\Services;

use App\Models\ProductWarehouse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarehouseStockService
{
    /**
     * الحصول على جميع المنتجات مع كمياتها في مخزن معين
     */
    public function getWarehouseProductsWithStock(int $warehouseId): array
    {
        $cacheKey = "warehouse_products_stock_{$warehouseId}";
        
        return Cache::remember($cacheKey, 60, function () use ($warehouseId) {
            $stocks = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->with(['product' => function($query) {
                    $query->select('id', 'name', 'sku', 'barcode')->where('is_active', true);
                }])
                ->select('product_id', 'quantity', 'reserved_quantity', 'min_stock')
                ->get();

            $result = [];
            
            foreach ($stocks as $stock) {
                if (!$stock->product) continue; // تجاهل المنتجات غير النشطة
                
                $available = max(0, $stock->quantity - ($stock->reserved_quantity ?? 0));
                
                $result[$stock->product_id] = [
                    'id' => $stock->product_id,
                    'name' => $stock->product->name,
                    'sku' => $stock->product->sku,
                    'barcode' => $stock->product->barcode ?? '',
                    'quantity' => (float) $stock->quantity,
                    'reserved' => (float) ($stock->reserved_quantity ?? 0),
                    'available' => (float) $available,
                    'min_stock' => (float) ($stock->min_stock ?? 0),
                    'formatted' => number_format($available, 2),
                    'status' => $this->getStockStatus($available, $stock->min_stock)
                ];
            }
            
            return $result;
        });
    }

    /**
     * التحقق من توفر كمية (بدون cache - للعمليات الحرجة)
     */
    public function checkAvailability(int $warehouseId, int $productId, float $requiredQuantity): array
    {
        $stock = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first(['quantity', 'reserved_quantity']);
        
        if (!$stock) {
            return [
                'available' => false,
                'current_quantity' => 0,
                'required_quantity' => $requiredQuantity,
                'shortage' => $requiredQuantity,
                'message' => 'المنتج غير موجود في المخزن'
            ];
        }
        
        $available = max(0, $stock->quantity - ($stock->reserved_quantity ?? 0));
        $isAvailable = $available >= $requiredQuantity;
        
        return [
            'available' => $isAvailable,
            'current_quantity' => (float) $available,
            'required_quantity' => (float) $requiredQuantity,
            'shortage' => $isAvailable ? 0 : ($requiredQuantity - $available),
            'message' => $isAvailable ? 'الكمية متوفرة' : "ينقص " . number_format($requiredQuantity - $available, 2) . " وحدة"
        ];
    }

    /**
     * مسح الكاش
     */
    public function clearCache(int $warehouseId): void
    {
        Cache::forget("warehouse_products_stock_{$warehouseId}");
    }

    /**
     * مسح كاش متعدد
     */
    public function clearMultipleCache(array $warehouseIds): void
    {
        foreach ($warehouseIds as $warehouseId) {
            $this->clearCache($warehouseId);
        }
    }

    /**
     * تحديد حالة المخزون
     */
    private function getStockStatus(float $available, ?float $minStock = null): string
    {
        if ($available <= 0) {
            return 'out_of_stock';
        }
        
        if ($minStock && $available <= $minStock) {
            return 'low_stock';
        }
        
        if ($minStock && $available <= ($minStock * 1.5)) {
            return 'warning';
        }
        
        return 'in_stock';
    }
}