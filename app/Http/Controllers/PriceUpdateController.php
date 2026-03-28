<?php

namespace App\Http\Controllers;

use App\Services\AdvancedPricingService;
use App\Traits\UnitsManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * 🎯 PriceUpdateController - المُحسّن للـ Production
 */
class PriceUpdateController extends Controller
{
    use UnitsManagement;

    public function __construct(
        private AdvancedPricingService $pricingService
    ) {}

    /**
     * ✅ صفحة التحديث الذكي للأسعار (محسّنة بالكامل)
     */
    public function bulkPriceUpdatePage()
    {
        try {
            // ✅ جلب البيانات مع Caching
            $cacheKey = 'bulk_price_update_page_data';
            
            $data = Cache::remember($cacheKey, 1800, function () {
                return [
                    'allUnits' => $this->getAllUnits(),
                    'activeUnits' => $this->getActiveUnits(),
                    'mostUsedUnits' => $this->getMostUsedUnits(10),
                    'unitsByCategory' => $this->getUnitsByCategory(),
                ];
            });

            // ✅ Log للتشخيص
            Log::info('📦 Bulk Price Update Page Data:', [
                'allUnits_count' => count($data['allUnits']),
                'activeUnits_count' => count($data['activeUnits']),
                'mostUsedUnits_count' => count($data['mostUsedUnits']),
                'unitsByCategory_count' => count($data['unitsByCategory']),
            ]);

            // ✅ Fallback: إذا كانت البيانات فارغة
            if ($this->isDataEmpty($data)) {
                Log::warning('⚠️ No units found! Using default units.');
                $data = $this->getDefaultUnitsData();
            }

            return view('products.bulk-price-update', $data);

        } catch (\Exception $e) {
            Log::error('❌ Error in bulkPriceUpdatePage:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // ✅ إرجاع View مع رسالة خطأ
            return view('products.bulk-price-update', [
                'error' => 'حدث خطأ في تحميل الصفحة: ' . $e->getMessage(),
                'unitsByCategory' => $this->getDefaultUnitsData()['unitsByCategory'],
                'activeUnits' => $this->getDefaultUnitsData()['activeUnits'],
                'mostUsedUnits' => [],
                'allUnits' => [],
            ]);
        }
    }

    /**
     * 🔍 API: جلب التصنيفات حسب الوحدة (محسّن)
     */
    public function getCategoriesByUnit(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
            ]);

            Log::info('🔍 getCategoriesByUnit called:', $validated);

            // ✅ استخدام الـ Service
            $result = $this->pricingService->getCategoriesByBaseUnit($validated['base_unit']);
            
            Log::info('📦 getCategoriesByUnit result:', [
                'success' => $result['success'] ?? false,
                'categories_count' => isset($result['categories']) ? count($result['categories']) : 0
            ]);

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation error in getCategoriesByUnit:', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'categories' => [],
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Error in getCategoriesByUnit:', [
                'unit' => $request->input('base_unit'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل التصنيفات: ' . $e->getMessage(),
                'categories' => [],
            ], 500);
        }
    }

    /**
     * 📦 API: جلب المنتجات حسب الوحدة + التصنيف (محسّن)
     */
    public function getProductsByUnitAndCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
                'category' => 'required|string|max:255',
            ]);

            Log::info('🔍 getProductsByUnitAndCategory called:', $validated);

            // ✅ استخدام الـ Service
            $result = $this->pricingService->getProductsByUnitAndCategory(
                $validated['base_unit'],
                $validated['category']
            );
            
            Log::info('📦 getProductsByUnitAndCategory result:', [
                'success' => $result['success'] ?? false,
                'products_count' => isset($result['products']) ? count($result['products']) : 0
            ]);

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'products' => [],
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Error in getProductsByUnitAndCategory:', [
                'unit' => $request->input('base_unit'),
                'category' => $request->input('category'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل المنتجات: ' . $e->getMessage(),
                'products' => [],
            ], 500);
        }
    }

    /**
     * 💾 تطبيق التحديث الذكي للأسعار (محسّن)
     */
    public function applyBulkPriceUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
                'category' => 'required|string|max:255',
                'base_purchase_price' => 'required|numeric|min:0|max:999999.99',
                'profit_value' => 'required|numeric|min:0|max:999999.99',
                'profit_type' => ['required', Rule::in(['fixed', 'percentage'])],
                'selected_products' => 'required|json',
                'change_reason' => 'nullable|string|max:500',
            ]);

            // ✅ فك تشفير المنتجات
            $selectedProductIds = json_decode($validated['selected_products'], true);
            
            // ✅ التحقق من صحة JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'صيغة JSON غير صحيحة',
                ], 400);
            }

            if (empty($selectedProductIds) || !is_array($selectedProductIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد منتج واحد على الأقل',
                ], 400);
            }

            // ✅ حد أقصى 5000 منتج
            if (count($selectedProductIds) > 5000) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديث أكثر من 5000 منتج دفعة واحدة',
                ], 400);
            }

            Log::info('💾 Starting bulk price update:', [
                'base_unit' => $validated['base_unit'],
                'category' => $validated['category'],
                'products_count' => count($selectedProductIds)
            ]);

            // ✅ تنفيذ التحديث
            $result = $this->pricingService->applyBulkPriceUpdate(
                baseUnit: $validated['base_unit'],
                category: $validated['category'],
                purchasePrice: $validated['base_purchase_price'],
                profitValue: $validated['profit_value'],
                profitType: $validated['profit_type'],
                selectedProductIds: $selectedProductIds,
                changeReason: $validated['change_reason'] ?? 'تحديث جماعي ذكي'
            );
            
            // ✅ مسح الكاش
            Cache::forget('bulk_price_update_page_data');
            Cache::forget("products_pricing_{$validated['base_unit']}_{$validated['category']}");

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Bulk price update failed:', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 📊 معاينة التحديثات قبل التطبيق (محسّن)
     */
    public function previewSmartUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
                'category' => 'required|string|max:255',
                'base_purchase_price' => 'required|numeric|min:0|max:999999.99',
                'profit_value' => 'required|numeric|min:0|max:999999.99',
                'profit_type' => ['required', Rule::in(['fixed', 'percentage'])],
                'selected_products' => 'required|array|min:1|max:5000',
                'selected_products.*' => 'integer|exists:products,id',
            ]);

            Log::info('📊 Preview bulk price update:', [
                'products_count' => count($validated['selected_products'])
            ]);

            $result = $this->pricingService->previewBulkUpdate(
                baseUnit: $validated['base_unit'],
                category: $validated['category'],
                purchasePrice: $validated['base_purchase_price'],
                profitValue: $validated['profit_value'],
                profitType: $validated['profit_type'],
                selectedProductIds: $validated['selected_products']
            );
            
            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Preview failed:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعاينة: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ========================================
    // 🛠️ Helper Methods
    // ========================================

    /**
     * ✅ التحقق من فراغ البيانات
     */
    private function isDataEmpty(array $data): bool
    {
        return empty($data['allUnits']) 
            && empty($data['activeUnits']) 
            && empty($data['mostUsedUnits']) 
            && empty($data['unitsByCategory']);
    }

    /**
     * ✅ الحصول على بيانات افتراضية
     */
    private function getDefaultUnitsData(): array
    {
        return [
            'unitsByCategory' => [
                'weight' => [
                    'label' => 'وحدات الوزن',
                    'units' => [
                        'kg' => 'كيلوجرام',
                        'g' => 'جرام',
                        'ton' => 'طن',
                    ]
                ],
                'volume' => [
                    'label' => 'وحدات الحجم',
                    'units' => [
                        'l' => 'لتر',
                        'ml' => 'ملليلتر',
                    ]
                ],
                'quantity' => [
                    'label' => 'وحدات الكمية',
                    'units' => [
                        'piece' => 'قطعة',
                        'box' => 'صندوق',
                        'carton' => 'كرتونة',
                    ]
                ]
            ],
            'activeUnits' => [
                'kg' => 'كيلوجرام',
                'piece' => 'قطعة',
                'l' => 'لتر',
            ],
            'mostUsedUnits' => [],
            'allUnits' => [
                'kg' => 'كيلوجرام',
                'g' => 'جرام',
                'piece' => 'قطعة',
                'l' => 'لتر',
            ],
        ];
    }

    /**
     * ✅ جلب الوحدات من قاعدة البيانات
     */
    private function getAllUnitsFromDatabase(): array
    {
        try {
            $cacheKey = 'all_units_from_database';

            return Cache::remember($cacheKey, 3600, function () {
                // ✅ محاولة من product_base_pricing
                $unitsFromPricing = DB::table('product_base_pricing')
                    ->select('base_unit', DB::raw('COUNT(*) as count'))
                    ->where('is_active', true)
                    ->groupBy('base_unit')
                    ->orderBy('count', 'desc')
                    ->pluck('base_unit')
                    ->mapWithKeys(function ($unit) {
                        return [$unit => $this->getUnitLabel($unit)];
                    })
                    ->toArray();

                // ✅ Fallback: محاولة من products
                if (empty($unitsFromPricing)) {
                    return DB::table('products')
                        ->select('base_unit', DB::raw('COUNT(*) as count'))
                        ->where('is_active', true)
                        ->whereNotNull('base_unit')
                        ->groupBy('base_unit')
                        ->orderBy('count', 'desc')
                        ->pluck('base_unit')
                        ->mapWithKeys(function ($unit) {
                            return [$unit => $this->getUnitLabel($unit)];
                        })
                        ->toArray();
                }

                return $unitsFromPricing;
            });

        } catch (\Exception $e) {
            Log::error('Error getting units from database', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}