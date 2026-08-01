<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $feature = null): Response
    {
        $tenant = function_exists('tenant') ? tenant() : null;

        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->is_suspended || (isset($tenant->data['is_suspended']) && $tenant->data['is_suspended'])) {
            abort(403, 'هذا الحساب معطل حالياً لعدم سداد الاشتراك أو بقرار من الإدارة. يرجى مراجعة الدعم الفني.');
        }

        if ($tenant->plan_expires_at && now()->greaterThan($tenant->plan_expires_at)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'plan_expired'], 403);
            }

            abort(403, 'plan_expired');
        }

        if (! $feature) {
            return $next($request);
        }

        $planId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? null);
        $requestedFeature = Tenant::resolveFeatureKey($feature);
        $cacheKey = "tenant_{$tenant->id}_plan_v2";
        $customFeatures = $tenant instanceof Tenant ? $tenant->customFeatureKeys() : [];

        $planFeatures = Cache::remember($cacheKey, 1800, function () use ($planId, $customFeatures) {
            // Custom plans are per-tenant. They must never inherit the
            // central "custom" plan's feature rows.
            if ($planId === 'custom') {
                return array_fill_keys($customFeatures, true);
            }

            $centralConnection = config('tenancy.database.central_connection', 'central');
            $plan = DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if (! $plan) {
                return [];
            }

            return DB::connection($centralConnection)
                ->table('plan_features')
                ->where('plan_id', $plan->id)
                ->get()
                ->keyBy('feature_key')
                ->toArray();
        });

        $hasFeature = false;
        foreach (array_unique([$requestedFeature, $feature]) as $featureKey) {
            if (! array_key_exists($featureKey, $planFeatures)) {
                continue;
            }

            $value = $planFeatures[$featureKey];
            $hasFeature = is_object($value)
                ? (bool) ($value->is_enabled ?? false)
                : (bool) $value;
            break;
        }

        // Preserve the legacy JSON fallback for standard plans when the
        // feature has no row in plan_features.
        if (! $hasFeature && $planId !== 'custom') {
            $centralConnection = config('tenancy.database.central_connection', 'central');
            $plan = DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if ($plan) {
                $rawFeatures = json_decode($plan->features, true) ?? [];
                $features = collect($rawFeatures)
                    ->map(function ($item) {
                        if (is_array($item) && isset($item['feature_key'])) {
                            return (string) $item['feature_key'];
                        }

                        return is_scalar($item) ? (string) $item : null;
                    })
                    ->filter()
                    ->map(fn (string $key) => Tenant::resolveFeatureKey($key));

                $hasFeature = $features->contains($requestedFeature);
            }
        }

        if (! $hasFeature) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'feature_not_in_plan'], 403);
            }

            if (function_exists('route') && \Route::has('plan.upgrade')) {
                $friendlyFeatures = [
                    'pos' => 'نقاط البيع (POS)',
                    'purchase' => 'المشتريات والموردين',
                    'manufacturing' => 'موديول التصنيع والإنتاج',
                    'multi_warehouse' => 'تعدد المستودعات والمخازن',
                    'accounting' => 'موديول الحسابات والمالية',
                    'accounting_advanced' => 'المحاسبة المتقدمة',
                    'reports_advanced' => 'التقارير المالية المتقدمة',
                ];
                $reasonName = $friendlyFeatures[$requestedFeature] ?? $requestedFeature;

                return redirect()->route('plan.upgrade')
                    ->with('error', 'feature_not_in_plan')
                    ->with('reason', $reasonName);
            }

            abort(403, 'feature_not_in_plan');
        }

        return $next($request);
    }
}
