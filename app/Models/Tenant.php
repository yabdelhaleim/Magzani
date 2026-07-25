<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Custom database columns that should be directly accessible on the model.
     * Any other columns will be stored inside the 'data' JSON column.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'plan_expires_at',
            'trial_ends_at',
            'data',
        ];
    }

    /**
     * Feature aliases — maps legacy keys to current standard keys.
     * Used when checking custom_features that may have been stored under
     * older naming conventions (e.g. "sales" → "pos").
     */
    public const FEATURE_ALIASES = [
        'sales'         => 'pos',
        'purchases'     => 'purchase',
        'warehouses'    => 'multi_warehouse',
        'warehouse'     => 'multi_warehouse',
        'reports'       => 'reports_advanced',
        'advanced_accounting' => 'accounting_advanced',
    ];

    /**
     * Resolve a feature key through aliases (returns the standard key).
     */
    public static function resolveFeatureKey(string $feature): string
    {
        return self::FEATURE_ALIASES[$feature] ?? $feature;
    }

    /**
     * التحقق من توفر ميزة معينة في باقة المستأجر
     *
     * المنطق الموحّد (يصلح المشكلة الأساسية):
     *  1. إذا كان المستأجر مخصص (custom) → فحص custom_features
     *  2. البحث في جدول plan_features (المصدر الموثوق)
     *  3. Fallback إلى عمود plans.features JSON
     */
    public function hasFeature(string $feature): bool
    {
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);

        // ── 1) Custom plan ──────────────────────────────
        if ($planId === 'custom') {
            $customFeatures = $this->custom_features ?? ($this->data['custom_features'] ?? []);
            $customFeatures = is_array($customFeatures) ? $customFeatures : [];

            // البحث المباشر
            if (in_array($feature, $customFeatures, true)) {
                return true;
            }

            // البحث عبر aliases (sales → pos)
            foreach ($customFeatures as $cf) {
                if (self::resolveFeatureKey((string) $cf) === $feature) {
                    return true;
                }
            }

            return false;
        }

        // ── 2) Source of truth: plan_features table ────
        $centralConnection = config('tenancy.database.central_connection', 'central');

        try {
            $plan = \Illuminate\Support\Facades\DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if ($plan) {
                // أ) فحص جدول plan_features (مصدر موثوق)
                $row = \Illuminate\Support\Facades\DB::connection($centralConnection)
                    ->table('plan_features')
                    ->where('plan_id', $plan->id)
                    ->where('feature_key', $feature)
                    ->first();

                if ($row) {
                    return (bool) $row->is_enabled;
                }

                // ب) Fallback: فحص عمود JSON في plans
                $features = json_decode($plan->features, true) ?? [];

                // تنظيف: إذا كان JSON يحتوي objects (مثل {id, plan_id, feature_key})
                // استخرج feature_key فقط
                $featureKeys = collect($features)
                    ->map(function ($item) {
                        if (is_array($item) && isset($item['feature_key'])) {
                            return (string) $item['feature_key'];
                        }
                        return (string) $item;
                    })
                    ->toArray();

                return in_array($feature, $featureKeys, true);
            }
        } catch (\Exception $e) {
            // تجاهل الخطأ
        }

        return false;
    }

    /**
     * Get the limit value for a feature assigned to this tenant's plan.
     *
     * Returns null if no limit is set.
     */
    public function getFeatureLimit(string $feature): ?int
    {
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);

        if (! $planId || $planId === 'custom') {
            return null; // Custom plans have no limits
        }

        $centralConnection = config('tenancy.database.central_connection', 'central');

        try {
            $plan = \Illuminate\Support\Facades\DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if (! $plan) {
                return null;
            }

            $row = \Illuminate\Support\Facades\DB::connection($centralConnection)
                ->table('plan_features')
                ->where('plan_id', $plan->id)
                ->where('feature_key', $feature)
                ->first();

            // 🐛 Bug Fix: (int) null = 0 in PHP
            // Treat null as "no limit set" (return null), not as 0
            if ($row && $row->limit_value !== null) {
                return (int) $row->limit_value;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Invalidate cached plan features for this tenant.
     * Should be called after any plan/feature change.
     */
    public function invalidateFeatureCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget("tenant_{$this->id}_plan");
        \Illuminate\Support\Facades\Cache::forget("tenant_{$this->id}_features");
    }

    /**
     * علاقة الباقة مع المستأجر
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'slug');
    }

    /**
     * جلب الباقة ديناميكياً للتعامل مع الباقات القياسية والمخصصة
     */
    public function getPlanAttribute()
    {
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);
        if (!$planId) {
            return null;
        }

        if ($planId === 'custom') {
            $plan = Plan::where('slug', 'custom')->first();
            if ($plan) {
                $customFeatures = $this->custom_features ?? ($this->data['custom_features'] ?? []);
                $featuresCollection = collect(is_array($customFeatures) ? $customFeatures : [])
                    ->map(function ($key) use ($plan) {
                        $f = new PlanFeature();
                        $f->plan_id = $plan->id;
                        $f->feature_key = $key;
                        $f->is_enabled = true;
                        return $f;
                    });
                $plan->setRelation('features', $featuresCollection);
                return $plan;
            }
        }

        return $this->getRelationValue('plan') ?? Plan::where('slug', $planId)->first();
    }

    /**
     * Build the public URL for this tenant (login page by default).
     */
    public function publicUrl(string $path = '/login'): ?string
    {
        $domain = $this->domains->first()?->domain;
        if (!$domain) {
            return null;
        }

        $scheme = (string) config('tenancy.tenant_url.scheme', 'https');
        $port = config('tenancy.tenant_url.port');

        $url = $scheme.'://'.$domain;
        if ($port) {
            $url .= ':'.$port;
        }

        return rtrim($url, '/').'/'.ltrim($path, '/');
    }
}

