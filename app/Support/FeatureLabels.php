<?php

namespace App\Support;

use App\Models\PlanFeature;

/**
 * Maps feature_key codes used in plan_features table to their Arabic display
 * labels for the public pricing page.
 *
 * Keeping the mapping in one place means the super-admin dashboard can attach
 * any feature_key without having to touch the pricing blade.
 */
class FeatureLabels
{
    /**
     * Canonical mapping of feature_key => Arabic label.
     *
     * Add new keys here when introducing new plan-level features.
     */
    public const LABELS = [
        PlanFeature::POS                 => 'نقاط البيع (POS) مع الكاشير',
        PlanFeature::PURCHASE            => 'فواتير المبيعات والمشتريات',
        PlanFeature::MANUFACTURING       => 'إدارة التصنيع وأوامر الإنتاج',
        PlanFeature::MULTI_WAREHOUSE     => 'تعدد المستودعات والتحويلات',
        PlanFeature::ACCOUNTING          => 'الحسابات والمصروفات',
        PlanFeature::ACCOUNTING_ADVANCED => 'المحاسبة المتقدمة (شجرة الحسابات والقيود)',
        PlanFeature::STOCK_COUNT         => 'الجرد الدوري وحركات المخزون',
        PlanFeature::REPORTS_ADVANCED    => 'التقارير المالية وقوائم الدخل',
    ];

    /**
     * Translate a feature_key into its Arabic label.
     * Falls back to a humanized version of the key if no mapping exists.
     */
    public static function translate(string $featureKey): string
    {
        if (isset(self::LABELS[$featureKey])) {
            return self::LABELS[$featureKey];
        }

        return ucfirst(str_replace('_', ' ', $featureKey));
    }

    /**
     * @return array<string,string>
     */
    public static function all(): array
    {
        return self::LABELS;
    }
}