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
                    'pbu.id as base_unit_id', // 🔥 إضافة base_unit_id
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
                    'base_unit_id' => $row->base_unit_id, // 🔥 إضافة base_unit_id
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

        $profit = $this->calculateProfit($purchasePrice, $profitValue, $profitType);
        $sellingPrice = round($purchasePrice + $profit, 2);

        return DB::transaction(function () use (
            $baseUnit,
            $category,
            $purchasePrice,
            $sellingPrice,
            $profit,
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

            foreach ($chunks as $chunk) {
                $chunkUpdated = $this->processUpdateChunk(
                    $chunk,
                    $category,
                    $baseUnit,
                    $purchasePrice,
                    $sellingPrice,
                    $profit,
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
        float $profit,
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
                Log::warning('⚠️ لم يتم العثور على وحدة أساسية', ['product_id' => $productId]);
                continue;
            }

            // حساب نسبة التغيير
            $diffPercentage = 0;
            if ($baseUnitRecord->base_selling_price > 0) {
                $diffPercentage = (($sellingPrice - $baseUnitRecord->base_selling_price) / $baseUnitRecord->base_selling_price) * 100;
            }

            // 🔥 حفظ السجل التاريخي قبل التحديث
            $historyRecord = PriceChangeHistory::create([
                'product_id' => $productId,
                'base_unit_id' => $baseUnitRecord->id,
                'old_base_purchase_price' => $baseUnitRecord->base_purchase_price,
                'new_base_purchase_price' => $purchasePrice,
                'old_base_selling_price' => $baseUnitRecord->base_selling_price,
                'new_base_selling_price' => $sellingPrice,
                'diff_percentage' => round($diffPercentage, 2),
                'change_reason' => $changeReason ?? 'تحديث جماعي للأسعار',
                'changed_by' => $userId,
                'changed_at' => $now,
                'affected_selling_units' => 0,
                'selling_units_updated' => false,
            ]);

            // 🔥 تحديث الوحدة الأساسية
            $baseUnitRecord->update([
                'base_purchase_price' => $purchasePrice,
                'base_selling_price' => $sellingPrice,
                'profit_margin' => $profitValue,
                'updated_by' => $userId,
            ]);

            // 🔥 تحديث المنتج نفسه
            Product::where('id', $productId)->update([
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'profit_margin' => $profitValue,
                'updated_at' => $now,
            ]);

            // 🔥 التأكد من وجود selling unit للوحدة الأساسية
            $this->ensureBaseSellingUnitExists($baseUnitRecord);

            // 🔥 تحديث عدد الوحدات المتأثرة في السجل التاريخي
            $affectedCount = ProductSellingUnit::where('base_unit_id', $baseUnitRecord->id)
                ->where('auto_calculate_price', true)
                ->where('is_active', true)
                ->count();

            $historyRecord->update([
                'affected_selling_units' => $affectedCount,
                'selling_units_updated' => true,
            ]);

            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * 🔥 التأكد من وجود Selling Unit للوحدة الأساسية
     */
    protected function ensureBaseSellingUnitExists(ProductBaseUnit $baseUnit): void
    {
        // التحقق من وجود selling unit للوحدة الأساسية
        $existingBaseUnit = ProductSellingUnit::where('product_id', $baseUnit->product_id)
            ->where('base_unit_id', $baseUnit->id)
            ->where('is_base', true)
            ->first();

        if (!$existingBaseUnit) {
            // إنشاء selling unit للوحدة الأساسية
            ProductSellingUnit::create([
                'product_id' => $baseUnit->product_id,
                'base_unit_id' => $baseUnit->id, // 🔥 المفتاح المهم
                'unit_name' => $baseUnit->base_unit_label,
                'unit_code' => $baseUnit->base_unit_code,
                'unit_label' => $baseUnit->base_unit_label,
                'conversion_factor' => 1,
                'quantity_in_base_unit' => 1,
                'unit_purchase_price' => $baseUnit->base_purchase_price,
                'unit_selling_price' => $baseUnit->base_selling_price,
                'auto_calculate_price' => true,
                'is_base' => true, // 🔥 وحدة أساسية
                'is_default' => true,
                'is_active' => true,
                'display_order' => 0,
                'created_by' => auth()->id(),
            ]);

            Log::info('✅ تم إنشاء selling unit للوحدة الأساسية', [
                'product_id' => $baseUnit->product_id,
                'base_unit_id' => $baseUnit->id,
            ]);
        }
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
        Cache::tags(['products', 'pricing'])->flush();
    }
}