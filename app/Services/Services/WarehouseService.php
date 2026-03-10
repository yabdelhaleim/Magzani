<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\ProductWarehouse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class WarehouseService
{
    public function __construct(
        private InventoryMovementService $movementService
    ) {}

    /**
     * ✅ جلب جميع المخازن مع الإحصائيات - محسّن
     */
    public function getAllWithStats()
    {
        return Warehouse::withStats()
            ->withLowStockCount()
            ->orderBy('name')
            ->paginate(20);
    }

    /**
     * ✅ جلب المخازن النشطة فقط
     */
    public function getActiveWarehouses()
    {
        return Cache::remember('active_warehouses', 300, function () {
            return Warehouse::active()
                ->select('id', 'name', 'code', 'city', 'area')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * ✅ إنشاء مخزن جديد
     */
    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            
            // توليد كود تلقائي إذا لم يتم إدخاله
            if (empty($data['code'])) {
                $data['code'] = $this->generateWarehouseCode($data['name']);
            }

            $warehouse = Warehouse::create($data);

            Log::info('✅ تم إنشاء مخزن جديد', [
                'warehouse_id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
            ]);

            // مسح الكاش
            Cache::forget('active_warehouses');

            return $warehouse;
        });
    }

    /**
     * ✅ تحديث بيانات المخزن
     */
    public function update(int $warehouseId, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouseId, $data) {
            
            $warehouse = Warehouse::findOrFail($warehouseId);
            
            // التحقق من تغيير الحالة
            $statusChanged = isset($data['is_active']) 
                && $data['is_active'] != $warehouse->is_active;

            $warehouse->update($data);

            if ($statusChanged) {
                Log::info('⚠️ تم تغيير حالة المخزن', [
                    'warehouse_id' => $warehouse->id,
                    'old_status' => !$data['is_active'],
                    'new_status' => $data['is_active'],
                ]);
            }

            // مسح الكاش
            Cache::forget('active_warehouses');
            Cache::forget("warehouse_details_{$warehouseId}");

            return $warehouse->fresh();
        });
    }

    /**
     * ✅ حذف المخزن مع التحقق
     */
    public function delete(int $warehouseId): bool
    {
        return DB::transaction(function () use ($warehouseId) {
            
            $warehouse = Warehouse::findOrFail($warehouseId);
            
            // 1. التحقق من عدم وجود منتجات بكميات
            $hasProducts = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('quantity', '>', 0)
                ->exists();
            
            if ($hasProducts) {
                throw new Exception('❌ لا يمكن حذف المخزن لأنه يحتوي على منتجات بكميات');
            }

            // 2. التحقق من عدم وجود تحويلات معلقة
            $hasPendingTransfers = DB::table('warehouse_transfers')
                ->where(function($q) use ($warehouseId) {
                    $q->where('from_warehouse_id', $warehouseId)
                      ->orWhere('to_warehouse_id', $warehouseId);
                })
                ->whereIn('status', ['draft', 'pending', 'in_transit'])
                ->exists();

            if ($hasPendingTransfers) {
                throw new Exception('❌ لا يمكن حذف المخزن لوجود تحويلات معلقة');
            }
            
            // 3. حذف العلاقات الفارغة
            ProductWarehouse::where('warehouse_id', $warehouseId)->delete();
            
            // 4. حذف المخزن (Soft Delete)
            $deleted = $warehouse->delete();

            Log::warning('⚠️ تم حذف مخزن', [
                'warehouse_id' => $warehouseId,
                'name' => $warehouse->name,
            ]);

            // مسح الكاش
            Cache::forget('active_warehouses');
            Cache::forget("warehouse_details_{$warehouseId}");

            return $deleted;
        });
    }

    /**
     * ✅ جلب تفاصيل المخزن الكاملة - محسّن مع Cache
     */
   public function getWarehouseDetails(int $warehouseId): array
{
    $cacheKey = "warehouse_details_{$warehouseId}";

    return Cache::remember($cacheKey, 300, function () use ($warehouseId) {
        
        $warehouse = Warehouse::with([
'productWarehouses.product' => function($q) {
    $q->select('id', 'name', 'code', 'sku', 'unit', 'is_active', 'selling_price'); // ✅ أضف selling_price
},
            'manager:id,name,email',
        ])->findOrFail($warehouseId);

        $products = $warehouse->productWarehouses;
        
        $stats = [
            'total_products'    => $products->count(),
            'active_products'   => $products->filter(fn($p) => $p->product?->is_active)->count(),
            'total_quantity'    => $products->sum('quantity'),
            'reserved_quantity' => $products->sum('reserved_quantity'),
            'available_quantity'=> $products->sum(fn($p) => $p->quantity - ($p->reserved_quantity ?? 0)),
            'low_stock_items'   => $products->filter(fn($p) => $p->quantity <= $p->min_stock)->count(),
            'out_of_stock_items'=> $products->filter(fn($p) => $p->quantity <= 0)->count(),
            
            // ✅ إضافة حساب القيمة الإجمالية
            'total_value' => $products->sum(
                fn($p) => $p->quantity * ($p->product?->selling_price ?? 0)
            ),
        ];

        $lowStockProducts = $products->filter(function($p) {
            return $p->quantity <= $p->min_stock && $p->product?->is_active;
        })->sortBy('quantity')->values();

        return [
            'warehouse' => $warehouse,
            'products'  => $products,
            'stats'     => $stats,
            'lowStock'  => $lowStockProducts,
        ];
    });
}

    /**
     * ✅ إضافة منتج للمخزن
     */
    public function addProduct(int $warehouseId, array $data): ProductWarehouse
    {
        return DB::transaction(function () use ($warehouseId, $data) {
            
            // التحقق من وجود المخزن
            $warehouse = Warehouse::findOrFail($warehouseId);

            if (!$warehouse->isActive()) {
                throw new Exception('❌ المخزن غير نشط');
            }

            // التحقق من عدم وجود المنتج
            $exists = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('product_id', $data['product_id'])
                ->exists();

            if ($exists) {
                throw new Exception('❌ المنتج موجود بالفعل في هذا المخزن');
            }

            // التحقق من نشاط المنتج
            $product = Product::where('id', $data['product_id'])
                ->where('is_active', true)
                ->first();

            if (!$product) {
                throw new Exception('❌ المنتج غير نشط أو غير موجود');
            }

            // إنشاء السجل
            $productWarehouse = ProductWarehouse::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'] ?? 0,
                'min_stock' => $data['min_stock'] ?? 10,
                'max_stock' => $data['max_stock'] ?? null,
                'reserved_quantity' => 0,
            ]);

            // تسجيل كحركة مخزون إذا كانت الكمية أكبر من صفر
            if ($data['quantity'] > 0) {
                $this->movementService->recordMovement([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $data['product_id'],
                    'movement_type' => 'initial_stock',
                    'quantity_change' => $data['quantity'],
                    'notes' => 'إضافة منتج جديد للمخزن',
                    'movement_date' => now(),
                ]);
            }

            Log::info('✅ تمت إضافة منتج للمخزن', [
                'warehouse_id' => $warehouseId,
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'] ?? 0,
            ]);

            // مسح الكاش
            Cache::forget("warehouse_details_{$warehouseId}");
            Cache::forget("warehouse_products_stock_{$warehouseId}");

            return $productWarehouse->load('product');
        });
    }

    /**
     * ✅ تحديث كمية منتج في المخزن
     */
    public function updateProductQuantity(int $warehouseId, int $productId, array $data): ProductWarehouse
    {
        return DB::transaction(function () use ($warehouseId, $productId, $data) {
            
            $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->firstOrFail();

            $oldQuantity = $productWarehouse->quantity;
            $newQuantity = $data['quantity'];
            $difference = $newQuantity - $oldQuantity;

            // تحديث الكمية
            $productWarehouse->update([
                'quantity' => $newQuantity,
                'min_stock' => $data['min_stock'] ?? $productWarehouse->min_stock,
                'max_stock' => $data['max_stock'] ?? $productWarehouse->max_stock,
            ]);

            // تسجيل الحركة إذا تغيرت الكمية
            if ($difference != 0) {
                $movementType = $difference > 0 ? 'adjustment_increase' : 'adjustment_decrease';
                
                $this->movementService->recordMovement([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'movement_type' => $movementType,
                    'quantity_change' => $difference,
                    'notes' => $data['notes'] ?? 'تعديل يدوي للكمية',
                    'movement_date' => now(),
                ]);
            }

            Log::info('✅ تم تحديث كمية منتج', [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ]);

            // مسح الكاش
            Cache::forget("warehouse_details_{$warehouseId}");
            Cache::forget("warehouse_products_stock_{$warehouseId}");

            return $productWarehouse->fresh(['product']);
        });
    }

    /**
     * ✅ حذف منتج من المخزن
     */
    public function removeProduct(int $warehouseId, int $productId): bool
    {
        return DB::transaction(function () use ($warehouseId, $productId) {
            
            $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->firstOrFail();

            // التحقق من عدم وجود كمية
            if ($productWarehouse->quantity > 0) {
                throw new Exception('❌ لا يمكن حذف المنتج لوجود كميات في المخزن');
            }

            // التحقق من عدم وجود كميات محجوزة
            if ($productWarehouse->reserved_quantity > 0) {
                throw new Exception('❌ لا يمكن حذف المنتج لوجود كميات محجوزة');
            }

            $deleted = $productWarehouse->delete();

            Log::info('✅ تم حذف منتج من المخزن', [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
            ]);

            // مسح الكاش
            Cache::forget("warehouse_details_{$warehouseId}");
            Cache::forget("warehouse_products_stock_{$warehouseId}");

            return $deleted;
        });
    }

    /**
     * ✅ تقرير المخزون المنخفض
     */
    public function getLowStockReport(int $warehouseId): array
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        
        $lowStockProducts = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->whereRaw('quantity <= min_stock')
            ->where('quantity', '>', 0)
            ->with(['product' => function($q) {
                $q->select('id', 'name', 'code', 'sku', 'unit')
                  ->where('is_active', true);
            }])
            ->orderBy('quantity', 'asc')
            ->get()
            ->filter(fn($p) => $p->product); // فقط المنتجات النشطة

        $outOfStock = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->where('quantity', '<=', 0)
            ->with(['product' => function($q) {
                $q->select('id', 'name', 'code', 'sku', 'unit')
                  ->where('is_active', true);
            }])
            ->get()
            ->filter(fn($p) => $p->product);

        return [
            'warehouse' => $warehouse,
            'lowStockProducts' => $lowStockProducts,
            'outOfStock' => $outOfStock,
            'stats' => [
                'low_stock_count' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStock->count(),
            ],
        ];
    }

    /**
     * ✅ البحث عن منتجات في المخزن
     */
    public function searchProducts(int $warehouseId, string $search)
    {
        return ProductWarehouse::where('warehouse_id', $warehouseId)
            ->whereHas('product', function ($query) use ($search) {
                $query->where('is_active', true)
                      ->where(function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                      });
            })
            ->with(['product' => function($q) {
                $q->select('id', 'name', 'code', 'sku', 'barcode', 'unit');
            }])
            ->limit(50)
            ->get();
    }

    /**
     * ✅ تقرير شامل للمخزن
     */
    public function getWarehouseReport(int $warehouseId, array $filters = []): array
    {
        $warehouse = Warehouse::with('manager:id,name')->findOrFail($warehouseId);

        $query = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->with(['product' => function($q) {
                $q->select('id', 'name', 'code', 'sku', 'unit', 'category_id')
                  ->where('is_active', true);
            }]);

        // الفلاتر
        if (!empty($filters['category_id'])) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $filters['category_id']));
        }

        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low':
                    $query->whereRaw('quantity <= min_stock');
                    break;
                case 'out':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'normal':
                    $query->whereRaw('quantity > min_stock');
                    break;
            }
        }

        $products = $query->orderBy('quantity', 'asc')->get()
            ->filter(fn($p) => $p->product); // فقط المنتجات النشطة

        return [
            'warehouse' => $warehouse,
            'products' => $products,
            'summary' => [
                'total_products' => $products->count(),
                'total_quantity' => $products->sum('quantity'),
                'total_reserved' => $products->sum('reserved_quantity'),
                'total_available' => $products->sum(fn($p) => $p->quantity - ($p->reserved_quantity ?? 0)),
            ],
        ];
    }

    /**
     * ✅ توليد كود تلقائي للمخزن
     */
    private function generateWarehouseCode(string $name): string
    {
        // أخذ أول 3 أحرف من الاسم
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        
        // رقم تسلسلي
        $lastWarehouse = Warehouse::where('code', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        $sequence = $lastWarehouse 
            ? intval(substr($lastWarehouse->code, -4)) + 1 
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ✅ إحصائيات عامة للمخازن
     */
    public function getGeneralStats(): array
    {
        return Cache::remember('warehouses_general_stats', 300, function () {
            
            $totalWarehouses = Warehouse::count();
            $activeWarehouses = Warehouse::active()->count();
            
            $totalProducts = ProductWarehouse::sum('quantity');
            $totalReserved = ProductWarehouse::sum('reserved_quantity');
            
            $lowStockCount = ProductWarehouse::whereRaw('quantity <= min_stock')
                ->where('quantity', '>', 0)
                ->count();

            $outOfStockCount = ProductWarehouse::where('quantity', '<=', 0)->count();

            return [
                'total_warehouses' => $totalWarehouses,
                'active_warehouses' => $activeWarehouses,
                'inactive_warehouses' => $totalWarehouses - $activeWarehouses,
                'total_products_quantity' => (float) $totalProducts,
                'total_reserved_quantity' => (float) $totalReserved,
                'total_available_quantity' => (float) ($totalProducts - $totalReserved),
                'low_stock_items' => $lowStockCount,
                'out_of_stock_items' => $outOfStockCount,
            ];
        });
    }
}