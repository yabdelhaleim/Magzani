<?php
// app/Http/Controllers/ProductPricingController.php

namespace App\Http\Controllers;

use App\Services\ProductPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductPricingController extends Controller
{
    public function __construct(
        private ProductPricingService $pricingService
    ) {}

    /**
     * 📋 صفحة التحديث الجماعي
     */
    public function bulkPriceUpdatePage()
    {
        $units = $this->getAvailableUnits();
        
        return view('products.bulk-price-update', compact('units'));
    }

    /**
     * 🔍 جلب التصنيفات حسب الوحدة
     */
    public function getCategoriesByUnit(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
            ]);

            $result = $this->pricingService->getCategoriesByBaseUnit($validated['base_unit']);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('خطأ في getCategoriesByUnit', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
                'categories' => [],
            ], 500);
        }
    }

    /**
     * 📦 جلب المنتجات
     */
    public function getProductsByUnitAndCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
                'category' => 'required|string|max:255',
            ]);

            $result = $this->pricingService->getProductsByUnitAndCategory(
                $validated['base_unit'],
                $validated['category']
            );
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
                'products' => [],
            ], 500);
        }
    }

    /**
     * 💾 تطبيق التحديث الجماعي
     */
    public function applyBulkPriceUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'base_unit' => 'required|string|max:50',
                'category' => 'required|string|max:255',
                'base_purchase_price' => 'required|numeric|min:0',
                'profit_value' => 'required|numeric|min:0',
                'profit_type' => ['required', Rule::in(['fixed', 'percentage'])],
                'selected_products' => 'required|json',
                'change_reason' => 'nullable|string|max:500',
            ]);

            $selectedProductIds = json_decode($validated['selected_products'], true);
            
            if (empty($selectedProductIds) || !is_array($selectedProductIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد منتج واحد على الأقل',
                ], 400);
            }

            $result = $this->pricingService->applyBulkPriceUpdate(
                baseUnit: $validated['base_unit'],
                category: $validated['category'],
                purchasePrice: $validated['base_purchase_price'],
                profitValue: $validated['profit_value'],
                profitType: $validated['profit_type'],
                selectedProductIds: $selectedProductIds,
                changeReason: $validated['change_reason'] ?? null
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('فشل التحديث الجماعي', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 📋 الوحدات المتاحة
     */
    private function getAvailableUnits(): array
    {
        return [
            'ton' => 'طن',
            'kg' => 'كيلوجرام',
            'g' => 'جرام',
            'piece' => 'قطعة',
            'liter' => 'لتر',
            'meter' => 'متر',
            'box' => 'صندوق',
            'carton' => 'كرتونة',
        ];
    }
}