<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBasePricing;
use App\Models\ProductSellingUnit;
use App\Models\ProductPriceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * 🎯 خدمة التسعير المتقدم بنظام الوحدات
 * 
 * تتيح:
 * - تسعير المنتجات بالوحدة الأساسية (طن، كيلو، إلخ)
 * - حساب أسعار الوحدات الفرعية تلقائياً
 * - تتبع تاريخ الأسعار
 * - مقارنة الأسعار عبر الوقت
 */
class PriceUpdateService
{
    /**
     * 📋 إنشاء منتج جديد مع نظام التسعير الذكي
     */
    public function createProductWithPricing(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            
            // 1️⃣ إنشاء المنتج الأساسي
            $product = Product::create([
                'name' => $data['name'],
                'sku' => $data['sku'] ?? $this->generateSKU(),
                'code' => $data['code'] ?? $this->generateProductCode(),
                'barcode' => $data['barcode'] ?? null,
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2️⃣ إنشاء التسعير الأساسي
            $pricing = ProductBasePricing::create([
                'product_id' => $product->id,
                'base_unit' => $data['base_unit'], // مثلاً: ton
                'base_purchase_price' => $data['base_purchase_price'],
                'base_selling_price' => $data['base_selling_price'] ?? null,
                'profit_type' => $data['profit_type'] ?? 'fixed',
                'profit_value' => $data['profit_value'] ?? 0,
                'is_active' => true,
                'effective_from' => $data['effective_from'] ?? now(),
                'created_by' => auth()->id(),
            ]);

            // 3️⃣ إنشاء وحدات البيع المختلفة
            if (!empty($data['selling_units'])) {
                foreach ($data['selling_units'] as $unit) {
                    ProductSellingUnit::create([
                        'product_id' => $product->id,
                        'unit_name' => $unit['name'],
                        'unit_code' => $unit['code'] ?? null,
                        'quantity_in_base_unit' => $unit['quantity'], // مثلاً: 0.05 للشيكارة
                        'barcode' => $unit['barcode'] ?? null,
                        'is_default' => $unit['is_default'] ?? false,
                        'display_order' => $unit['order'] ?? 0,
                    ]);
                }
            } else {
                // إنشاء وحدات افتراضية
                ProductSellingUnit::createDefaultUnits($product->id, $data['base_unit']);
            }

            // 4️⃣ إضافة للمخزن إذا وُجد
            if (!empty($data['warehouse_id'])) {
                $product->warehouses()->attach($data['warehouse_id'], [
                    'quantity' => $data['initial_quantity'] ?? 0,
                    'min_stock' => $data['min_stock'] ?? 10,
                    'reserved_quantity' => 0,
                    'available_quantity' => $data['initial_quantity'] ?? 0,
                    'average_cost' => $pricing->base_purchase_price,
                ]);
            }

            return $product->fresh(['warehouses', 'basePricing', 'sellingUnits']);
        });
    }

    /**
     * 💰 تحديث السعر الأساسي (مع حفظ التاريخ)
     */
    public function updateBasePrice(
        int $productId,
        float $newPurchasePrice,
        float $newSellingPrice,
        string $changeReason = null
    ): ProductBasePricing {
        return DB::transaction(function () use ($productId, $newPurchasePrice, $newSellingPrice, $changeReason) {
            
            // الحصول على السعر الحالي
            $currentPricing = ProductBasePricing::getCurrentPricing($productId);
            
            if (!$currentPricing) {
                throw new \RuntimeException('لا يوجد تسعير نشط لهذا المنتج');
            }

            // حفظ التاريخ
            ProductPriceHistory::create([
                'product_id' => $productId,
                'base_unit' => $currentPricing->base_unit,
                'old_purchase_price' => $currentPricing->base_purchase_price,
                'new_purchase_price' => $newPurchasePrice,
                'old_selling_price' => $currentPricing->base_selling_price,
                'new_selling_price' => $newSellingPrice,
                'change_reason' => $changeReason,
                'changed_by' => auth()->id(),
            ]);

            // تحديث السعر
            $currentPricing->update([
                'base_purchase_price' => $newPurchasePrice,
                'base_selling_price' => $newSellingPrice,
                'updated_by' => auth()->id(),
            ]);

            // مسح الكاش
            $this->clearPricingCache($productId);

            // لوج التغيير
            Log::info('Base price updated', [
                'product_id' => $productId,
                'old_purchase' => $currentPricing->base_purchase_price,
                'new_purchase' => $newPurchasePrice,
                'old_selling' => $currentPricing->base_selling_price,
                'new_selling' => $newSellingPrice,
                'reason' => $changeReason,
                'changed_by' => auth()->id(),
            ]);

            return $currentPricing->fresh();
        });
    }

    /**
     * 🎯 تحديث جماعي للأسعار حسب الوحدة الأساسية
     * 
     * مثال: تحديث كل المنتجات التي وحدتها الأساسية "طن"
     */
    public function bulkUpdateByBaseUnit(
        string $baseUnit,
        float $newPurchasePrice,
        float $profitValue,
        string $profitType = 'fixed',
        ?array $selectedProductIds = null,
        string $changeReason = null
    ): array {
        return DB::transaction(function () use (
            $baseUnit, 
            $newPurchasePrice, 
            $profitValue, 
            $profitType, 
            $selectedProductIds,
            $changeReason
        ) {
            
            // جلب المنتجات المستهدفة
            $query = ProductBasePricing::byBaseUnit($baseUnit)->current();
            
            if ($selectedProductIds) {
                $query->whereIn('product_id', $selectedProductIds);
            }
            
            $pricings = $query->get();
            
            if ($pricings->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'لا توجد منتجات للتحديث',
                    'updated_count' => 0,
                ];
            }

            $updatedCount = 0;
            $errors = [];

            foreach ($pricings as $pricing) {
                try {
                    // حساب سعر البيع الجديد
                    $newSellingPrice = $profitType === 'percentage'
                        ? $newPurchasePrice * (1 + $profitValue / 100)
                        : $newPurchasePrice + $profitValue;

                    // حفظ التاريخ
                    ProductPriceHistory::create([
                        'product_id' => $pricing->product_id,
                        'base_unit' => $pricing->base_unit,
                        'old_purchase_price' => $pricing->base_purchase_price,
                        'new_purchase_price' => $newPurchasePrice,
                        'old_selling_price' => $pricing->base_selling_price,
                        'new_selling_price' => $newSellingPrice,
                        'change_reason' => $changeReason ?? "تحديث جماعي للوحدة: {$baseUnit}",
                        'changed_by' => auth()->id(),
                    ]);

                    // تحديث السعر
                    $pricing->update([
                        'base_purchase_price' => $newPurchasePrice,
                        'base_selling_price' => $newSellingPrice,
                        'profit_type' => $profitType,
                        'profit_value' => $profitValue,
                        'updated_by' => auth()->id(),
                    ]);

                    $updatedCount++;

                } catch (\Exception $e) {
                    $errors[] = "فشل تحديث المنتج #{$pricing->product_id}: " . $e->getMessage();
                    
                    Log::error('Bulk price update failed for product', [
                        'product_id' => $pricing->product_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // مسح الكاش
            Cache::forget("pricing_stats_{$baseUnit}");

            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'total_count' => $pricings->count(),
                'errors' => $errors,
                'message' => "تم تحديث {$updatedCount} منتج من أصل {$pricings->count()}",
            ];
        });
    }

    /**
     * 📊 الحصول على معاينة التحديث قبل التطبيق
     */
    public function previewBulkUpdate(
        string $baseUnit,
        float $newPurchasePrice,
        float $profitValue,
        string $profitType = 'fixed'
    ): array {
        $pricings = ProductBasePricing::with('product')
            ->byBaseUnit($baseUnit)
            ->current()
            ->get();

        $preview = [];
        
        foreach ($pricings as $pricing) {
            $newSellingPrice = $profitType === 'percentage'
                ? $newPurchasePrice * (1 + $profitValue / 100)
                : $newPurchasePrice + $profitValue;

            $preview[] = [
                'product_id' => $pricing->product_id,
                'product_name' => $pricing->product?->name ?? null,
                'sku' => $pricing->product?->sku ?? null,
                'base_unit' => $pricing->base_unit,
                'old_purchase_price' => (float) $pricing->base_purchase_price,
                'new_purchase_price' => $newPurchasePrice,
                'old_selling_price' => (float) $pricing->base_selling_price,
                'new_selling_price' => round($newSellingPrice, 2),
                'old_profit' => $pricing->profit_margin,
                'new_profit' => round($newSellingPrice - $newPurchasePrice, 2),
                'price_change' => round($newSellingPrice - $pricing->base_selling_price, 2),
                'price_change_percentage' => $pricing->base_selling_price > 0
                    ? round((($newSellingPrice - $pricing->base_selling_price) / $pricing->base_selling_price) * 100, 2)
                    : 0,
            ];
        }

        return $preview;
    }

    /**
     * 📈 إحصائيات التسعير حسب الوحدة الأساسية
     */
    public function getBaseUnitStatistics(string $baseUnit): array
    {
        $cacheKey = "pricing_stats_{$baseUnit}";
        
        return Cache::remember($cacheKey, 1800, function () use ($baseUnit) {
            $pricings = ProductBasePricing::with('product')
                ->byBaseUnit($baseUnit)
                ->current()
                ->get();

            if ($pricings->isEmpty()) {
                return [
                    'total_products' => 0,
                    'avg_purchase_price' => 0,
                    'avg_selling_price' => 0,
                    'avg_profit_margin' => 0,
                    'min_purchase_price' => 0,
                    'max_purchase_price' => 0,
                    'total_value' => 0,
                ];
            }

            return [
                'total_products' => $pricings->count(),
                'avg_purchase_price' => round($pricings->avg('base_purchase_price'), 2),
                'avg_selling_price' => round($pricings->avg('base_selling_price'), 2),
                'avg_profit_margin' => round($pricings->avg(function ($p) {
                    return $p->base_selling_price - $p->base_purchase_price;
                }), 2),
                'min_purchase_price' => round($pricings->min('base_purchase_price'), 2),
                'max_purchase_price' => round($pricings->max('base_purchase_price'), 2),
                'total_value' => round($pricings->sum('base_purchase_price'), 2),
            ];
        });
    }

    /**
     * 🔍 الحصول على أسعار وحدة بيع محددة
     */
    public function getSellingUnitPrices(int $productId, string $unitName): ?array
    {
        $unit = ProductSellingUnit::where('product_id', $productId)
            ->where('unit_name', $unitName)
            ->active()
            ->first();

        if (!$unit) {
            return null;
        }

        return $unit->calculated_prices;
    }

    /**
     * 📋 الحصول على كل وحدات البيع مع الأسعار لمنتج
     */
    public function getProductSellingUnitsWithPrices(int $productId): array
    {
        $units = ProductSellingUnit::forProduct($productId)
            ->active()
            ->ordered()
            ->get();

        $result = [];
        
        foreach ($units as $unit) {
            $prices = $unit->calculated_prices;
            
            $result[] = [
                'id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'full_name' => $unit->full_name,
                'quantity_in_base_unit' => $unit->quantity_in_base_unit,
                'barcode' => $unit->barcode,
                'is_default' => $unit->is_default,
                'purchase_price' => $prices['purchase'] ?? 0,
                'selling_price' => $prices['selling'] ?? 0,
                'profit' => $prices['profit'] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * 📊 تقرير تاريخ الأسعار
     */
    public function getPriceHistoryReport(int $productId, int $limit = 10): array
    {
        $history = ProductPriceHistory::where('product_id', $productId)
            ->orderBy('changed_at', 'desc')
            ->limit($limit)
            ->get();

        $report = [];
        
        foreach ($history as $change) {
            $report[] = [
                'date' => $change->changed_at->format('Y-m-d H:i:s'),
                'old_purchase' => $change->old_purchase_price,
                'new_purchase' => $change->new_purchase_price,
                'purchase_diff' => $change->new_purchase_price - $change->old_purchase_price,
                'old_selling' => $change->old_selling_price,
                'new_selling' => $change->new_selling_price,
                'selling_diff' => $change->new_selling_price - $change->old_selling_price,
                'reason' => $change->change_reason,
                'changed_by' => $change->changed_by,
            ];
        }

        return $report;
    }

    /**
     * 💡 اقتراح ذكي للأسعار عند إضافة منتج جديد
     */
    public function suggestPricing(string $baseUnit, ?string $category = null): ?array
    {
        $query = ProductBasePricing::with('product')
            ->byBaseUnit($baseUnit)
            ->current();

        if ($category) {
            $query->whereHas('product', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $pricings = $query->get();

        if ($pricings->isEmpty()) {
            return null;
        }

        $avgPurchase = $pricings->avg('base_purchase_price');
        $avgSelling = $pricings->avg('base_selling_price');
        $avgProfit = $avgSelling - $avgPurchase;

        return [
            'suggested_purchase_price' => round($avgPurchase, 2),
            'suggested_selling_price' => round($avgSelling, 2),
            'suggested_profit_margin' => round($avgProfit, 2),
            'suggested_profit_percentage' => $avgPurchase > 0 
                ? round(($avgProfit / $avgPurchase) * 100, 2) 
                : 0,
            'base_unit' => $baseUnit,
            'sample_size' => $pricings->count(),
            'category' => $category,
        ];
    }

    /**
     * 🔄 تحويل السعر من وحدة لأخرى
     */
    public function convertPrice(
        float $price,
        string $fromUnit,
        string $toUnit,
        float $fromQuantity = 1.0,
        float $toQuantity = 1.0
    ): float {
        // حساب السعر لكل وحدة أساسية واحدة
        $pricePerBaseUnit = $price / $fromQuantity;
        
        // حساب السعر للوحدة الهدف
        return round($pricePerBaseUnit * $toQuantity, 2);
    }

    /**
     * 🧹 مسح الكاش
     */
    private function clearPricingCache(int $productId): void
    {
        $pricing = ProductBasePricing::getCurrentPricing($productId);
        
        if ($pricing) {
            Cache::forget("pricing_stats_{$pricing->base_unit}");
        }
        
        Cache::forget("product_pricing_{$productId}");
    }

    /**
     * ✅ توليد SKU تلقائي
     */
    private function generateSKU(): string
    {
        do {
            $sku = 'SKU-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Product::where('sku', $sku)->exists());
        
        return $sku;
    }

    /**
     * ✅ توليد كود منتج تلقائي
     */
    private function generateProductCode(): string
    {
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $number = $lastProduct ? $lastProduct->id + 1 : 1;
        return 'PRD' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}