<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Landlord\SuperAdminController;

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
*/

Route::prefix('super-admin')->name('super-admin.')->group(function () {
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
