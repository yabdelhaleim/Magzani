<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the upgrade plans page.
     */
    public function upgrade(Request $request)
    {
        // 1. Get all plans from central/landlord database connection
        $centralConnection = config('tenancy.database.central_connection', 'central');
        
        try {
            $plans = DB::connection($centralConnection)
                ->table('plans')
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            $plans = collect();
        }
            
        // 2. Get the current tenant's plan slug
        $currentTenant = tenant();
        $currentPlanId = $currentTenant ? ($currentTenant->plan_id ?? ($currentTenant->data['plan_id'] ?? null)) : null;
        
        // 3. Get blocking reason from session
        $reason = session('reason');

        // Parse features JSON for display in Blade
        $plans = $plans->map(function($p) {
            $p->features_list = is_string($p->features) ? (json_decode($p->features, true) ?? []) : (array) $p->features;
            return $p;
        });

        return view('plan.upgrade', compact('plans', 'currentPlanId', 'reason'));
    }
}
