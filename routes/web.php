<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Landlord\SuperAdminController;
use App\Http\Controllers\PricingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Central / Landlord Application)
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your central application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
| These central routes are accessible only via the central domains (localhost, 127.0.0.1).
|
| NOTE: GET/POST /login and POST /logout are intentionally NOT defined here.
| They live in RouteServiceProvider::boot() so they can be registered per-central-domain
| via Route::domain($domain), which avoids the route-key collision with routes/tenant.php
| /login (which is intentionally registered with empty domain key for tenant hosts).
*/

/*
|--------------------------------------------------------------------------
| Public Marketing Routes
|--------------------------------------------------------------------------
|
| These routes are PUBLIC — no auth, no tenant context — meant to be served
| on a dedicated subdomain (e.g. pricing.kayyan.com) and/or the /pricing path
| on any central domain. They are NOT inside the super-admin group so they
| remain reachable from outside the dashboard.
|
*/

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.public');

/*
|--------------------------------------------------------------------------
| Central Auth Routes (Landlord / Super-Admin Login)
|--------------------------------------------------------------------------
| The login form and POST are only reachable from the central domains
| (superdashboard / localhost). Tenant login continues to live in
| routes/tenant.php.
|
| These routes are registered PER-CENTRAL-DOMAIN in RouteServiceProvider::boot()
| (not here) so that their route key (method + domain + uri) is unique per host.
| That avoids the historic silent overwrite of /login in this file by
| routes/tenant.php /login (which uses an empty domain key).
*/

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super.admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Plans CRUD
    Route::get('/plans', [SuperAdminController::class, 'plansIndex'])->name('plans.index');
    Route::get('/plans/create', [SuperAdminController::class, 'plansCreate'])->name('plans.create');
    Route::post('/plans', [SuperAdminController::class, 'plansStore'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [SuperAdminController::class, 'plansEdit'])->name('plans.edit');
    Route::put('/plans/{plan}', [SuperAdminController::class, 'plansUpdate'])->name('plans.update');
    Route::delete('/plans/{plan}', [SuperAdminController::class, 'plansDestroy'])->name('plans.destroy');

    // Tenants CRUD
    Route::get('/tenants', [SuperAdminController::class, 'tenantsIndex'])->name('tenants.index');
    Route::get('/tenants/create', [SuperAdminController::class, 'tenantsCreate'])->name('tenants.create');
    Route::post('/tenants', [SuperAdminController::class, 'tenantsStore'])->name('tenants.store');
    Route::get('/tenants/{id}/edit', [SuperAdminController::class, 'tenantsEdit'])->name('tenants.edit');
    Route::put('/tenants/{id}', [SuperAdminController::class, 'tenantsUpdate'])->name('tenants.update');
    Route::post('/tenants/{id}/toggle-status', [SuperAdminController::class, 'tenantsToggleStatus'])->name('tenants.toggle-status');
    Route::delete('/tenants/{id}', [SuperAdminController::class, 'tenantsDestroy'])->name('tenants.destroy');
});
