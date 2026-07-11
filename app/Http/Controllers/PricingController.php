<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

/**
 * Public Pricing Page Controller.
 *
 * Plans shown on /pricing are sourced entirely from the central DB through the
 * super-admin dashboard (/super-admin/plans). Anything you create or edit there
 * shows up here automatically — no hard-coded slugs or copy in this file.
 *
 * - No auth required (public route).
 * - No tenant context (lives in routes/web.php wrapped in central.domains middleware).
 * - Only active plans are returned, ordered by sort_order then price.
 */
class PricingController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::where('is_active', true)
            ->with(['featuresList' => fn ($q) => $q->where('is_enabled', true)])
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

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