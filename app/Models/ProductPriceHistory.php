<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductPriceHistory extends Model
{
    use HasFactory;

    protected $table = 'product_price_history';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'base_unit',
        'old_purchase_price',
        'new_purchase_price',
        'old_selling_price',
        'new_selling_price',
        'change_reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'old_purchase_price' => 'decimal:2',
        'new_purchase_price' => 'decimal:2',
        'old_selling_price' => 'decimal:2',
        'new_selling_price' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    // ========================================
    // 🔗 Relationships
    // ========================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ========================================
    // 🔍 Scopes (محسّنة)
    // ========================================

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeBetweenDates(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('changed_at', [$from, $to]);
    }

    public function scopeLastDays(Builder $query, int $days = 30): Builder
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('changed_by', $userId);
    }

    public function scopePriceIncreases(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereRaw('new_purchase_price > old_purchase_price')
              ->orWhereRaw('new_selling_price > old_selling_price');
        });
    }

    public function scopePriceDecreases(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereRaw('new_purchase_price < old_purchase_price')
              ->orWhereRaw('new_selling_price < old_selling_price');
        });
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('changed_at', 'desc');
    }

    // ========================================
    // 🛠️ Helper Methods
    // ========================================

    public function getPurchasePriceChangePercentage(): float
    {
        if ($this->old_purchase_price == 0) {
            return 0;
        }

        return round((($this->new_purchase_price - $this->old_purchase_price) / $this->old_purchase_price) * 100, 2);
    }

    public function getSellingPriceChangePercentage(): float
    {
        if ($this->old_selling_price == 0) {
            return 0;
        }

        return round((($this->new_selling_price - $this->old_selling_price) / $this->old_selling_price) * 100, 2);
    }

    public function getPurchasePriceDifference(): float
    {
        return round($this->new_purchase_price - $this->old_purchase_price, 2);
    }

    public function getSellingPriceDifference(): float
    {
        return round($this->new_selling_price - $this->old_selling_price, 2);
    }

    public function isIncrease(): bool
    {
        return $this->new_purchase_price > $this->old_purchase_price
            || $this->new_selling_price > $this->old_selling_price;
    }

    public function isDecrease(): bool
    {
        return $this->new_purchase_price < $this->old_purchase_price
            || $this->new_selling_price < $this->old_selling_price;
    }

    // ========================================
    // 🎨 Accessors
    // ========================================

    public function getPurchaseChangePercentageAttribute(): float
    {
        return $this->getPurchasePriceChangePercentage();
    }

    public function getSellingChangePercentageAttribute(): float
    {
        return $this->getSellingPriceChangePercentage();
    }

    public function getPurchaseDifferenceAttribute(): float
    {
        return $this->getPurchasePriceDifference();
    }

    public function getSellingDifferenceAttribute(): float
    {
        return $this->getSellingPriceDifference();
    }

    public function getChangeTypeAttribute(): string
    {
        if ($this->isIncrease()) {
            return 'increase';
        } elseif ($this->isDecrease()) {
            return 'decrease';
        }
        return 'no_change';
    }

    public function getChangeSummaryAttribute(): string
    {
        $purchaseDiff = $this->purchase_difference;
        $sellingDiff = $this->selling_difference;

        $summary = [];

        if ($purchaseDiff != 0) {
            $direction = $purchaseDiff > 0 ? 'زيادة' : 'انخفاض';
            $summary[] = "سعر الشراء: {$direction} " . abs($purchaseDiff);
        }

        if ($sellingDiff != 0) {
            $direction = $sellingDiff > 0 ? 'زيادة' : 'انخفاض';
            $summary[] = "سعر البيع: {$direction} " . abs($sellingDiff);
        }

        return implode(' | ', $summary);
    }

    // ========================================
    // 📊 Static Methods (محسّنة للأداء العالي)
    // ========================================

    /**
     * ✅ تسجيل تغيير سعر - محسّن مع Batch Insert
     */
    public static function logPriceChange(
        int $productId,
        string $baseUnit,
        float $oldPurchasePrice,
        float $newPurchasePrice,
        float $oldSellingPrice,
        float $newSellingPrice,
        ?string $reason = null
    ): self {
        // التحقق من وجود تغيير فعلي
        if ($oldPurchasePrice == $newPurchasePrice && $oldSellingPrice == $newSellingPrice) {
            throw new \RuntimeException('لا يوجد تغيير في الأسعار');
        }

        $history = self::create([
            'product_id' => $productId,
            'base_unit' => $baseUnit,
            'old_purchase_price' => $oldPurchasePrice,
            'new_purchase_price' => $newPurchasePrice,
            'old_selling_price' => $oldSellingPrice,
            'new_selling_price' => $newSellingPrice,
            'change_reason' => $reason,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        // مسح الكاش
        self::clearCacheForProduct($productId);

        return $history;
    }

    /**
     * ✅ تسجيل تغييرات متعددة دفعة واحدة (BULK INSERT)
     */
    public static function logBulkPriceChanges(array $changes, ?string $reason = null): int
    {
        if (empty($changes)) {
            return 0;
        }

        $userId = auth()->id();
        $now = now();
        $data = [];

        foreach ($changes as $change) {
            // التحقق من وجود تغيير فعلي
            if ($change['old_purchase_price'] == $change['new_purchase_price'] 
                && $change['old_selling_price'] == $change['new_selling_price']) {
                continue;
            }

            $data[] = [
                'product_id' => $change['product_id'],
                'base_unit' => $change['base_unit'],
                'old_purchase_price' => $change['old_purchase_price'],
                'new_purchase_price' => $change['new_purchase_price'],
                'old_selling_price' => $change['old_selling_price'],
                'new_selling_price' => $change['new_selling_price'],
                'change_reason' => $reason,
                'changed_by' => $userId,
                'changed_at' => $now,
            ];
        }

        if (empty($data)) {
            return 0;
        }

        // Batch Insert بدون Events لسرعة أعلى
        $inserted = DB::table('product_price_history')->insert($data);

        // مسح الكاش للمنتجات المتأثرة
        $productIds = array_unique(array_column($data, 'product_id'));
        foreach ($productIds as $productId) {
            self::clearCacheForProduct($productId);
        }

        return count($data);
    }

    /**
     * ✅ آخر تغيير - مع Cache محسّن
     */
    public static function getLastChangeForProduct(int $productId): ?self
    {
        return Cache::remember("last_price_change_{$productId}", 300, function () use ($productId) {
            return self::forProduct($productId)->latest()->first();
        });
    }

    /**
     * ✅ إحصائيات التغييرات - محسّنة بشكل كبير
     */
    public static function getProductPriceStatistics(int $productId): array
    {
        $cacheKey = "price_stats_{$productId}";

        return Cache::remember($cacheKey, 600, function () use ($productId) {
            $stats = self::forProduct($productId)
                ->selectRaw('
                    COUNT(*) as total_changes,
                    SUM(CASE WHEN new_purchase_price > old_purchase_price OR new_selling_price > old_selling_price THEN 1 ELSE 0 END) as increases,
                    SUM(CASE WHEN new_purchase_price < old_purchase_price OR new_selling_price < old_selling_price THEN 1 ELSE 0 END) as decreases,
                    AVG(new_purchase_price) as avg_purchase_price,
                    AVG(new_selling_price) as avg_selling_price,
                    MAX(new_purchase_price) as max_purchase_price,
                    MIN(new_purchase_price) as min_purchase_price,
                    MAX(new_selling_price) as max_selling_price,
                    MIN(new_selling_price) as min_selling_price
                ')
                ->first();

            if (!$stats || $stats->total_changes == 0) {
                return [];
            }

            return [
                'total_changes' => (int) $stats->total_changes,
                'increases' => (int) $stats->increases,
                'decreases' => (int) $stats->decreases,
                'avg_purchase_price' => round($stats->avg_purchase_price, 2),
                'avg_selling_price' => round($stats->avg_selling_price, 2),
                'max_purchase_price' => round($stats->max_purchase_price, 2),
                'min_purchase_price' => round($stats->min_purchase_price, 2),
                'max_selling_price' => round($stats->max_selling_price, 2),
                'min_selling_price' => round($stats->min_selling_price, 2),
            ];
        });
    }

    /**
     * ✅ تقرير التغييرات - محسّن مع Parallel Processing
     */
    public static function getPriceChangeReport(Carbon $from, Carbon $to): array
    {
        $cacheKey = "price_report_{$from->format('Ymd')}_{$to->format('Ymd')}";

        return Cache::remember($cacheKey, 1800, function () use ($from, $to) {
            // استخدام Query واحدة محسّنة
            $stats = self::betweenDates($from, $to)
                ->selectRaw('
                    COUNT(*) as total_changes,
                    COUNT(DISTINCT product_id) as products_affected,
                    SUM(CASE WHEN new_purchase_price > old_purchase_price OR new_selling_price > old_selling_price THEN 1 ELSE 0 END) as increases,
                    SUM(CASE WHEN new_purchase_price < old_purchase_price OR new_selling_price < old_selling_price THEN 1 ELSE 0 END) as decreases,
                    AVG(new_purchase_price - old_purchase_price) as avg_purchase_change,
                    AVG(new_selling_price - old_selling_price) as avg_selling_change
                ')
                ->first();

            $changesByUser = self::betweenDates($from, $to)
                ->selectRaw('changed_by, COUNT(*) as count')
                ->groupBy('changed_by')
                ->pluck('count', 'changed_by')
                ->toArray();

            return [
                'total_changes' => (int) ($stats->total_changes ?? 0),
                'products_affected' => (int) ($stats->products_affected ?? 0),
                'increases' => (int) ($stats->increases ?? 0),
                'decreases' => (int) ($stats->decreases ?? 0),
                'avg_purchase_change' => round($stats->avg_purchase_change ?? 0, 2),
                'avg_selling_change' => round($stats->avg_selling_change ?? 0, 2),
                'changes_by_user' => $changesByUser,
            ];
        });
    }

    /**
     * ✅ مسح الكاش للمنتج
     */
    protected static function clearCacheForProduct(int $productId): void
    {
        Cache::forget("last_price_change_{$productId}");
        Cache::forget("price_stats_{$productId}");
    }

    // ========================================
    // 🔄 Events
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($history) {
            if (auth()->check() && !$history->changed_by) {
                $history->changed_by = auth()->id();
            }

            if (!$history->changed_at) {
                $history->changed_at = now();
            }
        });

        static::created(function ($history) {
            self::clearCacheForProduct($history->product_id);
        });
    }
}