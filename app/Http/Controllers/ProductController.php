<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\BulkPriceUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * ✅ عرض قائمة المنتجات (محسّن)
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with([
                'sellingUnits' => fn($q) => $q->select('id', 'product_id', 'unit_name', 'unit_code', 'is_default')
                    ->where('is_active', true)
                    ->orderBy('display_order'),
                'warehouses' => fn($q) => $q->select(
                    'warehouses.id', 
                    'warehouses.name',
                    'product_warehouse.quantity',
                    'product_warehouse.reserved_quantity',
                    'product_warehouse.available_quantity'
                )
            ])
            ->select([
                'id', 'name', 'code', 'sku', 'barcode', 'category', 
                'base_unit', 'base_unit_label', 'selling_price', 
                'purchase_price', 'is_active', 'image', 'stock_alert_quantity'
            ]);

        // الفلاتر
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($category = $request->input('category')) {
            $query->byCategory($category);
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // ✅ فلتر المخزون المنخفض
        if ($request->input('low_stock')) {
            $query->whereHas('warehouses', function($q) {
                $q->whereRaw('product_warehouse.quantity <= product_warehouse.min_stock');
            });
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = min($request->input('per_page', 20), 100);
        $products = $query->paginate($perPage)->withQueryString();

        return view('products.index', compact('products'));
    }

    /**
     * ✅ عرض صفحة إضافة منتج جديد
     */
    public function create()
    {
        // ✅ جلب المخازن النشطة فقط (مع cache)
        $warehouses = Cache::remember('active_warehouses', 1800, function () {
            return Warehouse::select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
        
        $unitsByCategory = $this->productService->getUnitsByCategory();

        return view('products.create', compact('warehouses', 'unitsByCategory'));
    }

    /**
     * ✅ حفظ منتج جديد
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            // ✅ رسالة نجاح واضحة
            return redirect()
                ->route('products.index')
                ->with('success', "✅ تم إضافة المنتج '{$product->name}' بنجاح!\nالكود: {$product->code}\nSKU: {$product->sku}");

        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', '❌ حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * ✅ عرض تفاصيل المنتج (محسّن)
     */
    public function show(Product $product)
    {
        // ✅ Eager loading محسّن
        $product->load([
            'sellingUnits' => fn($q) => $q->ordered(),
            'warehouses' => fn($q) => $q->select('warehouses.id', 'warehouses.name'),
            'priceHistory' => fn($q) => $q->orderBy('changed_at', 'desc')->limit(10),
        ]);

        // ✅ إحصائيات المخزون
        $stockStats = $this->productService->getStockStatistics($product);
        
        return view('products.show', compact('product', 'stockStats'));
    }

    /**
     * ✅ عرض صفحة التعديل
     */
    public function edit(Product $product)
    {
        $warehouses = Cache::remember('active_warehouses', 1800, function () {
            return Warehouse::select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
        
        $unitsByCategory = $this->productService->getUnitsByCategory();

        return view('products.edit', compact('product', 'warehouses', 'unitsByCategory'));
    }

    /**
     * ✅ تحديث المنتج
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $this->productService->updateProduct($product, $request->validated());

            return redirect()
                ->route('products.index')
                ->with('success', "✅ تم تحديث المنتج '{$product->name}' بنجاح!");

        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);
            
            return back()
                ->withInput()
                ->with('error', '❌ حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * ✅ حذف المنتج
     */
    public function destroy(Product $product)
    {
        // ✅ Security: Check authorization
        $this->authorize('delete', $product);
        try {
            $productName = $product->name;
            $this->productService->deleteProduct($product);
            
            return redirect()
                ->route('products.index')
                ->with('success', "✅ تم حذف المنتج '{$productName}' بنجاح!");

        } catch (\Exception $e) {
            Log::error('Product deletion failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * ✅ صفحة التحديث الجماعي للأسعار
     */
    public function bulkPriceUpdatePage()
    {
        $unitsByCategory = $this->productService->getUnitsByCategory();

        return view('products.bulk-price-update', compact('unitsByCategory'));
    }

    /**
     * ✅ تطبيق التحديث الجماعي للأسعار
     */
    public function applyBulkPriceUpdate(BulkPriceUpdateRequest $request)
    {
        try {
            $result = $this->productService->bulkUpdatePrices($request->validated());

            return response()->json([
                'success' => true,
                'message' => "✅ تم تحديث {$result['updated_count']} منتج بنجاح!",
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk price update failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '❌ حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ AJAX: جلب التصنيفات حسب الوحدة
     */
    public function getCategoriesByUnit(Request $request)
    {
        $request->validate([
            'base_unit' => 'required|string|max:50'
        ]);

        try {
            $categories = $this->productService->getCategoriesByUnit($request->base_unit);

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ✅ AJAX: جلب المنتجات حسب الوحدة والتصنيف
     */
    public function getProductsByUnitAndCategory(Request $request)
    {
        $request->validate([
            'base_unit' => 'required|string|max:50',
            'category' => 'required|string|max:255'
        ]);

        try {
            $products = $this->productService->getProductsByUnitAndCategory(
                $request->base_unit,
                $request->category
            );

            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => count($products)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ✅ AJAX: اقتراحات التسعير الذكية
     */
    public function getSuggestedPricing(Request $request)
    {
        $request->validate([
            'base_unit' => 'required|string|max:50',
            'category' => 'nullable|string|max:255'
        ]);

        try {
            $suggestions = $this->productService->getSuggestedPricing(
                $request->base_unit,
                $request->category
            );

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ✅ AJAX: البحث السريع
     */
    public function quickSearch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        try {
            $products = Product::search($request->q)
                ->select('id', 'name', 'code', 'sku', 'barcode', 'selling_price')
                ->active()
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'products' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ✅ نقل مخزون
     */
    public function transferStock(Request $request, Product $product)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $this->productService->transferStock(
                $product,
                $request->from_warehouse_id,
                $request->to_warehouse_id,
                $request->quantity,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => "✅ تم نقل {$request->quantity} من المنتج بنجاح!"
            ]);

        } catch (\Exception $e) {
            Log::error('Stock transfer failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * ✅ تصدير المنتجات (Excel/CSV)
     */
    public function export(Request $request)
    {
        try {
            // يمكنك استخدام Laravel Excel أو تصدير يدوي
            $products = Product::with(['sellingUnits', 'warehouses'])
                ->select('id', 'name', 'code', 'sku', 'category', 'selling_price', 'purchase_price')
                ->get();

            // مثال بسيط لـ CSV
            $filename = 'products_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($products) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, ['الكود', 'الاسم', 'SKU', 'التصنيف', 'سعر البيع', 'سعر الشراء', 'المخزون']);
                
                // Data
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->code,
                        $product->name,
                        $product->sku,
                        $product->category,
                        $product->selling_price,
                        $product->purchase_price,
                        $product->total_stock ?? 0,
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Product export failed', ['error' => $e->getMessage()]);
            
            return back()->with('error', '❌ فشل التصدير: ' . $e->getMessage());
        }
    }
}