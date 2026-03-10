<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductBasePricing extends Model
{
    use HasFactory;

    protected $table = 'product_base_pricing';

    protected $fillable = [
        'product_id',
        'base_unit',
        'base_purchase_price',
        'base_selling_price',
        'profit_type',
        'profit_value',
        'is_active',
        'is_current',
        'effective_from',
        'effective_to',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'base_purchase_price' => 'decimal:2',
        'base_selling_price' => 'decimal:2',
        'profit_value' => 'decimal:2',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================================
    // 🔗 Relationships
    // ========================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========================================
    // 🔍 Scopes (محسّنة للأداء)
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * ✅ السعر الحالي - محسّن باستخدام is_current
     */
    public function scopeCurrent(Builder $query, ?Carbon $date = null): Builder
    {
        $date = $date ?? now();

        return $query->where('is_current', true)
                    ->where('is_active', true)
                    ->where('effective_from', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $date);
                    });
    }

    public function scopeEffectiveOn(Builder $query, Carbon $date): Builder
    {
        return $query->where('effective_from', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $date);
                    });
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByUnit(Builder $query, string $unit): Builder
    {
        return $query->where('base_unit', $unit);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('effective_to')
                    ->where('effective_to', '<', now());
    }

    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('effective_from', '>', now());
    }

    // ========================================
    // 🛠️ Helper Methods
    // ========================================

    public function calculateSellingPrice(): float
    {
        if ($this->profit_type === 'percentage') {
            return round($this->base_purchase_price * (1 + ($this->profit_value / 100)), 2);
        }

        return round($this->base_purchase_price + $this->profit_value, 2);
    }

    public function calculateProfitMargin(): float
    {
        return $this->base_selling_price - $this->base_purchase_price;
    }

    public function calculateProfitPercentage(): float
    {
        if ($this->base_purchase_price == 0) {
            return 0;
        }

        return round((($this->base_selling_price - $this->base_purchase_price) / $this->base_purchase_price) * 100, 2);
    }

    public function updateSellingPrice(): void
    {
        $this->base_selling_price = $this->calculateSellingPrice();
        $this->save();
    }

    public function isValidOn(?Carbon $date = null): bool
    {
        $date = $date ?? now();

        if (!$this->is_active || !$this->is_current) {
            return false;
        }

        if ($this->effective_from->gt($date)) {
            return false;
        }

        if ($this->effective_to && $this->effective_to->lt($date)) {
            return false;
        }

        return true;
    }

    public function deactivate(): bool
    {
        return $this->update([
            'is_active' => false,
            'is_current' => false,
            'effective_to' => now(),
        ]);
    }

    public function setAsCurrent(): bool
    {
        return DB::transaction(function () {
            // إلغاء السعر الحالي
            static::where('product_id', $this->product_id)
                ->where('base_unit', $this->base_unit)
                ->where('id', '!=', $this->id)
                ->update([
                    'is_current' => false,
                    'effective_to' => now(),
                ]);

            // تعيين هذا السعر
            $result = $this->update([
                'is_current' => true,
                'is_active' => true,
            ]);

            // مسح الكاش
            $this->clearCache();

            return $result;
        });
    }

    // ========================================
    // 🎨 Accessors
    // ========================================

    public function getProfitMarginAttribute(): float
    {
        return $this->calculateProfitMargin();
    }

    public function getProfitPercentageAttribute(): float
    {
        return $this->calculateProfitPercentage();
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) return 'inactive';
        if (!$this->is_current) return 'old';
        if ($this->effective_from->gt(now())) return 'future';
        if ($this->effective_to && $this->effective_to->lt(now())) return 'expired';

        return 'current';
    }

    // ========================================
    // 📊 Static Methods (محسّنة للأداء)
    // ========================================

    /**
     * ✅ الحصول على الأسعار الحالية لعدة منتجات دفعة واحدة (BULK)
     */
    public static function getCurrentPricesForProducts(array $productIds): \Illuminate\Support\Collection
    {
        $cacheKey = 'current_prices_' . md5(implode(',', $productIds));

        return Cache::remember($cacheKey, 600, function () use ($productIds) {
            return static::whereIn('product_id', $productIds)
                ->where('is_current', true)
                ->where('is_active', true)
                ->get()
                ->keyBy('product_id');
        });
    }

    /**
     * ✅ الحصول على السعر الحالي لمنتج واحد
     */
    public static function getCurrentPriceForProduct(int $productId, ?string $baseUnit = null): ?self
    {
        $cacheKey = "current_price_{$productId}_{$baseUnit}";

        return Cache::remember($cacheKey, 600, function () use ($productId, $baseUnit) {
            $query = self::forProduct($productId)
                ->current()
                ->orderBy('created_at', 'desc');

            if ($baseUnit) {
                $query->byUnit($baseUnit);
            }

            return $query->first();
        });
    }

    /**
     * ✅ إنشاء سعر جديد
     */
    public static function createNewPrice(array $data): self
    {
        return DB::transaction(function () use ($data) {
            // إنهاء السعر الحالي
            self::forProduct($data['product_id'])
                ->where('base_unit', $data['base_unit'])
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'effective_to' => now()->subDay(),
                ]);

            // إنشاء السعر الجديد
            $data['is_current'] = true;
            $data['is_active'] = true;
            $data['effective_from'] = $data['effective_from'] ?? now();

            $price = self::create($data);

            // مسح الكاش
            Cache::forget("current_price_{$data['product_id']}_{$data['base_unit']}");

            return $price;
        });
    }

    /**
     * ✅ مسح الكاش
     */
    protected function clearCache(): void
    {
        Cache::forget("current_price_{$this->product_id}_{$this->base_unit}");
        Cache::tags(['product_prices'])->flush();
    }

    // ========================================
    // 🔄 Events
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pricing) {
            if (auth()->check()) {
                $pricing->created_by = auth()->id();
            }

            $pricing->is_current = $pricing->is_current ?? true;
            $pricing->effective_from = $pricing->effective_from ?? now();

            if (!$pricing->base_selling_price || $pricing->base_selling_price == 0) {
                $pricing->base_selling_price = $pricing->calculateSellingPrice();
            }
        });

        static::updating(function ($pricing) {
            if (auth()->check()) {
                $pricing->updated_by = auth()->id();
            }
        });

        static::saved(function ($pricing) {
            $pricing->clearCache();
        });

        static::deleted(function ($pricing) {
            $pricing->clearCache();
        });
    }
}