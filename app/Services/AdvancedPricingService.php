<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBasePricing;
use App\Models\ProductPriceHistory;
use App\Traits\UnitsManagement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * 🎯 AdvancedPricingService - النسخة المحسّنة + حل مشكلة null
 */
class AdvancedPricingService
{
    use UnitsManagement;

    private const CHUNK_SIZE = 500; // معالجة 500 منتج في المرة
    private const CACHE_TTL = 300; // 5 دقائق

    /**
     * 🔍 جلب التصنيفات حسب الوحدة الأساسية (محسّن)
     */
    public function getCategoriesByBaseUnit(string $baseUnit): array
    {
        try {
            if (!$this->isValidUnit($baseUnit)) {
                return [
                    'success' => false,
                    'message' => 'الوحدة المحددة غير صحيحة',
                    'categories' => [],
                ];
            }

            Log::info('🔍 Searching for categories', ['base_unit' => $baseUnit]);

            $cacheKey = "categories_by_unit_{$baseUnit}";
            
            $categories = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($baseUnit) {
                // 🎯 استراتيجية 1: من product_base_pricing
                $categories = $this->getCategoriesFromBasePricing($baseUnit);

                // 🎯 استراتيجية 2: Fallback
                if ($categories->isEmpty()) {
                    Log::info('⚠️ No categories in basePricing, using fallback');
                    $categories = $this->getCategoriesFromProducts($baseUnit);
                }

                return $categories;
            });

            if ($categories->isEmpty()) {
                return [
                    'success' => false,
                    'message' => "لا توجد منتجات مسجلة بالوحدة: {$this->getUnitLabel($baseUnit)}",
                    'categories' => [],
                    'available_units' => $this->getAvailableUnitsInfo(),
                ];
            }

            return [
                'success' => true,
                'categories' => $categories,
                'unit_info' => [
                    'code' => $baseUnit,
                    'label' => $this->getUnitLabel($baseUnit),
                    'icon' => $this->getUnitIcon($baseUnit),
                ],
                'count' => $categories->count(),
            ];

        } catch (\Exception $e) {
            Log::error('❌ getCategoriesByBaseUnit failed', [
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
     * 📦 جلب التصنيفات من product_base_pricing
     */
    protected function getCategoriesFromBasePricing(string $baseUnit): Collection
    {
        try {
            return DB::table('product_base_pricing')
                ->join('products', 'products.id', '=', 'product_base_pricing.product_id')
                ->where('product_base_pricing.base_unit', $baseUnit)
                ->where('product_base_pricing.is_active', true)
                ->where('products.is_active', true)
                ->whereNotNull('products.category')
                ->where('products.category', '!=', '')
                ->distinct()
                ->orderBy('products.category')
                ->pluck('products.category');

        } catch (\Exception $e) {
            Log::error('Error in getCategoriesFromBasePricing', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * 📦 جلب التصنيفات من products (Fallback)
     */
    protected function getCategoriesFromProducts(string $baseUnit): Collection
    {
        try {
            return Product::where('base_unit', $baseUnit)
                ->where('is_active', true)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');

        } catch (\Exception $e) {
            Log::error('Error in getCategoriesFromProducts', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * 🔍 معلومات الوحدات المتاحة
     */
    protected function getAvailableUnitsInfo(): array
    {
        try {
            $unitsFromPricing = DB::table('product_base_pricing')
                ->select('base_unit', DB::raw('COUNT(*) as count'))
                ->where('is_active', true)
                ->groupBy('base_unit')
                ->pluck('count', 'base_unit')
                ->toArray();

            $unitsFromProducts = DB::table('products')
                ->select('base_unit', DB::raw('COUNT(*) as count'))
                ->where('is_active', true)
                ->whereNotNull('base_unit')
                ->groupBy('base_unit')
                ->pluck('count', 'base_unit')
                ->toArray();

            return [
                'from_base_pricing' => $unitsFromPricing,
                'from_products' => $unitsFromProducts,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting available units', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 📦 جلب المنتجات حسب الوحدة والتصنيف (محسّن للداتا الكبيرة)
     */
    public function getProductsByUnitAndCategory(string $baseUnit, string $category): array
    {
        try {
            Log::info('🔍 Fetching products', [
                'base_unit' => $baseUnit,
                'category' => $category
            ]);

            $cacheKey = "products_pricing_{$baseUnit}_{$category}";
            
            $products = Cache::remember($cacheKey, self::CACHE_TTL, function() use ($baseUnit, $category) {
                // ✅ استخدام Raw Query للأداء - أسرع من Eloquent
                return $this->fetchProductsOptimized($baseUnit, $category);
            });

            if (empty($products)) {
                return [
                    'success' => false,
                    'message' => 'لا توجد منتجات بهذه الوحدة والتصنيف',
                    'products' => [],
                ];
            }

            $statistics = $this->calculateStatistics($products);

            return [
                'success' => true,
                'products' => $products,
                'statistics' => $statistics,
                'filters' => [
                    'base_unit' => $baseUnit,
                    'base_unit_label' => $this->getUnitLabel($baseUnit),
                    'category' => $category,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('❌ getProductsByUnitAndCategory failed', [
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
     * 🚀 جلب المنتجات محسّن - Raw Query للسرعة
     */
    protected function fetchProductsOptimized(string $baseUnit, string $category): array
    {
        try {
            // ✅ استعلام واحد محسّن بـ JOIN
            $results = DB::table('products as p')
                ->leftJoin('product_base_pricing as pbp', function($join) {
                    $join->on('p.id', '=', 'pbp.product_id')
                         ->where('pbp.is_active', true);
                })
                ->where('p.category', $category)
                ->where('p.is_active', true)
                ->where(function($query) use ($baseUnit) {
                    $query->where('pbp.base_unit', $baseUnit)
                          ->orWhere('p.base_unit', $baseUnit);
                })
                ->select([
                    'p.id',
                    'p.name',
                    'p.sku',
                    'p.category',
                    'p.base_unit as product_base_unit',
                    'p.base_unit_label',
                    'p.purchase_price as product_purchase_price',
                    'p.selling_price as product_selling_price',
                    'pbp.base_unit as pricing_base_unit',
                    'pbp.base_purchase_price',
                    'pbp.base_selling_price',
                ])
                ->get();

            return $results->map(function($row) use ($baseUnit) {
                // ✅ استخدام التسعير من BasePricing أو Products
                $purchasePrice = $row->base_purchase_price ?? $row->product_purchase_price ?? 0;
                $sellingPrice = $row->base_selling_price ?? $row->product_selling_price ?? 0;
                $baseUnitUsed = $row->pricing_base_unit ?? $row->product_base_unit ?? $baseUnit;

                $profit = $sellingPrice - $purchasePrice;
                $profitPercentage = $purchasePrice > 0 ? ($profit / $purchasePrice) * 100 : 0;

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'sku' => $row->sku,
                    'category' => $row->category,
                    'base_unit' => $baseUnitUsed,
                    'base_unit_label' => $row->base_unit_label ?? $this->getUnitLabel($baseUnitUsed),
                    'base_purchase_price' => round((float)$purchasePrice, 2),
                    'base_selling_price' => round((float)$sellingPrice, 2),
                    'current_profit' => round($profit, 2),
                    'current_profit_percentage' => round($profitPercentage, 2),
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Error in fetchProductsOptimized', ['error' => $e->getMessage()]);
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
                'avg_profit_percentage' => 0,
                'total_value' => 0,
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
            'avg_profit_percentage' => $totalPurchase > 0 ? round(($totalProfit / $totalPurchase) * 100, 2) : 0,
            'total_value' => round($totalSelling, 2),
            'min_price' => round(min(array_column($products, 'base_purchase_price')), 2),
            'max_price' => round(max(array_column($products, 'base_selling_price')), 2),
        ];
    }

    /**
     * 💾 تطبيق التحديث الجماعي - محسّن للـ 5000+ منتج
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

        if (count($selectedProductIds) > 10000) {
            throw new RuntimeException('لا يمكن تحديث أكثر من 10,000 منتج دفعة واحدة');
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
            
            Log::info('🚀 Starting bulk update', [
                'total_products' => count($selectedProductIds),
                'category' => $category,
                'base_unit' => $baseUnit,
            ]);

            // ✅ معالجة بالـ Chunks
            $chunks = array_chunk($selectedProductIds, self::CHUNK_SIZE);
            $updatedCount = 0;
            $errors = [];

            foreach ($chunks as $index => $chunk) {
                try {
                    $chunkUpdated = $this->processUpdateChunk(
                        $chunk,
                        $category,
                        $baseUnit,
                        $purchasePrice,
                        $sellingPrice,
                        $profitValue,
                        $profitType,
                        $changeReason
                    );
                    
                    $updatedCount += $chunkUpdated;
                    
                    Log::info("✅ Chunk " . ($index + 1) . " processed", [
                        'chunk_size' => count($chunk),
                        'updated' => $chunkUpdated,
                        'total_updated' => $updatedCount,
                    ]);

                } catch (\Exception $e) {
                    $errors[] = "خطأ في Chunk " . ($index + 1) . ": " . $e->getMessage();
                    Log::error("Chunk processing failed", [
                        'chunk_index' => $index,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // ✅ مسح الـ Cache
            $this->clearPricingCache($baseUnit, $category);

            $executionTime = round(microtime(true) - $startTime, 2);

            Log::info('✅ Bulk update completed', [
                'updated_count' => $updatedCount,
                'execution_time' => $executionTime . 's',
                'errors' => count($errors),
            ]);

            $unitLabel = $this->getUnitLabel($baseUnit);
            $unitIcon = $this->getUnitIcon($baseUnit);
            
            $message = "{$unitIcon} تم تحديث {$updatedCount} منتج بنجاح";
            if (!empty($errors)) {
                $message .= "\n⚠️ مع " . count($errors) . " تحذير";
            }
            $message .= "\n📂 التصنيف: {$category}";
            $message .= "\n📦 الوحدة: {$unitLabel}";
            $message .= "\n💰 السعر الجديد: " . number_format($sellingPrice, 2) . " ج.م";
            $message .= "\n⏱️ وقت التنفيذ: {$executionTime}s";

            return [
                'success' => true,
                'message' => $message,
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_count' => count($selectedProductIds),
                    'errors_count' => count($errors),
                    'errors' => $errors,
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
     * 🔄 معالجة Chunk واحد (500 منتج)
     */
    protected function processUpdateChunk(
        array $productIds,
        string $category,
        string $baseUnit,
        float $purchasePrice,
        float $sellingPrice,
        float $profitValue,
        string $profitType,
        ?string $changeReason
    ): int {
        $updatedCount = 0;
        $now = now();
        $userId = auth()->id();

        // ✅ جلب المنتجات بالبيانات الحالية
        $products = DB::table('products as p')
            ->leftJoin('product_base_pricing as pbp', function($join) {
                $join->on('p.id', '=', 'pbp.product_id')
                     ->where('pbp.is_active', true);
            })
            ->whereIn('p.id', $productIds)
            ->where('p.category', $category)
            ->where('p.is_active', true)
            ->select([
                'p.id',
                'p.name',
                'p.purchase_price as old_purchase',
                'p.selling_price as old_selling',
                'pbp.id as pricing_id',
                'pbp.base_purchase_price as old_base_purchase',
                'pbp.base_selling_price as old_base_selling',
            ])
            ->get();

        // ✅ Arrays للـ Bulk Operations
        $priceHistoryData = [];
        $pricingUpdates = [];
        $pricingInserts = [];
        $productUpdates = [];

        foreach ($products as $product) {
            // تحضير بيانات التاريخ
            $oldPurchase = $product->old_base_purchase ?? $product->old_purchase ?? 0;
            $oldSelling = $product->old_base_selling ?? $product->old_selling ?? 0;

            $priceHistoryData[] = [
                'product_id' => $product->id,
                'base_unit' => $baseUnit,
                'old_purchase_price' => $oldPurchase,
                'new_purchase_price' => $purchasePrice,
                'old_selling_price' => $oldSelling,
                'new_selling_price' => $sellingPrice,
                'change_reason' => $changeReason ?? 'تحديث جماعي ذكي',
                'changed_by' => $userId,
                'changed_at' => $now,
            ];

            if ($product->pricing_id) {
                // Update existing
                $pricingUpdates[] = $product->pricing_id;
            } else {
                // Insert new
                $pricingInserts[] = [
                    'product_id' => $product->id,
                    'base_unit' => $baseUnit,
                    'base_purchase_price' => $purchasePrice,
                    'base_selling_price' => $sellingPrice,
                    'profit_type' => $profitType,
                    'profit_value' => $profitValue,
                    'is_active' => true,
                    'is_current' => true,
                    'effective_from' => $now,
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $productUpdates[] = $product->id;
            $updatedCount++;
        }

        // ✅ Bulk Insert - Price History
        if (!empty($priceHistoryData)) {
            DB::table('product_price_history')->insert($priceHistoryData);
        }

        // ✅ Bulk Update - Existing Pricing
        if (!empty($pricingUpdates)) {
            DB::table('product_base_pricing')
                ->whereIn('id', $pricingUpdates)
                ->update([
                    'base_purchase_price' => $purchasePrice,
                    'base_selling_price' => $sellingPrice,
                    'profit_type' => $profitType,
                    'profit_value' => $profitValue,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);
        }

        // ✅ Bulk Insert - New Pricing
        if (!empty($pricingInserts)) {
            DB::table('product_base_pricing')->insert($pricingInserts);
        }

        // ✅ Bulk Update - Products Table
        if (!empty($productUpdates)) {
            DB::table('products')
                ->whereIn('id', $productUpdates)
                ->update([
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'updated_at' => $now,
                ]);
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
     * 📊 معاينة التحديث الجماعي
     */
    public function previewBulkUpdate(
        string $baseUnit,
        string $category,
        float $purchasePrice,
        float $profitValue,
        string $profitType,
        array $selectedProductIds
    ): array {
        
        try {
            $profit = $this->calculateProfit($purchasePrice, $profitValue, $profitType);
            $newSellingPrice = round($purchasePrice + $profit, 2);
            $newProfitPercentage = $purchasePrice > 0 ? round(($profit / $purchasePrice) * 100, 2) : 0;

            // ✅ استعلام محسّن
            $products = DB::table('products as p')
                ->leftJoin('product_base_pricing as pbp', function($join) {
                    $join->on('p.id', '=', 'pbp.product_id')
                         ->where('pbp.is_active', true);
                })
                ->whereIn('p.id', $selectedProductIds)
                ->select([
                    'p.id',
                    'p.name',
                    'pbp.base_purchase_price',
                    'pbp.base_selling_price',
                    'p.purchase_price',
                    'p.selling_price',
                ])
                ->get()
                ->map(function($row) use ($purchasePrice, $newSellingPrice, $profit, $newProfitPercentage) {
                    $oldPurchasePrice = $row->base_purchase_price ?? $row->purchase_price ?? 0;
                    $oldSellingPrice = $row->base_selling_price ?? $row->selling_price ?? 0;

                    $oldProfit = $oldSellingPrice - $oldPurchasePrice;
                    $oldProfitPercentage = $oldPurchasePrice > 0 ? round(($oldProfit / $oldPurchasePrice) * 100, 2) : 0;

                    return [
                        'id' => $row->id,
                        'name' => $row->name,
                        'old_purchase_price' => round((float)$oldPurchasePrice, 2),
                        'old_selling_price' => round((float)$oldSellingPrice, 2),
                        'old_profit' => round($oldProfit, 2),
                        'old_profit_percentage' => $oldProfitPercentage,
                        'new_purchase_price' => $purchasePrice,
                        'new_selling_price' => $newSellingPrice,
                        'new_profit' => round($profit, 2),
                        'new_profit_percentage' => $newProfitPercentage,
                        'price_change' => round($newSellingPrice - $oldSellingPrice, 2),
                        'profit_change' => round($profit - $oldProfit, 2),
                    ];
                });

            $statistics = [
                'total_products' => $products->count(),
                'total_old_value' => round($products->sum('old_selling_price'), 2),
                'total_new_value' => round($products->sum('new_selling_price'), 2),
                'total_difference' => round($products->sum('price_change'), 2),
                'avg_profit_increase' => round($products->avg('profit_change'), 2),
            ];

            return [
                'success' => true,
                'preview' => $products->toArray(),
                'statistics' => $statistics,
                'pricing_info' => [
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $newSellingPrice,
                    'profit' => round($profit, 2),
                    'profit_percentage' => $newProfitPercentage,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Preview failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'حدث خطأ في المعاينة: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 🗑️ مسح الكاش
     */
protected function clearPricingCache(string $baseUnit, string $category): void
{
    try {
        // ✅ مسح الكاش الخاص بالوحدة والتصنيف
        Cache::forget("products_pricing_{$baseUnit}_{$category}");
        Cache::forget("categories_by_unit_{$baseUnit}");
        
        // ✅ مسح كل كاش التسعير (Optional)
        // إذا عايز تمسح كل حاجة متعلقة بالتسعير
        $this->clearAllPricingCache();
        
        Log::info('✅ Cache cleared', [
            'base_unit' => $baseUnit,
            'category' => $category
        ]);
        
    } catch (\Exception $e) {
        Log::warning('⚠️ Cache clear failed', [
            'error' => $e->getMessage()
        ]);
    }
}
protected function clearAllPricingCache(): void
{
    try {
        // ✅ جمع كل مفاتيح الكاش المتعلقة بالتسعير
        $units = ['piece', 'kg', 'liter', 'meter', 'box', 'carton']; // حط الوحدات اللي عندك
        
        foreach ($units as $unit) {
            Cache::forget("categories_by_unit_{$unit}");
            
            // مسح كاش كل التصنيفات لكل وحدة
            // (اختياري - ممكن تسيبه لو مش محتاج)
        }
        
    } catch (\Exception $e) {
        // Silent fail
        Log::debug('Cache pattern clear skipped', ['error' => $e->getMessage()]);
    }
}

}