<?php

namespace App\Models;

use App\Traits\ProductStockManagement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    use ProductStockManagement;

    protected $fillable = [
        'name',
        'code',
        'sku',
        'barcode',
        'description',
        'category_id',
        'brand_id',
        'unit_id',
        'base_unit',
        'base_unit_label',
        'purchase_price',
        'selling_price',
        'min_selling_price',
        'wholesale_price',
        'tax_rate',
        'default_discount',
        'profit_margin',
        'stock_alert_quantity',
        'reorder_level',
        'reorder_quantity',
        'min_stock',
        'max_stock',
        'weight',
        'dimensions',
        'image',
        'is_active',
        'is_featured',
        'has_expiry',
        'track_serial',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'category',
        'notes',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'default_discount' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'stock_alert_quantity' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'reorder_quantity' => 'decimal:3',
        'min_stock' => 'decimal:3',
        'max_stock' => 'decimal:3',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_expiry' => 'boolean',
        'track_serial' => 'boolean',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function basePricing(): HasOne
    {
        return $this->hasOne(ProductBasePricing::class, 'product_id')
            ->where('is_active', true)
            ->where('is_current', true)
            ->latest();
    }

    public function allBasePricing(): HasMany
    {
        return $this->hasMany(ProductBasePricing::class, 'product_id')
            ->orderBy('created_at', 'desc');
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class, 'product_id')
            ->orderBy('changed_at', 'desc');
    }

    public function sellingUnits(): HasMany
    {
        return $this->hasMany(ProductSellingUnit::class)->ordered();
    }

    public function defaultSellingUnit(): HasOne
    {
        return $this->hasOne(ProductSellingUnit::class)
            ->where('is_default', true);
    }

    public function baseSellingUnit(): HasOne
    {
        return $this->hasOne(ProductSellingUnit::class)
            ->where('is_base', true);
    }

    public function activeSellingUnits(): HasMany
    {
        return $this->hasMany(ProductSellingUnit::class)
            ->where('is_active', true)
            ->ordered();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->using(ProductWarehouse::class)
            ->withPivot([
                'quantity',
                'reserved_quantity',
                'available_quantity',
                'min_stock',
                'average_cost',
                'last_count_quantity',
                'last_count_date',
                'adjustment_total',
            ])
            ->withTimestamps();
    }

    public function salesInvoiceItems(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function purchaseInvoiceItems(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function salesReturnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function stockCountItems(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    /* ===========================
     * 📊 STOCK ACCESSORS (جديد)
     * =========================== */

    /**
     * ✅ إجمالي المخزون في كل المخازن
     */
    public function getTotalStockAttribute(): float
    {
        if ($this->relationLoaded('warehouses')) {
            return (float) $this->warehouses->sum('pivot.quantity');
        }
        
        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->sum('quantity');
    }

    /**
     * ✅ إجمالي الكمية المتاحة (غير المحجوزة)
     */
    public function getTotalAvailableAttribute(): float
    {
        if ($this->relationLoaded('warehouses')) {
            return (float) $this->warehouses->sum('pivot.available_quantity');
        }
        
        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->sum(DB::raw('GREATEST(0, quantity - COALESCE(reserved_quantity, 0))'));
    }

    /**
     * ✅ إجمالي الكمية المحجوزة
     */
    public function getTotalReservedAttribute(): float
    {
        if ($this->relationLoaded('warehouses')) {
            return (float) $this->warehouses->sum('pivot.reserved_quantity');
        }
        
        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->sum('reserved_quantity');
    }

    /**
     * ✅ حالة المخزون (نفذ / منخفض / متوفر)
     */
    public function getStockStatusAttribute(): string
    {
        $total = $this->total_stock;
        $alert = $this->stock_alert_quantity ?? 10;
        
        if ($total == 0) return 'نفذ';
        if ($total <= $alert) return 'منخفض';
        return 'متوفر';
    }

    /**
     * ✅ لون حالة المخزون (للعرض)
     */
    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'نفذ' => 'red',
            'منخفض' => 'yellow',
            'متوفر' => 'green',
            default => 'gray'
        };
    }

    /**
     * ✅ هل المخزون منخفض؟
     */
    public function getIsLowStockAttribute(): bool
    {
        $total = $this->total_stock;
        $alert = $this->stock_alert_quantity ?? 10;
        return $total > 0 && $total <= $alert;
    }

    /**
     * ✅ هل المخزون نفذ؟
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->total_stock == 0;
    }

    /* ===========================
     * 📊 OTHER ACCESSORS
     * =========================== */

    public function getSellingUnitsWithPricesAttribute(): array
    {
        if (!$this->relationLoaded('sellingUnits')) {
            return [];
        }

        return $this->sellingUnits->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->unit_name,
                'code' => $unit->unit_code,
                'label' => $unit->label,
                'conversion_factor' => $unit->conversion_factor,
                'selling_price' => $unit->selling_price,
                'purchase_price' => $unit->purchase_price,
                'is_default' => $unit->is_default,
                'is_base' => $unit->is_base,
                'is_active' => $unit->is_active,
            ];
        })->toArray();
    }

    /* ===========================
     * 🎯 SCOPES
     * =========================== */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * ✅ Scope للمنتجات منخفضة المخزون
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('warehouses', function($q) {
            $q->whereRaw('product_warehouse.quantity <= product_warehouse.min_stock')
              ->whereRaw('product_warehouse.quantity > 0');
        });
    }

    /**
     * ✅ Scope للمنتجات النافذة
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereDoesntHave('warehouses', function($q) {
            $q->where('product_warehouse.quantity', '>', 0);
        });
    }

    /* ===========================
     * 🛠️ HELPER METHODS
     * =========================== */

    public function ensureBaseSellingUnit(): ProductSellingUnit
    {
        return $this->sellingUnits()->firstOrCreate(
            ['is_base' => true],
            [
                'unit_name' => $this->base_unit_label ?? 'وحدة',
                'unit_code' => $this->base_unit ?? 'unit',
                'quantity_in_base_unit' => 1.0,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 0,
            ]
        );
    }

    public function getSellingUnit(int $unitId): ?ProductSellingUnit
    {
        if ($this->relationLoaded('sellingUnits')) {
            return $this->sellingUnits->firstWhere('id', $unitId);
        }

        return $this->sellingUnits()->find($unitId);
    }

    public function getSellingUnitByCode(string $code): ?ProductSellingUnit
    {
        if ($this->relationLoaded('sellingUnits')) {
            return $this->sellingUnits->firstWhere('unit_code', $code);
        }

        return $this->sellingUnits()->byCode($code)->first();
    }

    /**
     * ✅ الحصول على كمية المنتج في مخزن محدد
     */
    public function getQuantityInWarehouse(int $warehouseId): float
    {
        if ($this->relationLoaded('warehouses')) {
            $warehouse = $this->warehouses->firstWhere('id', $warehouseId);
            return $warehouse ? (float) $warehouse->pivot->quantity : 0;
        }

        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    /**
     * ✅ الحصول على الكمية المتاحة في مخزن محدد
     */
    public function getAvailableInWarehouse(int $warehouseId): float
    {
        if ($this->relationLoaded('warehouses')) {
            $warehouse = $this->warehouses->firstWhere('id', $warehouseId);
            return $warehouse ? (float) $warehouse->pivot->available_quantity : 0;
        }

        return (float) DB::table('product_warehouse')
            ->where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->value(DB::raw('GREATEST(0, quantity - COALESCE(reserved_quantity, 0))')) ?? 0;
    }

    /* ===========================
     * 🔄 EVENTS
     * =========================== */

    protected static function boot()
    {
        parent::boot();

        // ✅ حذف العلاقات عند حذف المنتج
        static::deleting(function ($product) {
            // حذف الوحدات
            $product->sellingUnits()->delete();
            
            // حذف علاقات المخازن
            $product->warehouses()->detach();
        });
    }
}