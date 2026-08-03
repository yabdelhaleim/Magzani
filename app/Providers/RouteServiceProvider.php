<?php

namespace App\Providers;

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

            // Central home AND routes/web.php are both domain-scoped and registered
            // once per central domain. Two reasons:
            //   1. The "/" redirect must NOT be shadowed by tenant "/" on tenant hosts.
            //   2. routes/web.php defines /login and /logout which DUPLICATE the same
            //      URIs in routes/tenant.php. Loading web.php with an empty domain
            //      makes its key collide with tenant.php /login (Laravel's RouteCollection
            //      uses method+domain+uri as the table key, see vendor: RouteCollection.php:62),
            //      and the second registration silently overwrites the first — leaving
            //      the central super-admin login unreachable. By scoping each web.php load
            //      with Route::domain($centralDomain), its key becomes unique per host,
            //      and tenant.php /login keeps the empty-domain key.
            foreach (config('tenancy.central_domains', ['localhost', '127.0.0.1']) as $domain) {
                Route::domain($domain)
                    ->middleware('web')
                    ->get('/', function () {
                        return redirect('/super-admin/dashboard');
                    });

                Route::domain($domain)
                    ->middleware('web')
                    ->group(base_path('routes/web.php'));
            }

            Route::group([], base_path('routes/tenant.php'));
        });
    }
}
