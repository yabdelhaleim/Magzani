<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

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
        'sales' => 'pos',
        'purchases' => 'purchase',
        'warehouses' => 'multi_warehouse',
        'warehouse' => 'multi_warehouse',
        'reports' => 'reports_advanced',
        'advanced_accounting' => 'accounting_advanced',
    ];

    /**
     * Canonical feature keys used by routes and plan_features.
     *
     * @var array<int, string>
     */
    public const FEATURE_KEYS = [
        'pos',
        'purchase',
        'manufacturing',
        'multi_warehouse',
        'accounting',
        'accounting_advanced',
        'stock_count',
        'reports_advanced',
    ];

    /**
     * Resolve a feature key through aliases (returns the standard key).
     */
    public static function resolveFeatureKey(string $feature): string
    {
        return self::FEATURE_ALIASES[$feature] ?? $feature;
    }

    /**
     * Normalize submitted or persisted feature values to canonical keys.
     *
     * @param  mixed  $features
     * @return array<int, string>
     */
    public static function normalizeFeatureKeys($features): array
    {
        if (! is_array($features)) {
            return [];
        }

        return collect($features)
            ->filter(fn ($feature) => is_string($feature) && filled($feature))
            ->map(fn (string $feature) => self::resolveFeatureKey($feature))
            ->filter(fn (string $feature) => in_array($feature, self::FEATURE_KEYS, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Return canonical and legacy keys accepted by the admin forms.
     *
     * @return array<int, string>
     */
    public static function acceptedFeatureKeys(): array
    {
        return array_values(array_unique(array_merge(
            self::FEATURE_KEYS,
            array_keys(self::FEATURE_ALIASES)
        )));
    }

    /**
     * Return the tenant's custom feature keys in their canonical form.
     *
     * Older records may still contain aliases such as "sales" or
     * "warehouses". Normalizing them here keeps every consumer consistent.
     *
     * @return array<int, string>
     */
    public function customFeatureKeys(): array
    {
        $customFeatures = $this->custom_features ?? ($this->data['custom_features'] ?? []);

        return self::normalizeFeatureKeys($customFeatures);
    }

    /**
     * التحقق من توفر ميزة معينة في باقة المستأجر.
     *
     * Custom plans use the tenant-specific feature list. Standard plans use
     * the central plan_features table and fall back to the legacy JSON column.
     */
    public function hasFeature(string $feature): bool
    {
        $requestedFeature = self::resolveFeatureKey($feature);
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);

        // ── 1) Custom plan ──────────────────────────────
        if ($planId === 'custom') {
            return in_array($requestedFeature, $this->customFeatureKeys(), true);
        }

        // ── 2) Source of truth: plan_features table ────
        $centralConnection = config('tenancy.database.central_connection', 'central');

        try {
            $plan = DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if ($plan) {
                // Prefer the canonical key, but keep compatibility with a
                // legacy row that may still use the requested alias.
                $row = DB::connection($centralConnection)
                    ->table('plan_features')
                    ->where('plan_id', $plan->id)
                    ->where('feature_key', $requestedFeature)
                    ->first();

                if (! $row && $feature !== $requestedFeature) {
                    $row = DB::connection($centralConnection)
                        ->table('plan_features')
                        ->where('plan_id', $plan->id)
                        ->where('feature_key', $feature)
                        ->first();
                }

                if ($row) {
                    return (bool) $row->is_enabled;
                }

                // Fallback: legacy JSON in plans.features.
                $features = json_decode($plan->features, true) ?? [];
                $featureKeys = collect($features)
                    ->map(function ($item) {
                        if (is_array($item) && isset($item['feature_key'])) {
                            return (string) $item['feature_key'];
                        }

                        return is_scalar($item) ? (string) $item : null;
                    })
                    ->filter()
                    ->map(fn (string $key) => self::resolveFeatureKey($key));

                return $featureKeys->contains($requestedFeature);
            }
        } catch (\Throwable $e) {
            // Keep feature checks fail-closed when the central store is
            // unavailable or the plan has not been configured yet.
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
        $feature = self::resolveFeatureKey($feature);
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);

        if (! $planId || $planId === 'custom') {
            return null; // Custom plans have no limits
        }

        $centralConnection = config('tenancy.database.central_connection', 'central');

        try {
            $plan = DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if (! $plan) {
                return null;
            }

            $row = DB::connection($centralConnection)
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
        Cache::forget("tenant_{$this->id}_plan");
        Cache::forget("tenant_{$this->id}_plan_v2");
        Cache::forget("tenant_{$this->id}_features");
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
        if (! $planId) {
            return null;
        }

        $centralConnection = config('tenancy.database.central_connection', 'central');
        $plan = Plan::on($centralConnection)->where('slug', $planId)->first();

        if (! $plan || $planId !== 'custom') {
            return $plan;
        }

        // The central "custom" plan row is just a placeholder. The runtime
        // feature list comes from the tenant's data.custom_features,
        // normalized through FEATURE_KEYS so the sidebar, view composers
        // and Plan::hasFeature() all see canonical keys.
        $featuresCollection = collect($this->customFeatureKeys())
            ->map(function (string $key) use ($plan) {
                $feature = new PlanFeature;
                $feature->plan_id = $plan->id;
                $feature->feature_key = $key;
                $feature->is_enabled = true;

                return $feature;
            });

        $plan->setRelation('features', $featuresCollection);

        return $plan;
    }

    /**
     * Build the public URL for this tenant (login page by default).
     */
    public function publicUrl(string $path = '/login'): ?string
    {
        $domain = $this->domains->first()?->domain;
        if (! $domain) {
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
