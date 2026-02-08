<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * 🎯 Model: UnitConversion
 * 
 * @property int $id
 * @property string $from_unit
 * @property string $to_unit
 * @property float $conversion_factor
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UnitConversion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'unit_conversions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'from_unit',
        'to_unit',
        'conversion_factor',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'conversion_factor' => 'decimal:6',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    // ========================================
    // 🔍 Scopes
    // ========================================

    /**
     * Scope للحصول على التحويلات النشطة فقط
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للبحث عن تحويل محدد
     */
    public function scopeForConversion(Builder $query, string $fromUnit, string $toUnit): Builder
    {
        return $query->where('from_unit', $fromUnit)
                    ->where('to_unit', $toUnit);
    }

    /**
     * Scope للحصول على جميع التحويلات من وحدة معينة
     */
    public function scopeFromUnit(Builder $query, string $unit): Builder
    {
        return $query->where('from_unit', $unit);
    }

    /**
     * Scope للحصول على جميع التحويلات إلى وحدة معينة
     */
    public function scopeToUnit(Builder $query, string $unit): Builder
    {
        return $query->where('to_unit', $unit);
    }

    // ========================================
    // 🛠️ Helper Methods
    // ========================================

    /**
     * تحويل كمية من وحدة إلى أخرى
     */
    public static function convert(float $quantity, string $fromUnit, string $toUnit): ?float
    {
        if ($fromUnit === $toUnit) {
            return $quantity;
        }

        $conversion = self::active()
            ->forConversion($fromUnit, $toUnit)
            ->first();

        if ($conversion) {
            return $quantity * $conversion->conversion_factor;
        }

        // محاولة البحث عن تحويل عكسي
        $reverseConversion = self::active()
            ->forConversion($toUnit, $fromUnit)
            ->first();

        if ($reverseConversion && $reverseConversion->conversion_factor != 0) {
            return $quantity / $reverseConversion->conversion_factor;
        }

        return null;
    }

    /**
     * الحصول على معامل التحويل
     */
    public static function getConversionFactor(string $fromUnit, string $toUnit): ?float
    {
        if ($fromUnit === $toUnit) {
            return 1.0;
        }

        $conversion = self::active()
            ->forConversion($fromUnit, $toUnit)
            ->first();

        if ($conversion) {
            return (float) $conversion->conversion_factor;
        }

        // محاولة البحث عن تحويل عكسي
        $reverseConversion = self::active()
            ->forConversion($toUnit, $fromUnit)
            ->first();

        if ($reverseConversion && $reverseConversion->conversion_factor != 0) {
            return 1 / (float) $reverseConversion->conversion_factor;
        }

        return null;
    }

    /**
     * الحصول على جميع الوحدات المتاحة للتحويل من وحدة معينة
     */
    public static function getAvailableUnitsFrom(string $fromUnit): array
    {
        return self::active()
            ->where('from_unit', $fromUnit)
            ->pluck('to_unit')
            ->toArray();
    }

    /**
     * الحصول على جميع الوحدات المتاحة للتحويل إلى وحدة معينة
     */
    public static function getAvailableUnitsTo(string $toUnit): array
    {
        return self::active()
            ->where('to_unit', $toUnit)
            ->pluck('from_unit')
            ->toArray();
    }

    /**
     * تحقق من إمكانية التحويل بين وحدتين
     */
    public static function canConvert(string $fromUnit, string $toUnit): bool
    {
        if ($fromUnit === $toUnit) {
            return true;
        }

        return self::active()
            ->where(function ($query) use ($fromUnit, $toUnit) {
                $query->where(function ($q) use ($fromUnit, $toUnit) {
                    $q->where('from_unit', $fromUnit)
                      ->where('to_unit', $toUnit);
                })->orWhere(function ($q) use ($fromUnit, $toUnit) {
                    $q->where('from_unit', $toUnit)
                      ->where('to_unit', $fromUnit);
                });
            })
            ->exists();
    }

    /**
     * إنشاء تحويل عكسي تلقائياً
     */
    public function createReverseConversion(): ?self
    {
        if ($this->conversion_factor == 0) {
            return null;
        }

        // التحقق من عدم وجود تحويل عكسي
        $exists = self::where('from_unit', $this->to_unit)
            ->where('to_unit', $this->from_unit)
            ->exists();

        if ($exists) {
            return null;
        }

        return self::create([
            'from_unit' => $this->to_unit,
            'to_unit' => $this->from_unit,
            'conversion_factor' => 1 / $this->conversion_factor,
            'is_active' => $this->is_active,
        ]);
    }

    // ========================================
    // 🎨 Accessors & Mutators
    // ========================================

    /**
     * Accessor لعرض اسم التحويل
     */
    public function getConversionNameAttribute(): string
    {
        return "{$this->from_unit} → {$this->to_unit}";
    }

    /**
     * Accessor للحصول على معامل التحويل العكسي
     */
    public function getReverseFactorAttribute(): float
    {
        return $this->conversion_factor != 0 ? 1 / $this->conversion_factor : 0;
    }

    // ========================================
    // 📊 Static Helper Methods
    // ========================================

    /**
     * الحصول على جميع الوحدات الفريدة
     */
    public static function getAllUnits(): array
    {
        $fromUnits = self::distinct()->pluck('from_unit');
        $toUnits = self::distinct()->pluck('to_unit');
        
        return $fromUnits->merge($toUnits)->unique()->sort()->values()->toArray();
    }

    /**
     * الحصول على مجموعة التحويلات حسب النوع
     */
    public static function getConversionsByType(string $type): array
    {
        $types = [
            'weight' => ['ton', 'kg', 'gram', 'quintal'],
            'volume' => ['liter', 'milliliter', 'gallon'],
            'length' => ['meter', 'cm', 'millimeter', 'inch'],
            'count' => ['dozen', 'piece', 'carton'],
        ];

        if (!isset($types[$type])) {
            return [];
        }

        return self::active()
            ->whereIn('from_unit', $types[$type])
            ->whereIn('to_unit', $types[$type])
            ->get()
            ->toArray();
    }
}