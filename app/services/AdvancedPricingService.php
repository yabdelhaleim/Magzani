<?php

namespace App\Services;

use App\Traits\UnitsManagement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * 🎯 AdvancedPricingService
 * تشتغل مع جدول product_base_units
 */
class AdvancedPricingService
{
    use UnitsManagement;

    private const CHUNK_SIZE = 500;
    private const CACHE_TTL  = 300;

    // ================================================================
    // 🔍 جلب التصنيفات حسب الوحدة الأساسية
    // ================================================================
    public function getCategoriesByBaseUnit(string $baseUnit): array
    {
        try {
            Log::info('🔍 getCategoriesByBaseUnit', ['base_unit' => $baseUnit]);

            $cacheKey   = "categories_by_unit_{$baseUnit}";
            $categories = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($baseUnit) {
                return DB::table('product_base_units as pbu')
                    ->join('products as p', 'p.id', '=', 'pbu.product_id')
                    ->where('pbu.base_unit_code', $baseUnit)
                    ->where('pbu.is_active', 1)
                    ->where('p.is_active', 1)
                    ->whereNull('pbu.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNotNull('p.category')
                    ->where('p.category', '!=', '')
                    ->distinct()
                    ->orderBy('p.category')
                    ->pluck('p.category');
            });

            if ($categories->isEmpty()) {
                return [
                    'success'    => false,
                    'message'    => "لا توجد منتجات بالوحدة: {$this->getUnitLabel($baseUnit)}",
                    'categories' => [],
                ];
            }

            return [
                'success'    => true,
                'categories' => $categories,
                'unit_info'  => [
                    'code'  => $baseUnit,
                    'label' => $this->getUnitLabel($baseUnit),
                    'icon'  => $this->getUnitIcon($baseUnit),
                ],
                'count' => $categories->count(),
            ];

        } catch (\Exception $e) {
            Log::error('❌ getCategoriesByBaseUnit failed', [
                'unit'  => $baseUnit,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success'    => false,
                'message'    => 'حدث خطأ: ' . $e->getMessage(),
                'categories' => [],
            ];
        }
    }

    // ================================================================
    // 📦 جلب المنتجات حسب الوحدة والتصنيف
    // ================================================================
    public function getProductsByUnitAndCategory(string $baseUnit, string $category): array
    {
        try {
            Log::info('📦 getProductsByUnitAndCategory', [
                'base_unit' => $baseUnit,
                'category'  => $category,
            ]);

            $cacheKey = "products_pricing_{$baseUnit}_{$category}";
            $products = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($baseUnit, $category) {
                return $this->fetchProductsOptimized($baseUnit, $category);
            });

            if (empty($products)) {
                return [
                    'success'  => false,
                    'message'  => 'لا توجد منتجات بهذه الوحدة والتصنيف',
                    'products' => [],
                ];
            }

            return [
                'success'    => true,
                'products'   => $products,
                'statistics' => $this->calculateStatistics($products),
                'filters'    => [
                    'base_unit'       => $baseUnit,
                    'base_unit_label' => $this->getUnitLabel($baseUnit),
                    'category'        => $category,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('❌ getProductsByUnitAndCategory failed', [
                'base_unit' => $baseUnit,
                'category'  => $category,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success'  => false,
                'message'  => 'حدث خطأ: ' . $e->getMessage(),
                'products' => [],
            ];
        }
    }

    // ================================================================
    // 🚀 جلب المنتجات من product_base_units
    // ================================================================
    protected function fetchProductsOptimized(string $baseUnit, string $category): array
    {
        try {
            $results = DB::table('product_base_units as pbu')
                ->join('products as p', 'p.id', '=', 'pbu.product_id')
                ->where('p.category', $category)
                ->where('p.is_active', 1)
                ->where('pbu.base_unit_code', $baseUnit)
                ->where('pbu.is_active', 1)
                ->whereNull('pbu.deleted_at')
                ->whereNull('p.deleted_at')
                ->select([
                    'p.id',
                    'p.name',
                    'p.sku',
                    'p.code',
                    'p.category',
                    'pbu.id          as base_unit_id',
                    'pbu.base_unit_code',
                    'pbu.base_unit_label',
                    'pbu.base_purchase_price',
                    'pbu.base_selling_price',
                    'pbu.profit_margin',
                ])
                ->get();

            return $results->map(function ($row) {
                $purchase = (float) ($row->base_purchase_price ?? 0);
                $selling  = (float) ($row->base_selling_price  ?? 0);
                $profit   = $selling - $purchase;

                return [
                    'id'                        => $row->id,
                    'name'                      => $row->name,
                    'sku'                       => $row->sku,
                    'code'                      => $row->code,
                    'category'                  => $row->category,
                    'base_unit_id'              => $row->base_unit_id,
                    'base_unit_code'            => $row->base_unit_code,
                    'base_unit_label'           => $row->base_unit_label,
                    'base_purchase_price'       => round($purchase, 2),
                    'base_selling_price'        => round($selling,  2),
                    'current_profit'            => round($profit,   2),
                    'current_profit_percentage' => $purchase > 0
                        ? round(($profit / $purchase) * 100, 2)
                        : 0,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('❌ fetchProductsOptimized failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ================================================================
    // 📊 حساب الإحصائيات
    // ================================================================
    protected function calculateStatistics(array $products): array
    {
        if (empty($products)) {
            return [
                'total_count'           => 0,
                'avg_purchase_price'    => 0,
                'avg_selling_price'     => 0,
                'avg_profit'            => 0,
                'avg_profit_percentage' => 0,
                'total_value'           => 0,
                'min_price'             => 0,
                'max_price'             => 0,
            ];
        }

        $count    = count($products);
        $purchase = array_sum(array_column($products, 'base_purchase_price'));
        $selling  = array_sum(array_column($products, 'base_selling_price'));
        $profit   = array_sum(array_column($products, 'current_profit'));

        return [
            'total_count'           => $count,
            'avg_purchase_price'    => round($purchase / $count, 2),
            'avg_selling_price'     => round($selling  / $count, 2),
            'avg_profit'            => round($profit   / $count, 2),
            'avg_profit_percentage' => $purchase > 0
                ? round(($profit / $purchase) * 100, 2)
                : 0,
            'total_value' => round($selling, 2),
            'min_price'   => round(min(array_column($products, 'base_purchase_price')), 2),
            'max_price'   => round(max(array_column($products, 'base_selling_price')),  2),
        ];
    }

    // ================================================================
    // 💾 تطبيق التحديث الجماعي
    // ================================================================
    public function applyBulkPriceUpdate(
        string  $baseUnit,
        string  $category,
        float   $purchasePrice,
        float   $profitValue,
        string  $profitType,
        array   $selectedProductIds,
        ?string $changeReason = null
    ): array {

        if (empty($selectedProductIds)) {
            throw new RuntimeException('لم يتم تحديد أي منتجات');
        }

        if (count($selectedProductIds) > 10000) {
            throw new RuntimeException('الحد الأقصى 10,000 منتج دفعة واحدة');
        }

        $profit       = $this->calculateProfit($purchasePrice, $profitValue, $profitType);
        $sellingPrice = round($purchasePrice + $profit, 2);

        return DB::transaction(function () use (
            $baseUnit, $category,
            $purchasePrice, $sellingPrice, $profit,
            $profitValue, $profitType,
            $selectedProductIds, $changeReason
        ) {
            $start        = microtime(true);
            $updatedCount = 0;
            $errors       = [];

            Log::info('🚀 applyBulkPriceUpdate start', [
                'total'          => count($selectedProductIds),
                'category'       => $category,
                'base_unit'      => $baseUnit,
                'purchase_price' => $purchasePrice,
                'selling_price'  => $sellingPrice,
            ]);

            foreach (array_chunk($selectedProductIds, self::CHUNK_SIZE) as $i => $chunk) {
                try {
                    $updatedCount += $this->processUpdateChunk(
                        $chunk, $category, $baseUnit,
                        $purchasePrice, $sellingPrice,
                        $profitValue, $profitType, $changeReason
                    );

                    Log::info('✅ Chunk ' . ($i + 1) . ' done', [
                        'total_updated' => $updatedCount,
                    ]);

                } catch (\Exception $e) {
                    $errors[] = 'خطأ في Chunk ' . ($i + 1) . ': ' . $e->getMessage();
                    Log::error('❌ Chunk failed', [
                        'chunk' => $i + 1,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->clearPricingCache($baseUnit, $category);

            $time      = round(microtime(true) - $start, 2);
            $unitLabel = $this->getUnitLabel($baseUnit);
            $unitIcon  = $this->getUnitIcon($baseUnit);

            Log::info('✅ applyBulkPriceUpdate done', [
                'updated_count'  => $updatedCount,
                'execution_time' => $time . 's',
                'errors'         => count($errors),
            ]);

            $msg  = "{$unitIcon} تم تحديث {$updatedCount} منتج بنجاح";
            $msg .= !empty($errors) ? "\n⚠️ مع " . count($errors) . ' تحذير' : '';
            $msg .= "\n📂 التصنيف: {$category}";
            $msg .= "\n📦 الوحدة: {$unitLabel}";
            $msg .= "\n💰 سعر البيع الجديد: " . number_format($sellingPrice, 2) . ' ج.م';
            $msg .= "\n⏱️ وقت التنفيذ: {$time}s";

            return [
                'success' => true,
                'message' => $msg,
                'data'    => [
                    'updated_count'  => $updatedCount,
                    'total_count'    => count($selectedProductIds),
                    'errors_count'   => count($errors),
                    'errors'         => $errors,
                    'execution_time' => $time,
                    'new_prices'     => [
                        'purchase'          => $purchasePrice,
                        'selling'           => $sellingPrice,
                        'profit'            => round($profit, 2),
                        'profit_percentage' => $purchasePrice > 0
                            ? round(($profit / $purchasePrice) * 100, 2)
                            : 0,
                    ],
                ],
            ];
        });
    }

    // ================================================================
    // 🔄 معالجة Chunk - يحدث product_base_units + products
    // ================================================================
    protected function processUpdateChunk(
        array   $productIds,
        string  $category,
        string  $baseUnit,
        float   $purchasePrice,
        float   $sellingPrice,
        float   $profitValue,
        string  $profitType,
        ?string $changeReason
    ): int {
        $now    = now();
        $userId = auth()->id();

        // جلب البيانات الحالية
        $rows = DB::table('product_base_units as pbu')
            ->join('products as p', 'p.id', '=', 'pbu.product_id')
            ->whereIn('p.id', $productIds)
            ->where('p.category', $category)
            ->where('p.is_active', 1)
            ->where('pbu.base_unit_code', $baseUnit)
            ->where('pbu.is_active', 1)
            ->whereNull('pbu.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'p.id            as product_id',
                'p.purchase_price as old_product_purchase',
                'p.selling_price  as old_product_selling',
                'pbu.id           as base_unit_id',
                'pbu.base_purchase_price as old_base_purchase',
                'pbu.base_selling_price  as old_base_selling',
            ])
            ->get();

        if ($rows->isEmpty()) {
            Log::warning('⚠️ processUpdateChunk: no rows found', [
                'product_ids' => $productIds,
                'category'    => $category,
                'base_unit'   => $baseUnit,
            ]);
            return 0;
        }

        $historyRows    = [];
        $baseUnitIds    = [];
        $productIdsUpd  = [];

        foreach ($rows as $row) {
            $oldPurchase = (float) ($row->old_base_purchase   ?? $row->old_product_purchase ?? 0);
            $oldSelling  = (float) ($row->old_base_selling    ?? $row->old_product_selling  ?? 0);

            $diffPct = $oldSelling > 0
                ? round((($sellingPrice - $oldSelling) / $oldSelling) * 100, 2)
                : 0;

            $historyRows[] = [
                'product_id'              => $row->product_id,
                'base_unit_id'            => $row->base_unit_id,
                'old_base_purchase_price' => $oldPurchase,
                'new_base_purchase_price' => $purchasePrice,
                'old_base_selling_price'  => $oldSelling,
                'new_base_selling_price'  => $sellingPrice,
                'diff_percentage'         => $diffPct,
                'change_reason'           => $changeReason ?? 'تحديث جماعي ذكي',
                'changed_by'              => $userId,
                'changed_at'              => $now,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];

            $baseUnitIds[]   = $row->base_unit_id;
            $productIdsUpd[] = $row->product_id;
        }

        // ✅ 1. تحديث product_base_units
        DB::table('product_base_units')
            ->whereIn('id', $baseUnitIds)
            ->update([
                'base_purchase_price' => $purchasePrice,
                'base_selling_price'  => $sellingPrice,
                'profit_margin'       => $profitValue,
                'updated_by'          => $userId,
                'updated_at'          => $now,
            ]);

        // ✅ 2. تحديث products (عشان يظهر في قائمة المنتجات والمخزن)
        DB::table('products')
            ->whereIn('id', $productIdsUpd)
            ->update([
                'purchase_price' => $purchasePrice,
                'selling_price'  => $sellingPrice,
                'profit_margin'  => $profitValue,
                'updated_at'     => $now,
            ]);

        // ✅ 3. حفظ التاريخ في price_change_history
        if (!empty($historyRows)) {
            try {
                DB::table('price_change_history')->insert($historyRows);
            } catch (\Exception $e) {
                Log::warning('⚠️ Could not save price_change_history', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ✅ 4. تحديث product_selling_units (الوحدات الأساسية فقط)
        if (!empty($baseUnitIds)) {
            try {
                DB::table('product_selling_units')
                    ->whereIn('base_unit_id', $baseUnitIds)
                    ->where('is_base', 1)
                    ->where('is_active', 1)
                    ->update([
                        'unit_purchase_price' => $purchasePrice,
                        'unit_selling_price'  => $sellingPrice,
                        'updated_at'          => $now,
                    ]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Could not update product_selling_units', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return count($productIdsUpd);
    }

    // ================================================================
    // 💰 حساب الربح
    // ================================================================
    protected function calculateProfit(float $purchasePrice, float $profitValue, string $profitType): float
    {
        return $profitType === 'percentage'
            ? round(($purchasePrice * $profitValue) / 100, 2)
            : round($profitValue, 2);
    }

    // ================================================================
    // 📊 معاينة التحديث قبل التطبيق
    // ================================================================
    public function previewBulkUpdate(
        string $baseUnit,
        string $category,
        float  $purchasePrice,
        float  $profitValue,
        string $profitType,
        array  $selectedProductIds
    ): array {

        try {
            $profit       = $this->calculateProfit($purchasePrice, $profitValue, $profitType);
            $newSelling   = round($purchasePrice + $profit, 2);
            $newProfitPct = $purchasePrice > 0
                ? round(($profit / $purchasePrice) * 100, 2)
                : 0;

            $products = DB::table('product_base_units as pbu')
                ->join('products as p', 'p.id', '=', 'pbu.product_id')
                ->whereIn('p.id', $selectedProductIds)
                ->where('pbu.base_unit_code', $baseUnit)
                ->where('pbu.is_active', 1)
                ->whereNull('pbu.deleted_at')
                ->whereNull('p.deleted_at')
                ->select([
                    'p.id',
                    'p.name',
                    'pbu.base_purchase_price',
                    'pbu.base_selling_price',
                ])
                ->get()
                ->map(function ($row) use ($purchasePrice, $newSelling, $profit, $newProfitPct) {
                    $oldP  = (float) ($row->base_purchase_price ?? 0);
                    $oldS  = (float) ($row->base_selling_price  ?? 0);
                    $oldPr = $oldS - $oldP;

                    return [
                        'id'                    => $row->id,
                        'name'                  => $row->name,
                        'old_purchase_price'    => round($oldP,  2),
                        'old_selling_price'     => round($oldS,  2),
                        'old_profit'            => round($oldPr, 2),
                        'old_profit_percentage' => $oldP > 0
                            ? round(($oldPr / $oldP) * 100, 2)
                            : 0,
                        'new_purchase_price'    => $purchasePrice,
                        'new_selling_price'     => $newSelling,
                        'new_profit'            => round($profit, 2),
                        'new_profit_percentage' => $newProfitPct,
                        'price_change'          => round($newSelling - $oldS,  2),
                        'profit_change'         => round($profit    - $oldPr, 2),
                    ];
                });

            return [
                'success'      => true,
                'preview'      => $products->toArray(),
                'statistics'   => [
                    'total_products'      => $products->count(),
                    'total_old_value'     => round($products->sum('old_selling_price'), 2),
                    'total_new_value'     => round($products->sum('new_selling_price'), 2),
                    'total_difference'    => round($products->sum('price_change'),      2),
                    'avg_profit_increase' => round($products->avg('profit_change'),     2),
                ],
                'pricing_info' => [
                    'purchase_price'    => $purchasePrice,
                    'selling_price'     => $newSelling,
                    'profit'            => round($profit, 2),
                    'profit_percentage' => $newProfitPct,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('❌ previewBulkUpdate failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'حدث خطأ في المعاينة: ' . $e->getMessage(),
            ];
        }
    }

    // ================================================================
    // 🗑️ مسح الكاش
    // ================================================================
    protected function clearPricingCache(string $baseUnit, string $category): void
    {
        try {
            Cache::forget("products_pricing_{$baseUnit}_{$category}");
            Cache::forget("categories_by_unit_{$baseUnit}");
            $this->clearAllPricingCache();

            Log::info('✅ Cache cleared', [
                'base_unit' => $baseUnit,
                'category'  => $category,
            ]);

        } catch (\Exception $e) {
            Log::warning('⚠️ Cache clear failed', ['error' => $e->getMessage()]);
        }
    }

    protected function clearAllPricingCache(): void
    {
        try {
            // ✅ جلب الوحدات الفعلية من DB
            $units = DB::table('product_base_units')
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('base_unit_code')
                ->toArray();

            foreach ($units as $unit) {
                Cache::forget("categories_by_unit_{$unit}");
            }

        } catch (\Exception $e) {
            Log::debug('Cache clear skipped', ['error' => $e->getMessage()]);
        }
    }
}