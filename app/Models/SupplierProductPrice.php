<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * 🎯 Model: SupplierProductPrice
 * 
 * @property int $id
 * @property int $supplier_id
 * @property int $product_id
 * @property string $base_unit
 * @property float $price
 * @property string $currency
 * @property float $min_order_quantity
 * @property int $delivery_time_days
 * @property \Carbon\Carbon $valid_from
 * @property \Carbon\Carbon|null $valid_to
 * @property bool $is_active
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SupplierProductPrice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'supplier_product_prices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'supplier_id',
        'product_id',
        'base_unit',
        'price',
        'currency',
        'min_order_quantity',
        'delivery_time_days',
        'valid_from',
        'valid_to',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'min_order_quantity' => 'decimal:2',
        'delivery_time_days' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    // ========================================
    // 🔗 Relationships
    // ========================================

    /**
     * العلاقة مع المنتج
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * العلاقة مع المورد (إذا كان لديك جدول suppliers)
     * ملاحظة: قد تحتاج لإنشاء model للمورد
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // ========================================
    // 🔍 Scopes
    // ========================================

    /**
     * Scope للحصول على الأسعار النشطة فقط
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للحصول على أسعار مورد معين
     */
    public function scopeForSupplier(Builder $query, int $supplierId): Builder
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope للحصول على أسعار منتج معين
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope للحصول على الأسعار الصالحة في تاريخ معين
     */
    public function scopeValidOn(Builder $query, Carbon $date = null): Builder
    {
        $date = $date ?? now();
        
        return $query->where('valid_from', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('valid_to')
                          ->orWhere('valid_to', '>=', $date);
                    });
    }

    /**
     * Scope للحصول على السعر الحالي
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->active()->validOn();
    }

    /**
     * Scope للأسعار منتهية الصلاحية
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('valid_to')
                    ->where('valid_to', '<', now());
    }

    /**
     * Scope للأسعار المستقبلية
     */
    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('valid_from', '>', now());
    }

    /**
     * Scope للبحث بالعملة
     */
    public function scopeByCurrency(Builder $query, string $currency): Builder
    {
        return $query->where('currency', $currency);
    }

    /**
     * Scope للترتيب حسب السعر (الأقل أولاً)
     */
    public function scopeCheapestFirst(Builder $query): Builder
    {
        return $query->orderBy('price');
    }

    /**
     * Scope للترتيب حسب وقت التوصيل (الأسرع أولاً)
     */
    public function scopeFastestDelivery(Builder $query): Builder
    {
        return $query->orderBy('delivery_time_days');
    }

    // ========================================
    // 🛠️ Helper Methods
    // ========================================

    /**
     * التحقق من صلاحية السعر في تاريخ معين
     */
    public function isValidOn(Carbon $date = null): bool
    {
        $date = $date ?? now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from > $date) {
            return false;
        }

        if ($this->valid_to && $this->valid_to < $date) {
            return false;
        }

        return true;
    }

    /**
     * حساب السعر الإجمالي لكمية معينة
     */
    public function calculateTotalPrice(float $quantity): float
    {
        return $quantity * $this->price;
    }

    /**
     * التحقق من تحقيق الحد الأدنى للطلب
     */
    public function meetsMinimumOrder(float $quantity): bool
    {
        return $quantity >= $this->min_order_quantity;
    }

    /**
     * حساب تاريخ التسليم المتوقع
     */
    public function estimatedDeliveryDate(Carbon $orderDate = null): Carbon
    {
        $orderDate = $orderDate ?? now();
        return $orderDate->copy()->addDays($this->delivery_time_days);
    }

    /**
     * الحصول على السعر بالعملة المحلية (مع التحويل)
     */
    public function getPriceInLocalCurrency(string $localCurrency = 'EGP', ?float $exchangeRate = null): float
    {
        if ($this->currency === $localCurrency) {
            return $this->price;
        }

        if ($exchangeRate === null) {
            // يمكنك هنا الحصول على سعر الصرف من جدول أو API
            $exchangeRate = 1; // قيمة افتراضية
        }

        return $this->price * $exchangeRate;
    }

    /**
     * مقارنة مع سعر مورد آخر
     */
    public function compareWith(self $otherPrice): array
    {
        return [
            'price_difference' => $this->price - $otherPrice->price,
            'price_percentage' => $otherPrice->price != 0 
                ? (($this->price - $otherPrice->price) / $otherPrice->price) * 100 
                : 0,
            'delivery_difference' => $this->delivery_time_days - $otherPrice->delivery_time_days,
            'min_order_difference' => $this->min_order_quantity - $otherPrice->min_order_quantity,
            'is_cheaper' => $this->price < $otherPrice->price,
            'is_faster' => $this->delivery_time_days < $otherPrice->delivery_time_days,
        ];
    }

    /**
     * تعطيل السعر
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        $this->valid_to = now();
        return $this->save();
    }

    // ========================================
    // 🎨 Accessors & Mutators
    // ========================================

    /**
     * Accessor للتحقق من صلاحية السعر حالياً
     */
    public function getIsCurrentAttribute(): bool
    {
        return $this->isValidOn();
    }

    /**
     * Accessor لحالة السعر
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->valid_from > now()) {
            return 'future';
        }

        if ($this->valid_to && $this->valid_to < now()) {
            return 'expired';
        }

        return 'current';
    }

    /**
     * Accessor لتاريخ التسليم المتوقع
     */
    public function getEstimatedDeliveryAttribute(): Carbon
    {
        return $this->estimatedDeliveryDate();
    }

    /**
     * Accessor لعدد الأيام المتبقية للصلاحية
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->valid_to) {
            return null;
        }

        return now()->diffInDays($this->valid_to, false);
    }

    /**
     * Accessor للتحقق من قرب انتهاء الصلاحية
     */
    public function getIsExpiringAttribute(): bool
    {
        if (!$this->valid_to) {
            return false;
        }

        return $this->days_until_expiry !== null && $this->days_until_expiry <= 30;
    }

    // ========================================
    // 📊 Static Helper Methods
    // ========================================

    /**
     * الحصول على أفضل سعر لمنتج معين
     */
    public static function getBestPriceForProduct(int $productId, string $criteria = 'price'): ?self
    {
        $query = self::forProduct($productId)->current();

        switch ($criteria) {
            case 'price':
                return $query->cheapestFirst()->first();
            case 'delivery':
                return $query->fastestDelivery()->first();
            case 'combined':
                // حساب نقاط بناءً على السعر ووقت التسليم
                return $query->get()->sortBy(function ($price) {
                    return ($price->price * 0.7) + ($price->delivery_time_days * 0.3);
                })->first();
            default:
                return $query->first();
        }
    }

    /**
     * الحصول على جميع موردي منتج معين
     */
    public static function getSuppliersForProduct(int $productId): array
    {
        return self::forProduct($productId)
            ->current()
            ->with('supplier')
            ->get()
            ->pluck('supplier')
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * مقارنة أسعار الموردين لمنتج معين
     */
    public static function compareSupplierPrices(int $productId): array
    {
        $prices = self::forProduct($productId)
            ->current()
            ->with('supplier')
            ->get();

        if ($prices->isEmpty()) {
            return [];
        }

        $cheapest = $prices->sortBy('price')->first();
        $fastest = $prices->sortBy('delivery_time_days')->first();

        return [
            'total_suppliers' => $prices->count(),
            'cheapest' => [
                'supplier_id' => $cheapest->supplier_id,
                'price' => $cheapest->price,
                'delivery_days' => $cheapest->delivery_time_days,
            ],
            'fastest' => [
                'supplier_id' => $fastest->supplier_id,
                'price' => $fastest->price,
                'delivery_days' => $fastest->delivery_time_days,
            ],
            'average_price' => $prices->avg('price'),
            'price_range' => [
                'min' => $prices->min('price'),
                'max' => $prices->max('price'),
            ],
            'all_prices' => $prices->toArray(),
        ];
    }

    /**
     * الحصول على الأسعار المنتهية قريباً
     */
    public static function getExpiringSoon(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->whereNotNull('valid_to')
            ->whereBetween('valid_to', [now(), now()->addDays($days)])
            ->with(['product', 'supplier'])
            ->get();
    }

    /**
     * إنشاء سعر جديد مع إيقاف القديم
     */
    public static function updateSupplierPrice(int $supplierId, int $productId, array $data): self
    {
        // إيقاف السعر الحالي
        self::forSupplier($supplierId)
            ->forProduct($productId)
            ->current()
            ->update([
                'is_active' => false,
                'valid_to' => now()->subDay(),
            ]);

        // إنشاء السعر الجديد
        $data['supplier_id'] = $supplierId;
        $data['product_id'] = $productId;
        
        return self::create($data);
    }

    // ========================================
    // 🔄 Events
    // ========================================

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // عند الإنشاء
        static::creating(function ($price) {
            // تعيين العملة الافتراضية
            if (!$price->currency) {
                $price->currency = 'EGP';
            }

            // تعيين تاريخ البداية الافتراضي
            if (!$price->valid_from) {
                $price->valid_from = now();
            }
        });

        // عند التحديث
        static::updating(function ($price) {
            // إذا تم تعطيل السعر، تعيين تاريخ الانتهاء
            if (!$price->is_active && $price->isDirty('is_active') && !$price->valid_to) {
                $price->valid_to = now();
            }
        });
    }
}