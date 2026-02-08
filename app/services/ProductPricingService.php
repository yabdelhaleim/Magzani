<?php
// app/Services/ProductPricingService.php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBaseUnit;
use App\Models\ProductSellingUnit;
use App\Models\PriceChangeHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * 🎯 خدمة إدارة أسعار المنتجات - النظام الجديد
 */
class ProductPricingService
{
    private const CHUNK_SIZE = 500;
    private const CACHE_TTL = 300;

    /**
     * 🔍 جلب التصنيفات حسب الوحدة الأساسية
     */
    public function getCategoriesByBaseUnit(string $baseUnit): array
    {
        try {
            Log::info('🔍 جلب التصنيفات', ['base_unit' => $baseUnit]);

            $cacheKey = "categories_by_unit_{$baseUnit}";
            
            $categories = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($baseUnit) {
                return DB::table('product_base_units as pbu')
                    ->join('products as p', 'p.id', '=', 'pbu.product_id')
                    ->where('pbu.base_unit_code', $baseUnit)
                    ->where('pbu.is_active', true)
                    ->where('p.is_active', true)
                    ->whereNotNull('p.category')
                    ->where('p.category', '!=', '')
                    ->distinct()
                    ->orderBy('p.category')
                    ->pluck('p.category');
            });

            if ($categories->isEmpty()) {
                return [
                    'success' => false,
                    'message' => "لا توجد منتجات بالوحدة: {$baseUnit}",
                    'categories' => [],
                ];
            }

            return [
                'success' => true,
                'categories' => $categories->toArray(),
                'count' => $categories->count(),
            ];

        } catch (\Exception $e) {
            Log::error('❌ فشل جلب التصنيفات', [
                'unit' => $baseUnit,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
                'categories' => [],
            ];
        }
    }

    /**
     * 📦 جلب المنتجات حسب الوحدة والتصنيف
     */
    public function getProductsByUnitAndCategory(string $baseUnit, string $category): array
    {
        try {
            Log::info('📦 جلب المنتجات', [
                'base_unit' => $baseUnit,
                'category' => $category
            ]);

            $cacheKey = "products_pricing_{$baseUnit}_{$category}";
            
            $products = Cache::remember($cacheKey, self::CACHE_TTL, function() use ($baseUnit, $category) {
                return $this->fetchProducts($baseUnit, $category);
            });

            if (empty($products)) {
                return [
                    'success' => false,
                    'message' => 'لا توجد منتجات',
                    'products' => [],
                ];
            }

            return [
                'success' => true,
                'products' => $products,
                'statistics' => $this->calculateStatistics($products),
            ];

        } catch (\Exception $e) {
            Log::error('❌ فشل جلب المنتجات', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
                'products' => [],
            ];
        }
    }

    /**
     * 🚀 جلب المنتجات محسّن
     */
    protected function fetchProducts(string $baseUnit, string $category): array
    {
        try {
            $results = DB::table('products as p')
                ->join('product_base_units as pbu', 'p.id', '=', 'pbu.product_id')
                ->where('p.category', $category)
                ->where('p.is_active', true)
                ->where('pbu.base_unit_code', $baseUnit)
                ->where('pbu.is_active', true)
                ->select([
                    'p.id',
                    'p.name',
                    'p.code',
                    'p.category',
                    'pbu.base_unit_code',
                    'pbu.base_unit_label',
                    'pbu.base_purchase_price',
                    'pbu.base_selling_price',
                    'pbu.profit_margin',
                ])
                ->get();

            return $results->map(function($row) {
                $profit = $row->base_selling_price - $row->base_purchase_price;

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'code' => $row->code,
                    'category' => $row->category,
                    'base_unit_code' => $row->base_unit_code,
                    'base_unit_label' => $row->base_unit_label,
                    'base_purchase_price' => round((float)$row->base_purchase_price, 2),
                    'base_selling_price' => round((float)$row->base_selling_price, 2),
                    'current_profit' => round($profit, 2),
                    'profit_margin' => round((float)$row->profit_margin, 2),
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('خطأ في جلب المنتجات', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 📊 حساب الإحصائيات
     */
    protected function calculateStatistics(array $products): array
    {
        if (empty($products)) {
            return [
                'total_count' => 0,
                'avg_purchase_price' => 0,
                'avg_selling_price' => 0,
                'avg_profit' => 0,
            ];
        }

        $totalCount = count($products);
        $totalPurchase = array_sum(array_column($products, 'base_purchase_price'));
        $totalSelling = array_sum(array_column($products, 'base_selling_price'));
        $totalProfit = array_sum(array_column($products, 'current_profit'));

        return [
            'total_count' => $totalCount,
            'avg_purchase_price' => round($totalPurchase / $totalCount, 2),
            'avg_selling_price' => round($totalSelling / $totalCount, 2),
            'avg_profit' => round($totalProfit / $totalCount, 2),
            'total_value' => round($totalSelling, 2),
        ];
    }

    /**
     * 💾 تطبيق التحديث الجماعي
     */
    public function applyBulkPriceUpdate(
        string $baseUnit,
        string $category,
        float $purchasePrice,
        float $profitValue,
        string $profitType,
        array $selectedProductIds,
        ?string $changeReason = null
    ): array {
        
        if (empty($selectedProductIds)) {
            throw new RuntimeException('لم يتم تحديد أي منتجات');
        }

        // ✅ حساب الربح وسعر البيع
        $profit = $this->calculateProfit($purchasePrice, $profitValue, $profitType);
        $sellingPrice = round($purchasePrice + $profit, 2);

        return DB::transaction(function () use (
            $baseUnit,
            $category,
            $purchasePrice,
            $sellingPrice,
            $profit, // ✅ تمرير المتغير
            $profitValue,
            $profitType,
            $selectedProductIds,
            $changeReason
        ) {
            $startTime = microtime(true);
            
            Log::info('🚀 بدء التحديث الجماعي', [
                'total_products' => count($selectedProductIds),
                'category' => $category,
            ]);

            $chunks = array_chunk($selectedProductIds, self::CHUNK_SIZE);
            $updatedCount = 0;

            foreach ($chunks as $index => $chunk) {
                $chunkUpdated = $this->processUpdateChunk(
                    $chunk,
                    $category,
                    $baseUnit,
                    $purchasePrice,
                    $sellingPrice,
                    $profit, // ✅ تمرير المتغير
                    $profitValue,
                    $profitType,
                    $changeReason
                );
                
                $updatedCount += $chunkUpdated;
            }

            $this->clearPricingCache($baseUnit, $category);

            $executionTime = round(microtime(true) - $startTime, 2);

            return [
                'success' => true,
                'message' => "✅ تم تحديث {$updatedCount} منتج بنجاح",
                'data' => [
                    'updated_count' => $updatedCount,
                    'execution_time' => $executionTime,
                    'new_prices' => [
                        'purchase' => $purchasePrice,
                        'selling' => $sellingPrice,
                        'profit' => round($profit, 2),
                        'profit_percentage' => $purchasePrice > 0 ? round(($profit / $purchasePrice) * 100, 2) : 0,
                    ],
                ],
            ];
        });
    }

    /**
     * 🔄 معالجة Chunk واحد
     */
    protected function processUpdateChunk(
        array $productIds,
        string $category,
        string $baseUnit,
        float $purchasePrice,
        float $sellingPrice,
        float $profit, // ✅ إضافة المتغير
        float $profitValue,
        string $profitType,
        ?string $changeReason
    ): int {
        $updatedCount = 0;
        $now = now();
        $userId = auth()->id();

        foreach ($productIds as $productId) {
            // جلب الوحدة الأساسية للمنتج
            $baseUnitRecord = ProductBaseUnit::where('product_id', $productId)
                ->where('is_active', true)
                ->first();

            if (!$baseUnitRecord) {
                continue;
            }

            // حساب نسبة التغيير
            $diffPercentage = 0;
            if ($baseUnitRecord->base_selling_price > 0) {
                $diffPercentage = (($sellingPrice - $baseUnitRecord->base_selling_price) / $baseUnitRecord->base_selling_price) * 100;
            }

            // حفظ السجل التاريخي
            PriceChangeHistory::create([
                'product_id' => $productId,
                'base_unit_id' => $baseUnitRecord->id,
                'old_base_purchase_price' => $baseUnitRecord->base_purchase_price,
                'new_base_purchase_price' => $purchasePrice,
                'old_base_selling_price' => $baseUnitRecord->base_selling_price,
                'new_base_selling_price' => $sellingPrice,
                'diff_percentage' => round($diffPercentage, 2),
                'change_reason' => $changeReason ?? 'تحديث جماعي',
                'changed_by' => $userId,
                'changed_at' => $now,
                'affected_selling_units' => 0, // سيتم تحديثه بواسطة Observer
                'selling_units_updated' => false, // سيتم تحديثه بواسطة Observer
            ]);

            // تحديث الوحدة الأساسية
            $baseUnitRecord->update([
                'base_purchase_price' => $purchasePrice,
                'base_selling_price' => $sellingPrice,
                'profit_margin' => $profitValue,
                'updated_by' => $userId,
            ]);

            // تحديث المنتج نفسه
            Product::where('id', $productId)->update([
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'profit_margin' => $profitValue,
                'updated_at' => $now,
            ]);

            // 🔥 التحديث التلقائي لوحدات البيع سيحصل عبر Observer

            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * 💰 حساب الربح
     */
    protected function calculateProfit(float $purchasePrice, float $profitValue, string $profitType): float
    {
        if ($profitType === 'percentage') {
            return round(($purchasePrice * $profitValue) / 100, 2);
        }
        
        return round($profitValue, 2);
    }

    /**
     * 🗑️ مسح الكاش
     */
    protected function clearPricingCache(string $baseUnit, string $category): void
    {
        Cache::forget("products_pricing_{$baseUnit}_{$category}");
        Cache::forget("categories_by_unit_{$baseUnit}");
    }
}