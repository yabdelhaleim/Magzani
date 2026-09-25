<?php

namespace App\Providers;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AtelierDashboardController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Central home must be domain-scoped and registered before tenant routes,
            // otherwise tenant "/" overwrites it and central domains get 404.
            foreach (config('tenancy.central_domains', ['localhost', '127.0.0.1']) as $domain) {
                Route::domain($domain)
                    ->middleware('web')
                    ->get('/', function () {
                        return redirect('/super-admin/dashboard');
                    });
            }

            // Central /login + /logout — registered PER central domain via Route::domain().
            // This is required to avoid the route-key collision (method+domain+uri) with the
            // SAME URIs in routes/tenant.php: by giving each central /login its own domain
            // constraint, every central domain gets a unique key in the route table, while
            // tenants continue to match the empty-domain /login from routes/tenant.php.
            //
            // The routes here are NOT named. The single source of truth for the "login" /
            // "logout" route names is routes/tenant.php, where they live with empty domain
            // constraint — so route('login') and route('logout') always resolve to a
            // path-only URL (e.g. "/login"). Browsers navigate that URL on whatever host the
            // user is on, and the router then dispatches to either this per-central-domain
            // route (matching the host constraint) or routes/tenant.php /login (matching
            // the empty-domain key). Both call the same LoginController.
            foreach (array_values(config('tenancy.central_domains', ['localhost', '127.0.0.1'])) as $domain) {
                Route::domain($domain)
                    ->middleware('web')
                    ->group(function () {
                        Route::middleware('guest')->group(function () {
                            Route::get('/login', [LoginController::class, 'showLoginForm']);
                            Route::post('/login', [LoginController::class, 'login'])
                                ->middleware('throttle:5,1');
                        });

                        Route::middleware('auth')->group(function () {
                            Route::post('/logout', [LoginController::class, 'logout']);
                        });
                    });
            }

            // Register central (landlord) routes once to avoid duplicate route names.
            // Access is restricted by middleware instead of domain-group looping.
            Route::middleware(['web', 'central.domains'])
                ->group(base_path('routes/web.php'));

            Route::group([], base_path('routes/tenant.php'));

            // Atelier Design System Dashboard — central preview, no tenant context.
            // Registered AFTER tenant.php so it wins route matching for the central
            // domains (where Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains
            // would otherwise block the tenant route). The DashboardService handles
            // cross-context safely (returns empty state when no tenant is set).
            Route::middleware(['web'])
                ->get('/atelier', [AtelierDashboardController::class, 'index'])
                ->name('dashboard.atelier.preview');
        });
    }
}
