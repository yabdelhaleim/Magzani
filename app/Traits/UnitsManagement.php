<?php

namespace App\Traits;

use App\Models\ProductBasePricing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * 🎯 Trait للوحدات - مُحسّن للنظام الجديد
 * 
 * يُستخدم في:
 * - ProductController
 * - PriceUpdateController
 * - أي Controller يتعامل مع الوحدات
 * 
 * الاستخدام:
 * use UnitsManagement;
 */
trait UnitsManagement
{
    /**
     * 📋 كل الوحدات المتاحة في النظام (مجموعة شاملة)
     * 
     * @return array
     */
    protected function getAllUnits(): array
    {
        return [
            // الوزن
            'ton' => 'طن',
            'kg' => 'كيلوجرام',
            'gram' => 'جرام',
            'quintal' => 'قنطار',
            
            // الحجم
            'liter' => 'لتر',
            'milliliter' => 'مللتر',
            'gallon' => 'جالون',
            
            // الطول
            'meter' => 'متر',
            'cm' => 'سنتيمتر',
            'millimeter' => 'مليمتر',
            'inch' => 'بوصة',
            
            // العدد والتعبئة
            'piece' => 'قطعة',
            'dozen' => 'دستة',
            'set' => 'طقم',
            'pair' => 'زوج',
            
            // التعبئات الشائعة
            'bag' => 'شيكارة',
            'sack' => 'جوال',
            'box' => 'صندوق',
            'carton' => 'كرتونة',
            'pack' => 'عبوة',
            'bundle' => 'ربطة',
            'pallet' => 'طبلية',
            
            // الحاويات
            'can' => 'علبة معدنية',
            'bottle' => 'زجاجة',
            'jar' => 'برطمان',
            'container' => 'حاوية',
            
            // أخرى
            'roll' => 'لفة',
            'sheet' => 'ورقة',
            'strip' => 'شريط',
            'unit' => 'وحدة',
        ];
    }

    /**
     * 📋 الوحدات المستخدمة فعلياً في النظام (من قاعدة البيانات)
     * 
     * @return array
     */
    protected function getActiveUnits(): array
    {
        return Cache::remember('active_units_list', 3600, function () {
            $activeUnits = ProductBasePricing::select('base_unit')
                ->distinct()
                ->where('is_active', true)
                ->pluck('base_unit')
                ->toArray();

            $allUnits = $this->getAllUnits();
            $result = [];

            foreach ($activeUnits as $unit) {
                $result[$unit] = $allUnits[$unit] ?? $unit;
            }

            return $result;
        });
    }

    /**
     * 🎯 الوحدات للاستخدام في الفورم (حسب السياق)
     * 
     * @param bool $onlyActive إذا true، يعرض فقط الوحدات المستخدمة
     * @return array
     */
    protected function getUnits(bool $onlyActive = false): array
    {
        return $onlyActive ? $this->getActiveUnits() : $this->getAllUnits();
    }

    /**
     * 🔍 الحصول على اسم الوحدة بالعربي
     * 
     * @param string $unit
     * @return string
     */
    protected function getUnitLabel(string $unit): string
    {
        $units = $this->getAllUnits();
        return $units[$unit] ?? $unit;
    }

    /**
     * ✅ التحقق من صحة الوحدة
     * 
     * @param string $unit
     * @return bool
     */
    protected function isValidUnit(string $unit): bool
    {
        return array_key_exists($unit, $this->getAllUnits());
    }

    /**
     * 📝 قواعد الـ Validation للوحدة (يُستخدم مع Laravel Validation)
     * 
     * @param bool $required هل الحقل مطلوب؟
     * @param bool $onlyActive فقط الوحدات المستخدمة؟
     * @return array
     */
    protected function getUnitValidationRule(bool $required = true, bool $onlyActive = false): array
    {
        $units = $onlyActive ? $this->getActiveUnits() : $this->getAllUnits();
        
        $rules = [];
        
        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        
        $rules[] = 'string';
        $rules[] = Rule::in(array_keys($units));
        
        return $rules;
    }

    /**
     * 📝 قواعد الـ Validation للوحدة (String format - للاستخدام القديم)
     * 
     * @param bool $onlyActive
     * @return string
     */
    protected function getUnitValidationRuleString(bool $onlyActive = false): string
    {
        $units = $onlyActive ? $this->getActiveUnits() : $this->getAllUnits();
        return 'required|string|in:' . implode(',', array_keys($units));
    }

    /**
     * 🏷️ الوحدات حسب الفئة/النوع
     * 
     * @return array
     */
    protected function getUnitsByCategory(): array
    {
        return [
            'weight' => [
                'label' => '⚖️ الوزن',
                'units' => [
                    'ton' => 'طن',
                    'quintal' => 'قنطار',
                    'kg' => 'كيلوجرام',
                    'gram' => 'جرام',
                ]
            ],
            'volume' => [
                'label' => '🧪 الحجم',
                'units' => [
                    'liter' => 'لتر',
                    'milliliter' => 'مللتر',
                    'gallon' => 'جالون',
                ]
            ],
            'length' => [
                'label' => '📏 الطول',
                'units' => [
                    'meter' => 'متر',
                    'cm' => 'سنتيمتر',
                    'millimeter' => 'مليمتر',
                    'inch' => 'بوصة',
                ]
            ],
            'count' => [
                'label' => '🔢 العدد',
                'units' => [
                    'piece' => 'قطعة',
                    'dozen' => 'دستة',
                    'set' => 'طقم',
                    'pair' => 'زوج',
                ]
            ],
            'packaging' => [
                'label' => '📦 التعبئة',
                'units' => [
                    'bag' => 'شيكارة',
                    'sack' => 'جوال',
                    'box' => 'صندوق',
                    'carton' => 'كرتونة',
                    'pack' => 'عبوة',
                    'bundle' => 'ربطة',
                    'pallet' => 'طبلية',
                    'can' => 'علبة معدنية',
                    'bottle' => 'زجاجة',
                    'jar' => 'برطمان',
                ]
            ],
            'other' => [
                'label' => '🎯 أخرى',
                'units' => [
                    'roll' => 'لفة',
                    'sheet' => 'ورقة',
                    'strip' => 'شريط',
                    'unit' => 'وحدة',
                    'container' => 'حاوية',
                ]
            ],
        ];
    }

    /**
     * 📊 الوحدات الأكثر استخداماً (من قاعدة البيانات)
     * 
     * @param int $limit
     * @return array
     */
    protected function getMostUsedUnits(int $limit = 5): array
    {
        return Cache::remember("most_used_units_{$limit}", 3600, function () use ($limit) {
            $units = ProductBasePricing::select('base_unit')
                ->where('is_active', true)
                ->groupBy('base_unit')
                ->selectRaw('base_unit, COUNT(*) as count')
                ->orderByDesc('count')
                ->limit($limit)
                ->get();

            $allUnits = $this->getAllUnits();
            $result = [];

            foreach ($units as $unit) {
                $result[$unit->base_unit] = [
                    'label' => $allUnits[$unit->base_unit] ?? $unit->base_unit,
                    'count' => $unit->count,
                ];
            }

            return $result;
        });
    }

    /**
     * 🔄 تحويل بين الوحدات (معاملات التحويل الأساسية)
     * 
     * @param float $value
     * @param string $fromUnit
     * @param string $toUnit
     * @return float|null
     */
    protected function convertUnit(float $value, string $fromUnit, string $toUnit): ?float
    {
        $conversions = [
            // الوزن
            'ton_to_kg' => 1000,
            'ton_to_gram' => 1000000,
            'ton_to_quintal' => 10,
            'kg_to_gram' => 1000,
            'quintal_to_kg' => 100,
            
            // الحجم
            'liter_to_milliliter' => 1000,
            'gallon_to_liter' => 3.785,
            
            // الطول
            'meter_to_cm' => 100,
            'meter_to_millimeter' => 1000,
            'cm_to_millimeter' => 10,
            'inch_to_cm' => 2.54,
            
            // العدد
            'dozen_to_piece' => 12,
            'carton_to_piece' => 24,
        ];

        // نفس الوحدة
        if ($fromUnit === $toUnit) {
            return $value;
        }

        // تحويل مباشر
        $key = "{$fromUnit}_to_{$toUnit}";
        if (isset($conversions[$key])) {
            return $value * $conversions[$key];
        }

        // تحويل عكسي
        $reverseKey = "{$toUnit}_to_{$fromUnit}";
        if (isset($conversions[$reverseKey])) {
            return $value / $conversions[$reverseKey];
        }

        // لا يوجد تحويل متاح
        return null;
    }

    /**
     * 🎨 رمز الوحدة (Emoji/Icon)
     * 
     * @param string $unit
     * @return string
     */
    protected function getUnitIcon(string $unit): string
    {
        $icons = [
            'ton' => '⚖️',
            'kg' => '⚖️',
            'gram' => '⚖️',
            'quintal' => '⚖️',
            'liter' => '🧪',
            'milliliter' => '🧪',
            'gallon' => '🧪',
            'meter' => '📏',
            'cm' => '📏',
            'millimeter' => '📏',
            'inch' => '📏',
            'piece' => '🔢',
            'dozen' => '🔢',
            'set' => '🎯',
            'pair' => '👟',
            'bag' => '📦',
            'sack' => '📦',
            'box' => '📦',
            'carton' => '📦',
            'pack' => '📦',
            'bundle' => '📦',
            'pallet' => '🏗️',
            'bottle' => '🍾',
            'can' => '🥫',
            'jar' => '🏺',
            'container' => '📦',
            'roll' => '🎞️',
            'sheet' => '📄',
            'strip' => '📏',
            'unit' => '📦',
        ];

        return $icons[$unit] ?? '📦';
    }

    /**
     * 🧹 مسح الكاش الخاص بالوحدات
     */
    protected function clearUnitsCache(): void
    {
        Cache::forget('active_units_list');
        
        // مسح كاش most_used_units لكل الـ limits الممكنة
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("most_used_units_{$i}");
        }
    }

    /**
     * 📋 الحصول على معلومات شاملة عن وحدة
     * 
     * @param string $unit
     * @return array|null
     */
    protected function getUnitInfo(string $unit): ?array
    {
        if (!$this->isValidUnit($unit)) {
            return null;
        }

        // جلب عدد المنتجات التي تستخدم هذه الوحدة
        $productsCount = ProductBasePricing::where('base_unit', $unit)
            ->where('is_active', true)
            ->count();

        return [
            'code' => $unit,
            'label' => $this->getUnitLabel($unit),
            'icon' => $this->getUnitIcon($unit),
            'products_count' => $productsCount,
            'is_active' => $productsCount > 0,
        ];
    }

    /**
     * 🔍 البحث عن وحدات بالنص
     * 
     * @param string $search
     * @return array
     */
    protected function searchUnits(string $search): array
    {
        $search = strtolower($search);
        $allUnits = $this->getAllUnits();
        $results = [];

        foreach ($allUnits as $code => $label) {
            if (str_contains(strtolower($code), $search) || 
                str_contains(strtolower($label), $search)) {
                $results[$code] = $label;
            }
        }

        return $results;
    }
}