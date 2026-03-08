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
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * عرض قائمة المنتجات
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with([
                'sellingUnits' => fn($q) => $q->select('id', 'product_id', 'unit_name', 'unit_code', 'is_default')
                    ->where('is_active', true)
                    ->orderBy('display_order'),
                // ✅ warehouses مع pivot عشان يظهر كل مخزن على حدة في الجدول
                'warehouses' => fn($q) => $q->select('warehouses.id', 'warehouses.name')
                    ->withPivot('quantity'),
            ])
            ->withSum('warehouses as total_stock', 'quantity')
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

        if ($request->input('low_stock')) {
            $query->whereHas('warehouses', function ($q) {
                $q->whereRaw('product_warehouse.quantity <= product_warehouse.min_stock')
                  ->whereRaw('product_warehouse.quantity > 0');
            });
        }

        $sortBy        = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        // ✅ لو الترتيب بالمخزون نتعامل معاه بشكل خاص
        if ($sortBy === 'total_stock') {
            $query->orderBy('total_stock', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage  = min($request->input('per_page', 20), 100);
        $products = $query->paginate($perPage)->withQueryString();

        return view('products.index', compact('products'));
    }

    /**
     * عرض صفحة إضافة منتج جديد
     */
    public function create()
    {
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
     * حفظ منتج سريع (من داخل الفاتورة)
     */
    public function quickStore(Request $request)
    {
        try {
            // التحقق من البيانات
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'base_unit' => 'required|string|max:50',
                'base_unit_type' => 'nullable|string|max:50',
                'base_unit_label' => 'nullable|string|max:100',
                'purchase_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
            ]);

            // تحديد نوع الوحدة
            $unitTypeMap = [
                'piece' => 'count',
                'kg' => 'weight',
                'ton' => 'weight',
                'gram' => 'weight',
                'meter' => 'length',
                'cm' => 'length',
                'liter' => 'volume',
                'ml' => 'volume',
                'box' => 'count',
                'pack' => 'count',
            ];
            $baseUnitType = $validated['base_unit_type'] ?? $unitTypeMap[$validated['base_unit']] ?? 'count';
            $baseUnitLabel = $validated['base_unit_label'] ?? $validated['base_unit'];

            // إنشاء المنتج
            $product = Product::create([
                'name' => $validated['name'],
                'code' => 'PRD-' . strtoupper(uniqid()),
                'sku' => null,
                'barcode' => null,
                'category' => 'غير مصنف',
                'base_unit' => $validated['base_unit'],
                'base_unit_label' => $baseUnitLabel,
                'purchase_price' => $validated['purchase_price'] ?? 0,
                'selling_price' => $validated['selling_price'] ?? 0,
                'is_active' => true,
            ]);

            // إنشاء الوحدة الأساسية
            $baseUnit = $product->baseunit()->create([
                'product_code' => $product->code,
                'base_unit_type' => $baseUnitType,
                'base_unit_code' => $validated['base_unit'],
                'base_unit_label' => $baseUnitLabel,
                'base_unit_weight_kg' => $this->getUnitWeight($validated['base_unit']),
                'base_purchase_price' => $validated['purchase_price'] ?? 0,
                'base_selling_price' => $validated['selling_price'] ?? 0,
                'is_active' => true,
                'effective_from' => now(),
            ]);

            // إنشاء وحدة بيع افتراضية
            $product->sellingUnits()->create([
                'base_unit_id' => $baseUnit->id,
                'unit_name' => $baseUnitLabel,
                'unit_code' => $validated['base_unit'],
                'unit_label' => $baseUnitLabel,
                'conversion_factor' => 1,
                'quantity_in_base_unit' => 1,
                'unit_purchase_price' => $validated['purchase_price'] ?? 0,
                'unit_selling_price' => $validated['selling_price'] ?? 0,
                'is_base' => true,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المنتج بنجاح',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'base_unit_type' => $baseUnitType,
                    'base_unit_code' => $validated['base_unit'],
                    'base_unit_label' => $baseUnitLabel,
                    'base_unit_purchase_price' => (float)($validated['purchase_price'] ?? 0),
                    'selling_units' => [[
                        'id' => $product->sellingUnits()->first()->id,
                        'unit_code' => $validated['base_unit'],
                        'unit_label' => $baseUnitLabel,
                        'conversion_factor' => 1,
                        'purchase_price' => (float)($validated['purchase_price'] ?? 0),
                        'selling_price' => (float)($validated['selling_price'] ?? 0),
                        'is_default' => true
                    ]]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Quick product creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * تحويل الوزن لكل وحدة
     */
    private function getUnitWeight(string $unit): float
    {
        $weights = [
            'ton' => 1000,
            'kg' => 1,
            'gram' => 0.001,
            'mg' => 0.000001,
        ];
        return $weights[$unit] ?? 1;
    }

    /**
     * حفظ منتج جديد
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return redirect()
                ->route('products.index')
                ->with('success', "✅ تم إضافة المنتج '{$product->name}' بنجاح!\nالكود: {$product->code}\nSKU: {$product->sku}");

        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'data'  => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', '❌ حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل المنتج
     */
    public function show(Product $product)
    {
        $product->load([
            'sellingUnits'  => fn($q) => $q->ordered(),
            // ✅ تحميل warehouses مع pivot data كاملة
            'warehouses'    => fn($q) => $q->select('warehouses.id', 'warehouses.name')
                                           ->withPivot(['quantity', 'reserved_quantity', 'available_quantity', 'min_stock']),
            'priceHistory'  => fn($q) => $q->latest()->limit(10),
        ]);

        $stockStats = $this->productService->getStockStatistics($product);

        return view('products.show', compact('product', 'stockStats'));
    }

    /**
     * عرض صفحة التعديل
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
 * @hideFromAPIDocumentation
 */

public function update(UpdateProductRequest $request, Product $product)
{
    if (app()->runningInConsole()) {
        return response()->json(['message' => 'Documentation mode']);
    }

    try {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', "✅ تم تحديث المنتج '{$product->name}' بنجاح!");

    } catch (\Exception $e) {
        Log::error('Product update failed', [
            'product_id' => $product->id,
            'error'      => $e->getMessage(),
            'data'       => $request->validated(),
        ]);

        return back()
            ->withInput()
            ->with('error', '❌ حدث خطأ: ' . $e->getMessage());
    }
}

    /**
     * حذف المنتج
     */
    public function destroy(Product $product)
    {
        try {
            $productName = $product->name;
            $this->productService->deleteProduct($product);

            return redirect()
                ->route('products.index')
                ->with('success', "✅ تم حذف المنتج '{$productName}' بنجاح!");

        } catch (\Exception $e) {
            Log::error('Product deletion failed', [
                'product_id' => $product->id,
                'error'      => $e->getMessage()
            ]);

            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * صفحة التحديث الجماعي للأسعار
     */
    public function bulkPriceUpdatePage()
    {
        $unitsByCategory = $this->productService->getUnitsByCategory();

        return view('products.bulk-price-update', compact('unitsByCategory'));
    }

    /**
     * تطبيق التحديث الجماعي للأسعار
     */
    public function applyBulkPriceUpdate(BulkPriceUpdateRequest $request)
    {
        try {
            $result = $this->productService->bulkUpdatePrices($request->validated());

            return response()->json([
                'success' => true,
                'message' => "✅ تم تحديث {$result['updated_count']} منتج بنجاح!",
                'data'    => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk price update failed', [
                'error' => $e->getMessage(),
                'data'  => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: جلب التصنيفات حسب الوحدة
     */
    public function getCategoriesByUnit(Request $request)
    {
        $request->validate(['base_unit' => 'required|string|max:50']);

        try {
            $categories = $this->productService->getCategoriesByUnit($request->base_unit);

            return response()->json(['success' => true, 'categories' => $categories]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * AJAX: جلب المنتجات حسب الوحدة والتصنيف
     */
    public function getProductsByUnitAndCategory(Request $request)
    {
        $request->validate([
            'base_unit' => 'required|string|max:50',
            'category'  => 'required|string|max:255'
        ]);

        try {
            $products = $this->productService->getProductsByUnitAndCategory(
                $request->base_unit,
                $request->category
            );

            return response()->json([
                'success'  => true,
                'products' => $products,
                'count'    => count($products)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * AJAX: اقتراحات التسعير
     */
    public function getSuggestedPricing(Request $request)
    {
        $request->validate([
            'base_unit' => 'required|string|max:50',
            'category'  => 'nullable|string|max:255'
        ]);

        try {
            $suggestions = $this->productService->getSuggestedPricing(
                $request->base_unit,
                $request->category
            );

            return response()->json(['success' => true, 'suggestions' => $suggestions]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * AJAX: البحث السريع
     */
    public function quickSearch(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        try {
            $products = Product::search($request->q)
                ->select('id', 'name', 'code', 'sku', 'barcode', 'selling_price')
                ->active()
                ->limit(10)
                ->get();

            return response()->json(['success' => true, 'products' => $products]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * نقل مخزون
     */
    public function transferStock(Request $request, Product $product)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity'          => 'required|numeric|min:0.001',
            'notes'             => 'nullable|string|max:500'
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
                'error'      => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * تصدير المنتجات CSV
     */
    public function export(Request $request)
    {
        try {
            // ✅ withSum بدل with('warehouses') لتجنب نفس مشكلة الـ pivot
            $products = Product::withSum('warehouses as total_stock', 'quantity')
                ->select('id', 'name', 'code', 'sku', 'category', 'selling_price', 'purchase_price')
                ->get();

            $filename = 'products_' . date('Y-m-d_H-i-s') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($products) {
                $file = fopen('php://output', 'w');
                // BOM لدعم العربي في Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['الكود', 'الاسم', 'SKU', 'التصنيف', 'سعر البيع', 'سعر الشراء', 'المخزون']);

                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->code,
                        $product->name,
                        $product->sku,
                        $product->category,
                        $product->selling_price,
                        $product->purchase_price,
                        // ✅ total_stock جاي من withSum مباشرة
                        (float) ($product->total_stock ?? 0),
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
