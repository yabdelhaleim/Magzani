<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

/**
 * Public Pricing Page Controller — simplified.
 *
 * Reads plans from the central DB. Plans are managed by SuperAdminController@plans*
 * in the landlord dashboard (/super-admin/plans). All marketing copy on the public
 * page is hard-coded in resources/views/pricing/index.blade.php — keep it simple.
 *
 * - No auth required (public route).
 * - No tenant context (lives in routes/web.php wrapped in central.domains middleware).
 * - Active plans only, filtered to the three SAAS plans (starter / pro / enterprise).
 *
 * Route: GET /pricing  (in routes/web.php)
 */
class PricingController extends Controller
{
    /**
     * Plans shown on the public pricing page.
     * Legacy plans (basic, pos, manufacturing, pxxx, custom) are excluded by design.
     */
    public const PUBLIC_PLAN_SLUGS = ['starter', 'pro', 'enterprise'];

    /**
     * Per-plan value-props shown on each card.
     */
    public const PLAN_VALUE_PROPS = [
        'starter' => [
            'basic cashiers',
            'sales & purchase invoices',
            'single warehouse',
            'basic reports',
        ],
        'pro' => [
            'all starter features',
            'manufacturing & BOM',
            'up to 5 warehouses',
            'advanced accounting',
            'financial reports',
        ],
        'enterprise' => [
            'all pro features',
            'unlimited warehouses',
            'wood stock & dispensing',
            'priority support',
            'custom integrations',
        ],
    ];

    public function index(Request $request)
    {
        $plans = Plan::where('is_active', true)
            ->with(['featuresList' => fn ($q) => $q->where('is_enabled', true)])
            ->whereIn('slug', self::PUBLIC_PLAN_SLUGS)
            ->orderByRaw("FIELD(slug, 'starter', 'pro', 'enterprise') ASC")
            ->orderBy('price')
            ->get()
            ->each(function ($plan) {
                $plan->value_props = self::PLAN_VALUE_PROPS[$plan->slug] ?? [];
                return $plan;
            });

        $siteOrigin = $request->getSchemeAndHttpHost();
        $canonical  = rtrim($siteOrigin, '/') . '/pricing';

        return view('pricing.index', [
            'plans'      => $plans,
            'siteOrigin' => $siteOrigin,
            'canonical'  => $canonical,
            'demoUrl'    => config('pricing.demo_url', '#'),
            'signupUrl'  => config('pricing.signup_url') ?: '#',
        ]);
    }
}
