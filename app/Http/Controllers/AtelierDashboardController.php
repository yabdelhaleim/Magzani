<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

/**
 * AtelierDashboardController — serves the new Atelier design-system dashboard.
 *
 * Kept separate from the legacy DashboardController so existing routes,
 * tests and partial views continue to work untouched. Switch to this
 * controller via the `dashboard.v2` route or by changing the route
 * binding in routes/tenant.php.
 */
class AtelierDashboardController extends Controller
{
    /**
     * Render the Atelier dashboard.
     */
    public function index(Request $request, DashboardService $service)
    {
        $range  = (string) $request->query('range', 'month');
        $range  = in_array($range, array_keys(DashboardService::RANGES), true) ? $range : 'month';

        return view('dashboard.atelier', [
            'initialRange' => $range,
            'pageTitle'    => 'لوحة التحكم',
        ]);
    }
}
