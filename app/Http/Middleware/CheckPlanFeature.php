<?php

namespace App\Http\Middleware;

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
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        // 1. Get the current tenant
        $tenant = function_exists('tenant') ? tenant() : null;

        if (!$tenant) {
            return $next($request);
        }

        // Check if tenant is suspended
        if ($tenant->is_suspended || (isset($tenant->data['is_suspended']) && $tenant->data['is_suspended'])) {
            abort(403, 'هذا الحساب معطل حالياً لعدم سداد الاشتراك أو بقرار من الإدارة. يرجى مراجعة الدعم الفني.');
        }

        // 2. Check if the plan is expired
        if ($tenant->plan_expires_at && now()->greaterThan($tenant->plan_expires_at)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'plan_expired'], 403);
            }
            abort(403, 'plan_expired');
        }

        if (!$feature) {
            return $next($request);
        }

        // 3. Get the plan with features cached for 30 minutes
        $planId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? null);
        $cacheKey = "tenant_{$tenant->id}_plan";

        $planFeatures = Cache::remember($cacheKey, 1800, function () use ($planId) {
            $centralConnection = config('tenancy.database.central_connection', 'central');
            
            $plan = DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();

            if (!$plan) {
                return [];
            }

            return DB::connection($centralConnection)
                ->table('plan_features')
                ->where('plan_id', $plan->id)
                ->get()
                ->keyBy('feature_key')
                ->toArray();
        });

        // 4. Check if the feature is not present or disabled
        $hasFeature = false;
        if (isset($planFeatures[$feature])) {
            $hasFeature = (bool) $planFeatures[$feature]->is_enabled;
        } else {
            // Fallback to legacy structure in plan model
            $centralConnection = config('tenancy.database.central_connection', 'central');
            $plan = DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();
            if ($plan) {
                $features = json_decode($plan->features, true) ?? [];
                $hasFeature = is_array($features) && in_array($feature, $features);
            }
        }

        if (!$hasFeature) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'feature_not_in_plan'], 403);
            }

            // 5. Redirect if not JSON request
            if (function_exists('route') && \Route::has('plan.upgrade')) {
                $friendlyFeatures = [
                    'pos' => 'نقاط البيع (POS)',
                    'manufacturing' => 'موديول التصنيع والإنتاج',
                    'multi_warehouse' => 'تعدد المستودعات والمخازن',
                    'warehouses' => 'تعدد المستودعات والمخازن',
                    'accounting' => 'موديول الحسابات والمالية',
                    'reports_advanced' => 'التقارير المالية المتقدمة',
                ];
                $reasonName = $friendlyFeatures[$feature] ?? $feature;
                return redirect()->route('plan.upgrade')
                    ->with('error', 'feature_not_in_plan')
                    ->with('reason', $reasonName);
            }

            abort(403, 'feature_not_in_plan');
        }

        return $next($request);
    }
}
