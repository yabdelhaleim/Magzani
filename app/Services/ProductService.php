<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBaseUnit;
use App\Models\ProductSellingUnit;
use App\Models\ProductPriceHistory;
use App\Models\PriceChangeHistory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ProductService
{
    private const UNITS_BY_CATEGORY = [
        'weight' => [
            'label' => 'الوزن',
            'units' => [
                'kg' => 'كيلوجرام (kg)',
                'g' => 'جرام (g)',
                'ton' => 'طن',
                'lb' => 'رطل (lb)',
            ]
        ],
        'length' => [
            'label' => 'الطول',
            'units' => [
                'm' => 'متر (m)',
                'cm' => 'سنتيمتر (cm)',
                'mm' => 'ملليمتر (mm)',
                'km' => 'كيلومتر (km)',
            ]
        ],
        'volume' => [
            'label' => 'الحجم',
            'units' => [
                'l' => 'لتر (L)',
                'ml' => 'ملليلتر (ml)',
                'm3' => 'متر مكعب (m³)',
            ]
        ],
        'quantity' => [
            'label' => 'الكمية',
            'units' => [
                'piece' => 'قطعة',
                'box' => 'صندوق',
                'carton' => 'كرتونة',
                'bag' => 'كيس',
                'pack' => 'باكيت',
                'dozen' => 'دستة',
            ]
        ],
        'area' => [
            'label' => 'المساحة',
            'units' => [
                'm2' => 'متر مربع (m²)',
                'cm2' => 'سنتيمتر مربع (cm²)',
            ]
        ],
    ];

    private const UNIT_LABELS = [
        'kg' => 'كيلوجرام',
        'g' => 'جرام',
        'ton' => 'طن',
        'lb' => 'رطل',
        'piece' => 'قطعة',
        'box' => 'صندوق',
        'carton' => 'كرتونة',
        'bag' => 'كيس',
        'pack' => 'باكيت',
        'dozen' => 'دستة',
        'm' => 'متر',
        'cm' => 'سنتيمتر',
        'mm' => 'ملليمتر',
        'km' => 'كيلومتر',
        'l' => 'لتر',
        'ml' => 'ملليلتر',
        'm3' => 'متر مكعب',
        'm2' => 'متر مربع',
        'cm2' => 'سنتيمتر مربع',
    ];

    private const UNIT_TYPE_MAP = [
        'kg' => 'weight',
        'g' => 'weight',
        'ton' => 'weight',
        'lb' => 'weight',
        'm' => 'length',
        'cm' => 'length',
        'mm' => 'length',
        'km' => 'length',
        'l' => 'volume',
        'ml' => 'volume',
        'm3' => 'volume',
        'piece' => 'piece',
        'box' => 'piece',
        'carton' => 'piece',
        'bag' => 'piece',
        'pack' => 'piece',
        'dozen' => 'piece',
        'm2' => 'area',
        'cm2' => 'area',
    ];

    /**
     * 🔥 إنشاء منتج جديد (متوافق 100% مع النظام الجديد)
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            try {
                // ✅ 1. Validation شامل
                $this->validateProductData($data);

                // ✅ 2. توليد الأكواد الفريدة
                $code = $this->generateUniqueCode();
                $sku = !empty($data['sku']) ? trim($data['sku']) : $this->generateUniqueSKU();
                
                // ✅ 3. التحقق من عدم تكرار SKU/Barcode
                $this->checkDuplicates($sku, $data['barcode'] ?? null);

                // ✅ 4. الحصول على تسمية الوحدة
                $baseUnitLabel = !empty($data['base_unit_label']) 
                    ? trim($data['base_unit_label']) 
                    : $this->getUnitLabel($data['base_unit']);

                // ✅ 5. إنشاء المنتج
                $product = Product::create([
                    'name' => trim($data['name']),
                    'code' => $code,
                    'sku' => $sku,
                    'barcode' => !empty($data['barcode']) ? trim($data['barcode']) : null,
                    'category' => trim($data['category']),
                    'description' => !empty($data['description']) ? trim($data['description']) : null,
                    'base_unit' => $data['base_unit'],
                    'base_unit_label' => $baseUnitLabel,
                    
                    // الأسعار
                    'purchase_price' => (float) $data['purchase_price'],
                    'selling_price' => (float) $data['selling_price'],
                    'min_selling_price' => !empty($data['min_selling_price']) ? (float) $data['min_selling_price'] : null,
                    'wholesale_price' => !empty($data['wholesale_price']) ? (float) $data['wholesale_price'] : null,
                    'tax_rate' => !empty($data['tax_rate']) ? (float) $data['tax_rate'] : 0,
                    'default_discount' => !empty($data['default_discount']) ? (float) $data['default_discount'] : 0,
                    
                    // المخزون
                    'stock_alert_quantity' => !empty($data['stock_alert_quantity']) ? (float) $data['stock_alert_quantity'] : 10,
                    
                    // الحالة
                    'is_active' => $data['is_active'] ?? true,
                ]);

                // 🔥 6. إنشاء الوحدة الأساسية في product_base_units
                $baseUnit = $this->createProductBaseUnit($product, $data);

                // 🔥 7. إنشاء selling unit للوحدة الأساسية (مع base_unit_id)
                $this->createBaseSellingUnit($product, $baseUnit, $data['base_unit'], $baseUnitLabel);

                // ✅ 8. إضافة للمخزن
                $warehouseId = $this->determineWarehouse($data);
                $initialQuantity = (float) ($data['warehouses'][0]['quantity'] ?? 0);
                $minStock = (float) ($data['warehouses'][0]['min_stock'] ?? $data['stock_alert_quantity'] ?? 10);
                
                $this->attachToWarehouseSecure(
                    $product,
                    $warehouseId,
                    $initialQuantity,
                    $minStock
                );

                // ✅ 9. تسجيل حركة المخزون
                if ($initialQuantity > 0) {
                    $this->logInitialStock($product, $warehouseId, $initialQuantity);
                }

                // ✅ 10. مسح الكاش
                $this->clearProductCache();

                Log::info('✅ تم إنشاء منتج جديد', [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'base_unit_id' => $baseUnit->id,
                ]);

                return $product->fresh(['sellingUnits', 'warehouses', 'baseUnit']);

            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('❌ فشل إنشاء المنتج', [
                    'data' => $data,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new RuntimeException('فشل إنشاء المنتج: ' . $e->getMessage());
            }
        });
    }

    /**
     * 🔥 إنشاء سجل الوحدة الأساسية في product_base_units
     */
    private function createProductBaseUnit(Product $product, array $data): ProductBaseUnit
    {
        $unitType = self::UNIT_TYPE_MAP[$data['base_unit']] ?? 'piece';
        $unitLabel = $this->getUnitLabel($data['base_unit']);
        
        $purchasePrice = (float) $data['purchase_price'];
        $sellingPrice = (float) $data['selling_price'];
        $profitMargin = $purchasePrice > 0 
            ? round((($sellingPrice - $purchasePrice) / $purchasePrice) * 100, 2) 
            : 0;

        return ProductBaseUnit::create([
            'product_id' => $product->id,
            'product_code' => $product->code,
            'base_unit_type' => $unitType,
            'base_unit_code' => $data['base_unit'],
            'base_unit_label' => $unitLabel,
            'base_purchase_price' => $purchasePrice,
            'base_selling_price' => $sellingPrice,
            'profit_margin' => $profitMargin,
            'is_active' => true,
            'auto_update_selling_units' => true,
            'effective_from' => now()->toDateString(),
            'notes' => 'وحدة أساسية تم إنشاؤها تلقائياً',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * 🔥 إنشاء selling unit للوحدة الأساسية (مع base_unit_id)
     */
private function createBaseSellingUnit(
    Product $product, 
    ProductBaseUnit $baseUnit,
    string $unitCode, 
    string $unitLabel
): void {
    try {
        // ✅ التحقق من عدم وجود selling unit أساسية مسبقاً
        $exists = DB::table('product_selling_units')
            ->where('product_id', $product->id)
            ->where('is_base', true)
            ->exists();

        if ($exists) {
            Log::warning('الوحدة الأساسية موجودة مسبقاً', [
                'product_id' => $product->id
            ]);
            return;
        }

        // ✅ إنشاء السجل باستخدام DB::table للتأكد من جميع الحقول
        $inserted = DB::table('product_selling_units')->insert([
            'product_id' => $product->id,
            'base_unit_id' => $baseUnit->id,
            
            // 🔥 الحقول النصية الثلاثة (كلها مطلوبة)
            'unit_name' => $unitLabel,
            'unit_code' => $unitCode,
            'unit_label' => $unitLabel,
            
            // الكميات والأسعار
            'quantity_in_base_unit' => 1.0,
            'conversion_factor' => 1.0,
            'unit_purchase_price' => round($baseUnit->base_purchase_price, 2),
            'unit_selling_price' => round($baseUnit->base_selling_price, 2),
            
            // الإعدادات
            'auto_calculate_price' => true,
            'is_base' => true,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 0,
            
            // Audit
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$inserted) {
            throw new RuntimeException('فشل إدراج السجل في product_selling_units');
        }

        // ✅ التحقق من نجاح الإنشاء
        $record = DB::table('product_selling_units')
            ->where('product_id', $product->id)
            ->where('base_unit_id', $baseUnit->id)
            ->where('is_base', true)
            ->first();

        if (!$record) {
            throw new RuntimeException('السجل لم يُنشأ رغم نجاح الـ insert!');
        }

        Log::info('✅ تم إنشاء selling unit للوحدة الأساسية', [
            'product_id' => $product->id,
            'base_unit_id' => $baseUnit->id,
            'unit_name' => $unitLabel,
            'unit_code' => $unitCode,
        ]);

    } catch (\Exception $e) {
        Log::error('❌ فشل إنشاء selling unit', [
            'product_id' => $product->id,
            'base_unit_id' => $baseUnit->id ?? null,
            'unit_label' => $unitLabel ?? null,
            'error' => $e->getMessage(),
            'sql_state' => $e->getCode() ?? 'unknown',
        ]);
        
        throw new RuntimeException('فشل إنشاء وحدة البيع الأساسية: ' . $e->getMessage());
    }
}

    /**
     * 🔥 تحديث منتج موجود (متوافق مع النظام الجديد)
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            try {
                // ✅ Validation
                $this->validateProductData($data);

                $baseUnitLabel = !empty($data['base_unit_label']) 
                    ? trim($data['base_unit_label']) 
                    : $this->getUnitLabel($data['base_unit']);

                // ✅ التحقق من SKU/Barcode
                $this->checkDuplicates(
                    $data['sku'] ?? $product->sku,
                    $data['barcode'] ?? null,
                    $product->id
                );

                // ✅ حفظ الأسعار القديمة
                $oldPurchasePrice = $product->purchase_price;
                $oldSellingPrice = $product->selling_price;

                $newPurchasePrice = (float) $data['purchase_price'];
                $newSellingPrice = (float) $data['selling_price'];

                // ✅ تحديث المنتج
                $product->update([
                    'name' => trim($data['name']),
                    'sku' => !empty($data['sku']) ? trim($data['sku']) : $product->sku,
                    'barcode' => !empty($data['barcode']) ? trim($data['barcode']) : null,
                    'category' => trim($data['category']),
                    'description' => !empty($data['description']) ? trim($data['description']) : null,
                    'base_unit' => $data['base_unit'],
                    'base_unit_label' => $baseUnitLabel,
                    
                    'purchase_price' => $newPurchasePrice,
                    'selling_price' => $newSellingPrice,
                    'min_selling_price' => !empty($data['min_selling_price']) ? (float) $data['min_selling_price'] : null,
                    'wholesale_price' => !empty($data['wholesale_price']) ? (float) $data['wholesale_price'] : null,
                    'tax_rate' => !empty($data['tax_rate']) ? (float) $data['tax_rate'] : 0,
                    'default_discount' => !empty($data['default_discount']) ? (float) $data['default_discount'] : 0,
                    'stock_alert_quantity' => !empty($data['stock_alert_quantity']) ? (float) $data['stock_alert_quantity'] : null,
                    
                    'is_active' => $data['is_active'] ?? $product->is_active,
                ]);

                // 🔥 تحديث الوحدة الأساسية في product_base_units
                $baseUnit = ProductBaseUnit::where('product_id', $product->id)
                    ->where('is_active', true)
                    ->first();

                if ($baseUnit) {
                    // تحديث السعر في الوحدة الأساسية (الـ Observer سيتولى تحديث selling units)
                    $profitMargin = $newPurchasePrice > 0 
                        ? round((($newSellingPrice - $newPurchasePrice) / $newPurchasePrice) * 100, 2) 
                        : 0;

                    $baseUnit->update([
                        'base_unit_code' => $data['base_unit'],
                        'base_unit_label' => $baseUnitLabel,
                        'base_purchase_price' => $newPurchasePrice,
                        'base_selling_price' => $newSellingPrice,
                        'profit_margin' => $profitMargin,
                        'updated_by' => auth()->id(),
                    ]);

                    // 🔥 سجل تاريخي في price_change_history (إذا تغير السعر)
                    if ($oldPurchasePrice != $newPurchasePrice || $oldSellingPrice != $newSellingPrice) {
                        $diffPercentage = $oldSellingPrice > 0 
                            ? round((($newSellingPrice - $oldSellingPrice) / $oldSellingPrice) * 100, 2) 
                            : 0;

                        PriceChangeHistory::create([
                            'product_id' => $product->id,
                            'base_unit_id' => $baseUnit->id,
                            'old_base_purchase_price' => $oldPurchasePrice,
                            'new_base_purchase_price' => $newPurchasePrice,
                            'old_base_selling_price' => $oldSellingPrice,
                            'new_base_selling_price' => $newSellingPrice,
                            'diff_percentage' => $diffPercentage,
                            'change_reason' => $data['price_change_reason'] ?? 'تحديث يدوي',
                            'changed_by' => auth()->id(),
                            'changed_at' => now(),
                        ]);
                    }
                } else {
                    // إنشاء الوحدة الأساسية إذا لم تكن موجودة
                    $baseUnit = $this->createProductBaseUnit($product, $data);
                    $this->createBaseSellingUnit($product, $baseUnit, $data['base_unit'], $baseUnitLabel);
                }

                $this->clearProductCache();

                Log::info('✅ تم تحديث المنتج', [
                    'product_id' => $product->id,
                    'old_price' => $oldSellingPrice,
                    'new_price' => $newSellingPrice,
                ]);

                return $product->fresh(['sellingUnits', 'warehouses', 'baseUnit']);

            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('❌ فشل تحديث المنتج', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
                throw new RuntimeException('فشل تحديث المنتج: ' . $e->getMessage());
            }
        });
    }

    /**
     * 🔥 التحديث الجماعي للأسعار (متوافق مع Observer)
     */
    public function bulkUpdatePrices(array $data): array
    {
        return DB::transaction(function () use ($data) {
            try {
                $selectedProductIds = json_decode($data['selected_products'], true);
                
                if (empty($selectedProductIds) || !is_array($selectedProductIds)) {
                    throw new RuntimeException('لم يتم تحديد أي منتجات');
                }

                if (count($selectedProductIds) > 5000) {
                    throw new RuntimeException('لا يمكن تحديث أكثر من 5000 منتج دفعة واحدة');
                }

                $purchasePrice = (float) ($data['purchase_price'] ?? $data['base_purchase_price'] ?? 0);
                $profitValue = (float) $data['profit_value'];
                
                if ($data['profit_type'] === 'percentage') {
                    $profit = ($purchasePrice * $profitValue) / 100;
                } else {
                    $profit = $profitValue;
                }
                
                $sellingPrice = round($purchasePrice + $profit, 2);
                $profitMargin = $purchasePrice > 0 
                    ? round((($sellingPrice - $purchasePrice) / $purchasePrice) * 100, 2) 
                    : 0;

                $chunks = array_chunk($selectedProductIds, 500);
                $totalUpdated = 0;
                $now = now();
                $userId = auth()->id();

                foreach ($chunks as $chunk) {
                    foreach ($chunk as $productId) {
                        // 🔥 الحصول على الوحدة الأساسية
                        $baseUnit = ProductBaseUnit::where('product_id', $productId)
                            ->where('is_active', true)
                            ->first();

                        if (!$baseUnit) {
                            Log::warning("لا توجد وحدة أساسية للمنتج {$productId}");
                            continue;
                        }

                        // 🔥 حفظ السجل التاريخي
                        if ($baseUnit->base_purchase_price != $purchasePrice || $baseUnit->base_selling_price != $sellingPrice) {
                            $diffPercentage = $baseUnit->base_selling_price > 0 
                                ? round((($sellingPrice - $baseUnit->base_selling_price) / $baseUnit->base_selling_price) * 100, 2) 
                                : 0;

                            PriceChangeHistory::create([
                                'product_id' => $productId,
                                'base_unit_id' => $baseUnit->id,
                                'old_base_purchase_price' => $baseUnit->base_purchase_price,
                                'new_base_purchase_price' => $purchasePrice,
                                'old_base_selling_price' => $baseUnit->base_selling_price,
                                'new_base_selling_price' => $sellingPrice,
                                'diff_percentage' => $diffPercentage,
                                'change_reason' => $data['change_reason'] ?? 'تحديث جماعي',
                                'changed_by' => $userId,
                                'changed_at' => $now,
                            ]);
                        }

                        // 🔥 تحديث product_base_units (Observer سيحدث selling units تلقائياً)
                        $baseUnit->update([
                            'base_purchase_price' => $purchasePrice,
                            'base_selling_price' => $sellingPrice,
                            'profit_margin' => $profitMargin,
                            'updated_by' => $userId,
                        ]);

                        // 🔥 تحديث جدول products (للتوافق مع الكود القديم)
                        Product::where('id', $productId)->update([
                            'purchase_price' => $purchasePrice,
                            'selling_price' => $sellingPrice,
                            'updated_at' => $now,
                        ]);

                        $totalUpdated++;
                    }
                }

                $this->clearProductCache();

                Log::info('✅ تحديث جماعي للأسعار', [
                    'count' => $totalUpdated,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice
                ]);

                return [
                    'updated_count' => $totalUpdated,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'profit' => round($profit, 2),
                    'profit_percentage' => $profitMargin,
                ];

            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('❌ فشل التحديث الجماعي', ['error' => $e->getMessage()]);
                throw new RuntimeException('فشل التحديث الجماعي: ' . $e->getMessage());
            }
        });
    }

    /**
     * ✅ حذف منتج (آمن)
     */
    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {
            try {
                // ✅ التحقق من عدم استخدام المنتج في فواتير
                if ($this->isProductUsedInInvoices($product)) {
                    throw new RuntimeException('لا يمكن حذف المنتج لأنه مستخدم في فواتير');
                }

                // ✅ التحقق من عدم وجود مخزون
                $totalStock = DB::table('product_warehouse')
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                if ($totalStock > 0) {
                    throw new RuntimeException("لا يمكن حذف المنتج لأنه يحتوي على مخزون (الكمية: {$totalStock})");
                }

                // ✅ حذف العلاقات
                DB::table('product_warehouse')->where('product_id', $product->id)->delete();
                DB::table('product_selling_units')->where('product_id', $product->id)->delete();
                DB::table('product_base_units')->where('product_id', $product->id)->delete();
                DB::table('price_change_history')->where('product_id', $product->id)->delete();

                // ✅ حذف المنتج
                $product->delete();

                $this->clearProductCache();

                Log::info('✅ تم حذف المنتج', ['product_id' => $product->id, 'name' => $product->name]);

            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('❌ فشل حذف المنتج', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
                throw new RuntimeException('فشل حذف المنتج: ' . $e->getMessage());
            }
        });
    }

    /**
     * ✅ الحصول على التصنيفات حسب الوحدة
     */
    public function getCategoriesByUnit(string $baseUnit): array
    {
        $cacheKey = "categories_by_unit_{$baseUnit}";
        
        return Cache::remember($cacheKey, 3600, function () use ($baseUnit) {
            return DB::table('product_base_units as pbu')
                ->join('products as p', 'p.id', '=', 'pbu.product_id')
                ->where('pbu.base_unit_code', $baseUnit)
                ->where('pbu.is_active', true)
                ->where('p.is_active', true)
                ->whereNotNull('p.category')
                ->where('p.category', '!=', '')
                ->distinct()
                ->orderBy('p.category')
                ->pluck('p.category')
                ->values()
                ->toArray();
        });
    }

    /**
     * ✅ الحصول على المنتجات حسب الوحدة والتصنيف
     */
    public function getProductsByUnitAndCategory(string $baseUnit, string $category): array
    {
        $cacheKey = "products_{$baseUnit}_{$category}";

        return Cache::remember($cacheKey, 1800, function () use ($baseUnit, $category) {
            return DB::table('products as p')
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
                    'pbu.id as base_unit_id',
                    'pbu.base_unit_code',
                    'pbu.base_unit_label',
                    'pbu.base_purchase_price',
                    'pbu.base_selling_price',
                    'pbu.profit_margin',
                ])
                ->orderBy('p.name')
                ->get()
                ->toArray();
        });
    }

    /* ===========================
     * 🛠️ الباقي من الكود كما هو
     * =========================== */

    private function determineWarehouse(array $data): int
    {
        if (!empty($data['warehouses'][0]['warehouse_id'])) {
            $warehouseId = (int) $data['warehouses'][0]['warehouse_id'];
            
            $warehouse = DB::table('warehouses')
                ->where('id', $warehouseId)
                ->where('is_active', true)
                ->first();
            
            if ($warehouse) {
                return $warehouseId;
            }
            
            Log::warning("المخزن {$warehouseId} غير موجود أو غير نشط، سيتم استخدام المخزن الافتراضي");
        }

        $defaultWarehouse = Cache::remember('default_warehouse_id', 3600, function () {
            return DB::table('warehouses')
                ->where('is_active', true)
                ->orderBy('id')
                ->value('id');
        });

        if (!$defaultWarehouse) {
            throw new RuntimeException('لا يوجد مخازن نشطة في النظام! يجب إنشاء مخزن واحد على الأقل');
        }

        return (int) $defaultWarehouse;
    }

// في ProductService.php

private function attachToWarehouseSecure(
    Product $product, 
    int $warehouseId, 
    float $quantity, 
    float $minStock
): void {
    try {
        Log::info('🔄 بدء إضافة المنتج للمخزن', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
        ]);

        // ✅ التحقق من المخزن
        $warehouse = DB::table('warehouses')
            ->where('id', $warehouseId)
            ->first(['id', 'name', 'is_active']);

        if (!$warehouse) {
            throw new RuntimeException("المخزن رقم {$warehouseId} غير موجود");
        }

        if (!$warehouse->is_active) {
            throw new RuntimeException("المخزن '{$warehouse->name}' غير نشط");
        }

        // ✅ التحقق من عدم التكرار
        $exists = DB::table('product_warehouse')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouseId)
            ->exists();

        if ($exists) {
            Log::warning("⚠️ المنتج موجود بالفعل في المخزن", [
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
            ]);
            return;
        }

        // ✅ تنظيف القيم
        $quantity = max(0, round($quantity, 3));
        $minStock = max(0, (int) $minStock);
        $averageCost = round($product->purchase_price ?? 0, 2);

        Log::info('📊 البيانات المُنظفة', [
            'quantity' => $quantity,
            'min_stock' => $minStock,
            'average_cost' => $averageCost,
        ]);

        // ✅ الإدراج المباشر
        $inserted = DB::table('product_warehouse')->insert([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'min_stock' => $minStock,
            'average_cost' => $averageCost,
            'last_count_quantity' => null,
            'last_count_date' => null,
            'adjustment_total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$inserted) {
            throw new RuntimeException("فشل الـ INSERT في product_warehouse");
        }

        // ✅ التحقق النهائي
        $record = DB::table('product_warehouse')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$record) {
            throw new RuntimeException("السجل لم يُنشأ رغم نجاح الـ insert!");
        }

        Log::info('✅ تم ربط المنتج بالمخزن بنجاح', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => $record->quantity,
            'record_id' => property_exists($record, 'id') ? $record->id : 'N/A',
        ]);

    } catch (RuntimeException $e) {
        throw $e;
    } catch (\Exception $e) {
        Log::error('❌ فشل ربط المنتج بالمخزن', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw new RuntimeException("فشل إضافة المنتج للمخزن: " . $e->getMessage());
    }
}    private function logInitialStock(Product $product, int $warehouseId, float $quantity): void
    {
        try {
            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'initial_stock',
                'quantity' => $quantity,
                'reference_type' => 'product_creation',
                'reference_id' => $product->id,
                'notes' => "رصيد أولي عند إنشاء المنتج",
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('فشل تسجيل حركة المخزون الأولية', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function validateProductData(array $data): void
    {
        if (empty(trim($data['name'] ?? ''))) {
            throw new RuntimeException('اسم المنتج مطلوب');
        }

        if (mb_strlen(trim($data['name'])) > 255) {
            throw new RuntimeException('اسم المنتج طويل جداً (الحد الأقصى 255 حرف)');
        }

        if (empty($data['base_unit'])) {
            throw new RuntimeException('وحدة القياس الأساسية مطلوبة');
        }

        if (!isset(self::UNIT_LABELS[$data['base_unit']])) {
            throw new RuntimeException('وحدة القياس غير صالحة');
        }

        if (empty(trim($data['category'] ?? ''))) {
            throw new RuntimeException('تصنيف المنتج مطلوب');
        }

        $purchasePrice = (float) ($data['purchase_price'] ?? 0);
        $sellingPrice = (float) ($data['selling_price'] ?? 0);

        if ($purchasePrice < 0) {
            throw new RuntimeException('سعر الشراء يجب أن يكون صفر أو أكبر');
        }

        if ($sellingPrice < 0) {
            throw new RuntimeException('سعر البيع يجب أن يكون صفر أو أكبر');
        }

        if ($sellingPrice > 0 && $sellingPrice < $purchasePrice) {
            throw new RuntimeException('سعر البيع لا يمكن أن يكون أقل من سعر الشراء');
        }
    }

    private function checkDuplicates(?string $sku, ?string $barcode, ?int $exceptId = null): void
    {
        if (!empty($sku)) {
            $query = DB::table('products')->where('sku', $sku);
            if ($exceptId) {
                $query->where('id', '!=', $exceptId);
            }
            if ($query->exists()) {
                throw new RuntimeException("رمز SKU '{$sku}' موجود بالفعل في النظام");
            }
        }

        if (!empty($barcode)) {
            $query = DB::table('products')->where('barcode', $barcode);
            if ($exceptId) {
                $query->where('id', '!=', $exceptId);
            }
            if ($query->exists()) {
                throw new RuntimeException("الباركود '{$barcode}' موجود بالفعل في النظام");
            }
        }
    }

    private function updateBaseSellingUnit(Product $product, string $unitCode, string $unitLabel, array $data): void
    {
        DB::table('product_selling_units')
            ->where('product_id', $product->id)
            ->where('is_base', true)
            ->update([
                'unit_name' => $unitLabel,
                'unit_code' => $unitCode,
                'unit_label' => $unitLabel,
                'updated_at' => now(),
            ]);
    }

    private function isProductUsedInInvoices(Product $product): bool
    {
        return DB::table('sales_invoice_items')
            ->where('product_id', $product->id)
            ->exists()
            ||
            DB::table('purchase_invoice_items')
                ->where('product_id', $product->id)
                ->exists();
    }

/**
 * 🗑️ مسح الكاش (متوافق مع جميع أنواع الكاش)
 */
private function clearProductCache(): void
{
    try {
        // ✅ مسح الـ keys المحددة
        $keys = [
            'products_count',
            'low_stock_products',
            'warehouses_with_stats',
            'active_warehouses',
            'default_warehouse_id',
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // ✅ مسح كاش التصنيفات (جميع الوحدات الممكنة)
        $possibleUnits = ['kg', 'g', 'ton', 'lb', 'm', 'cm', 'mm', 'km', 'l', 'ml', 'm3', 'piece', 'box', 'carton', 'bag', 'pack', 'dozen', 'm2', 'cm2'];
        
        foreach ($possibleUnits as $unit) {
            Cache::forget("categories_by_unit_{$unit}");
        }

        // ✅ مسح كاش المنتجات (نمط wildcard محاكاة)
        // لو عندك Laravel 9+ يمكن استخدام:
        // Cache::flush(); // ⚠️ بحذر - بيمسح الكاش كله
        
        // أو مسح فقط الـ keys المعروفة
        $categories = DB::table('products')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->toArray();

        foreach ($possibleUnits as $unit) {
            foreach ($categories as $category) {
                Cache::forget("products_{$unit}_{$category}");
                Cache::forget("suggested_pricing_{$unit}_{$category}");
            }
            Cache::forget("suggested_pricing_{$unit}_all");
        }

        Log::debug('✅ تم مسح الكاش بنجاح');

    } catch (\Exception $e) {
        // لا نرمي Exception لأن مسح الكاش ليس critical
        Log::warning('⚠️ فشل مسح الكاش (غير حرج)', [
            'error' => $e->getMessage()
        ]);
    }
}
    private function generateUniqueCode(): string
    {
        $maxId = DB::table('products')->max('id') ?? 0;
        $attempts = 0;
        
        do {
            $newNumber = $maxId + 1 + $attempts + rand(0, 99);
            $code = 'PRD' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            
            $exists = DB::table('products')->where('code', $code)->exists();
            $attempts++;
            
            if ($attempts >= 100) {
                return 'PRD-' . strtoupper(substr(uniqid() . bin2hex(random_bytes(2)), 0, 10));
            }
            
        } while ($exists);
        
        return $code;
    }

    private function generateUniqueSKU(): string
    {
        $attempts = 0;
        
        do {
            $sku = 'SKU-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $exists = DB::table('products')->where('sku', $sku)->exists();
            $attempts++;
            
            if ($attempts >= 50) {
                $sku = 'SKU-' . date('YmdHis') . '-' . strtoupper(substr(md5(microtime(true) . rand()), 0, 6));
                break;
            }
            
        } while ($exists);
        
        return $sku;
    }

    private function getUnitLabel(string $unit): string
    {
        return self::UNIT_LABELS[$unit] ?? $unit;
    }

    public function getUnitsByCategory(): array
    {
        return self::UNITS_BY_CATEGORY;
    }

    // باقي الـ methods للمخزون والنقل والتسوية كما هي...
    public function transferStock(
        Product $product,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            throw new RuntimeException('الكمية يجب أن تكون أكبر من صفر');
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw new RuntimeException('لا يمكن النقل إلى نفس المخزن');
        }

        DB::transaction(function () use ($product, $fromWarehouseId, $toWarehouseId, $quantity, $notes) {
            
            $from = DB::table('product_warehouse')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $fromWarehouseId)
                ->lockForUpdate()
                ->first();
            
            if (!$from) {
                throw new RuntimeException('المنتج غير موجود في المخزن المصدر');
            }

            $available = $from->quantity - ($from->reserved_quantity ?? 0);
            if ($available < $quantity) {
                throw new RuntimeException("الكمية المتاحة غير كافية. المتاح: {$available}");
            }

            DB::table('product_warehouse')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $fromWarehouseId)
                ->update([
                    'quantity' => DB::raw('quantity - ' . $quantity),
                    'updated_at' => now(),
                ]);

            DB::table('product_warehouse')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $toWarehouseId,
                ],
                [
                    'quantity' => DB::raw('COALESCE(quantity, 0) + ' . $quantity),
                    'average_cost' => $from->average_cost ?? 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $fromWarehouseId,
                'type' => 'transfer_out',
                'quantity' => -$quantity,
                'reference_type' => 'warehouse_transfer',
                'reference_id' => $toWarehouseId,
                'notes' => $notes ?? "نقل إلى مخزن رقم {$toWarehouseId}",
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $toWarehouseId,
                'type' => 'transfer_in',
                'quantity' => $quantity,
                'reference_type' => 'warehouse_transfer',
                'reference_id' => $fromWarehouseId,
                'notes' => $notes ?? "نقل من مخزن رقم {$fromWarehouseId}",
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            Cache::forget("stock_stats_{$product->id}");
        });
    }

    public function adjustStockFromCount(
        Product $product,
        int $warehouseId,
        float $actualQuantity,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($product, $warehouseId, $actualQuantity, $notes) {
            
            $current = DB::table('product_warehouse')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (!$current) {
                throw new RuntimeException('المنتج غير موجود في المخزن');
            }

            $variance = $actualQuantity - $current->quantity;

            if ($variance == 0) {
                return;
            }

            DB::table('product_warehouse')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->update([
                    'quantity' => $actualQuantity,
                    'last_count_quantity' => $current->quantity,
                    'last_count_date' => now(),
                    'adjustment_total' => DB::raw('COALESCE(adjustment_total, 0) + ' . $variance),
                    'updated_at' => now(),
                ]);

            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => $variance > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity' => $variance,
                'reference_type' => 'stock_count',
                'notes' => $notes ?? "تسوية من الجرد. الفرق: {$variance}",
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            Cache::forget("stock_stats_{$product->id}");
        });
    }

    public function getStockStatistics(Product $product): array
    {
        $cacheKey = "stock_stats_{$product->id}";

        return Cache::remember($cacheKey, 300, function () use ($product) {
            $stats = DB::table('product_warehouse')
                ->where('product_id', $product->id)
                ->selectRaw('
                    SUM(quantity) as total_quantity,
                    SUM(reserved_quantity) as total_reserved,
                    SUM(GREATEST(0, quantity - COALESCE(reserved_quantity, 0))) as total_available,
                    COUNT(*) as warehouses_count,
                    AVG(average_cost) as avg_cost,
                    SUM(quantity * average_cost) as total_value
                ')
                ->first();
            
            return [
                'total_quantity' => (float) ($stats->total_quantity ?? 0),
                'total_reserved' => (float) ($stats->total_reserved ?? 0),
                'total_available' => (float) ($stats->total_available ?? 0),
                'warehouses_count' => (int) ($stats->warehouses_count ?? 0),
                'average_cost' => (float) ($stats->avg_cost ?? 0),
                'total_value' => (float) ($stats->total_value ?? 0),
            ];
        });
    }

    public function getSuggestedPricing(string $baseUnit, ?string $category = null): array
    {
        $cacheKey = "suggested_pricing_{$baseUnit}_" . ($category ?? 'all');

        return Cache::remember($cacheKey, 1800, function () use ($baseUnit, $category) {
            $query = DB::table('products')
                ->where('base_unit', $baseUnit)
                ->where('is_active', true);
            
            if ($category) {
                $query->where('category', $category);
            }

            $stats = $query->selectRaw('
                COUNT(*) as sample_size,
                AVG(purchase_price) as avg_purchase,
                AVG(selling_price) as avg_selling,
                AVG(selling_price - purchase_price) as avg_profit,
                MIN(purchase_price) as min_purchase,
                MAX(purchase_price) as max_purchase
            ')->first();

            if (!$stats || $stats->sample_size == 0) {
                throw new RuntimeException('لا توجد منتجات مشابهة');
            }

            return [
                'suggested_purchase_price' => round($stats->avg_purchase, 2),
                'suggested_selling_price' => round($stats->avg_selling, 2),
                'suggested_profit_margin' => round($stats->avg_profit, 2),
                'min_purchase_price' => round($stats->min_purchase, 2),
                'max_purchase_price' => round($stats->max_purchase, 2),
                'sample_size' => $stats->sample_size,
            ];
        });
    }

    public function findByCodeOrBarcode(string $search): ?Product
    {
        $cacheKey = "product_search_" . md5($search);

        return Cache::remember($cacheKey, 300, function () use ($search) {
            return Product::where(function ($query) use ($search) {
                    $query->where('code', $search)
                          ->orWhere('barcode', $search)
                          ->orWhere('sku', $search);
                })
                ->with([
                    'warehouses:id,name',
                    'sellingUnits' => fn($q) => $q->select('id', 'product_id', 'unit_name', 'unit_code', 'conversion_factor', 'is_default')->where('is_active', true)
                ])
                ->first();
        });
    }

    public function updateStock(int $productId, int $warehouseId, float $quantity, string $operation): bool
    {
        $product = Product::findOrFail($productId);

        return $operation === 'add'
            ? $product->addStock($quantity, $warehouseId)
            : $product->deductStock($quantity, $warehouseId);
    }
}