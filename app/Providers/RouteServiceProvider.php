<?php

namespace App\Providers;

use App\Http\Controllers\Auth\LoginController;
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
            // The conditional naming trick: only the FIRST iteration gives the routes their
            // canonical names "login" / "logout". Subsequent iterations register the same
            // routes with null names, so Symfony's RouteCollection (used by route:cache) does
            // not throw "Another route has already been assigned name [login]". All N
            // registrations resolve to the same LoginController, so behaviour is identical.
            // LoginController::logout uses a path-relative URL ("/login") to ensure both
            // central and tenant logout flows end up on the correct /login form for their host.
            foreach (array_values(config('tenancy.central_domains', ['localhost', '127.0.0.1'])) as $i => $domain) {
                $loginName  = $i === 0 ? 'login'  : null;
                $logoutName = $i === 0 ? 'logout' : null;

                Route::domain($domain)
                    ->middleware('web')
                    ->group(function () use ($loginName, $logoutName) {
                        Route::middleware('guest')->group(function () use ($loginName) {
                            Route::get('/login', [LoginController::class, 'showLoginForm'])->name($loginName);
                            Route::post('/login', [LoginController::class, 'login'])
                                ->middleware('throttle:5,1');
                        });

                        Route::middleware('auth')->group(function () use ($logoutName) {
                            Route::post('/logout', [LoginController::class, 'logout'])
                                ->name($logoutName);
                        });
                    });
            }

            // Register central (landlord) routes once to avoid duplicate route names.
            // Access is restricted by middleware instead of domain-group looping.
            Route::middleware(['web', 'central.domains'])
                ->group(base_path('routes/web.php'));

            Route::group([], base_path('routes/tenant.php'));
        });
    }
}
