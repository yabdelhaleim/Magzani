<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =====================================================
        // تعريف مجموعات الـ features لكل باقة
        // =====================================================

        $allFeatures = [
            PlanFeature::POS             => ['enabled' => true,  'limit' => null],
            PlanFeature::PURCHASE        => ['enabled' => true,  'limit' => null],
            PlanFeature::MANUFACTURING   => ['enabled' => true,  'limit' => null],
            PlanFeature::MULTI_WAREHOUSE => ['enabled' => true,  'limit' => null],
            PlanFeature::ACCOUNTING      => ['enabled' => true,  'limit' => null],
            PlanFeature::ACCOUNTING_ADVANCED => ['enabled' => true, 'limit' => null],
            PlanFeature::STOCK_COUNT     => ['enabled' => true,  'limit' => null],
            PlanFeature::REPORTS_ADVANCED=> ['enabled' => true,  'limit' => null],
        ];

        $starterFeatures = [
            PlanFeature::POS      => ['enabled' => true, 'limit' => null],
            PlanFeature::PURCHASE => ['enabled' => true, 'limit' => null],
        ];

        $proFeatures = [
            PlanFeature::POS             => ['enabled' => true, 'limit' => null],
            PlanFeature::PURCHASE        => ['enabled' => true, 'limit' => null],
            PlanFeature::MANUFACTURING   => ['enabled' => true, 'limit' => null],
            PlanFeature::MULTI_WAREHOUSE => ['enabled' => true, 'limit' => 5],
            PlanFeature::ACCOUNTING      => ['enabled' => true, 'limit' => null],
            PlanFeature::ACCOUNTING_ADVANCED => ['enabled' => true, 'limit' => null],
            PlanFeature::REPORTS_ADVANCED=> ['enabled' => true, 'limit' => null],
        ];

        $enterpriseFeatures = [
            PlanFeature::POS             => ['enabled' => true, 'limit' => null],
            PlanFeature::PURCHASE        => ['enabled' => true, 'limit' => null],
            PlanFeature::MANUFACTURING   => ['enabled' => true, 'limit' => null],
            PlanFeature::MULTI_WAREHOUSE => ['enabled' => true, 'limit' => null],
            PlanFeature::ACCOUNTING      => ['enabled' => true, 'limit' => null],
            PlanFeature::ACCOUNTING_ADVANCED => ['enabled' => true, 'limit' => null],
            PlanFeature::STOCK_COUNT     => ['enabled' => true, 'limit' => null],
            PlanFeature::REPORTS_ADVANCED=> ['enabled' => true, 'limit' => null],
        ];

        // الباقة الأساسية القديمة: محاسبة فقط
        $basicFeatures = [
            PlanFeature::ACCOUNTING => ['enabled' => true, 'limit' => null],
        ];

        // باقة POS القديمة: كاشير + محاسبة
        $posFeatures = [
            PlanFeature::POS        => ['enabled' => true, 'limit' => null],
            PlanFeature::ACCOUNTING => ['enabled' => true, 'limit' => null],
            PlanFeature::PURCHASE   => ['enabled' => true, 'limit' => null],
        ];

        // الباقة الصناعية القديمة: كاشير + تصنيع + محاسبة
        $manufacturingFeatures = [
            PlanFeature::POS           => ['enabled' => true, 'limit' => null],
            PlanFeature::PURCHASE      => ['enabled' => true, 'limit' => null],
            PlanFeature::MANUFACTURING => ['enabled' => true, 'limit' => null],
            PlanFeature::ACCOUNTING    => ['enabled' => true, 'limit' => null],
        ];

        $plans = [
            // =====================================================
            // الباقات الجديدة (Starter / Pro / Enterprise)
            // =====================================================
            [
                'slug'         => 'starter',
                'name'         => 'Starter',
                'description'  => 'باقة المبتدئين - تشمل الكاشير والمشتريات فقط',
                'price'        => 99.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($starterFeatures),
                'is_active'    => true,
                'features_data'=> $starterFeatures,
            ],
            [
                'slug'         => 'pro',
                'name'         => 'Pro',
                'description'  => 'باقة المحترفين - إدارة كاملة تشمل التصنيع وتعدد المستودعات بحدود',
                'price'        => 299.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($proFeatures),
                'is_active'    => true,
                'features_data'=> $proFeatures,
            ],
            [
                'slug'         => 'enterprise',
                'name'         => 'Enterprise',
                'description'  => 'الباقة الشاملة - كافة الميزات والخيارات دون حدود لنمو شركتك',
                'price'        => 599.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($enterpriseFeatures),
                'is_active'    => true,
                'features_data'=> $enterpriseFeatures,
            ],

            // =====================================================
            // الباقات القديمة (Legacy) — موجودة في الـ DB وتحتاج features
            // =====================================================
            [
                'slug'         => 'basic',
                'name'         => 'الباقة الأساسية',
                'description'  => 'مناسبة للمستودعات وشركات التوزيع والمخازن العادية',
                'price'        => 19.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($basicFeatures),
                'is_active'    => true,
                'features_data'=> $basicFeatures,
            ],
            [
                'slug'         => 'pos',
                'name'         => 'باقة المحلات والتجزئة',
                'description'  => 'مناسبة للمحلات والمتاجر مع نقطة بيع',
                'price'        => 39.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($posFeatures),
                'is_active'    => true,
                'features_data'=> $posFeatures,
            ],
            [
                'slug'         => 'manufacturing',
                'name'         => 'الباقة الصناعية الكاملة',
                'description'  => 'مناسبة للمصانع وورش التصنيع',
                'price'        => 79.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($manufacturingFeatures),
                'is_active'    => true,
                'features_data'=> $manufacturingFeatures,
            ],
            [
                'slug'         => 'pxxx',
                'name'         => 'pro-x',
                'description'  => 'باقة مخصصة شاملة',
                'price'        => 500.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($allFeatures),
                'is_active'    => true,
                'features_data'=> $allFeatures,
            ],
            [
                'slug'         => 'custom',
                'name'         => 'باقة مخصصة',
                'description'  => 'باقة مخصصة تشمل جميع الميزات',
                'price'        => 0.00,
                'billing_period' => 'monthly',
                'features'     => array_keys($allFeatures),
                'is_active'    => true,
                'features_data'=> $allFeatures,
            ],
        ];

        $createdPlansCount   = 0;
        $createdFeaturesCount = 0;

        foreach ($plans as $planData) {
            $featuresData = $planData['features_data'];
            unset($planData['features_data']);

            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
            $createdPlansCount++;

            // Sync plan_features (upsert by plan_id + feature_key)
            foreach ($featuresData as $key => $config) {
                PlanFeature::updateOrCreate(
                    [
                        'plan_id'     => $plan->id,
                        'feature_key' => $key,
                    ],
                    [
                        'is_enabled'  => $config['enabled'],
                        'limit_value' => $config['limit'],
                    ]
                );
                $createdFeaturesCount++;
            }
        }

        $this->command->info("✅ PlanSeeder completed successfully!");
        $this->command->info("   Plans created/updated : {$createdPlansCount}");
        $this->command->info("   Features synced       : {$createdFeaturesCount}");
    }
}
