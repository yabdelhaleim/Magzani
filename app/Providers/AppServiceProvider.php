<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Navigation\BreadcrumbRegistry;
use App\Navigation\NavRegistry;
use App\Observers\TenantObserver;
use App\Support\NotificationPresenter;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NavRegistry::class);
        $this->app->singleton(BreadcrumbRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Tenant::observe(TenantObserver::class);

        view()->composer('*', function ($view) {
            if (function_exists('tenant') && tenant()) {
                try {
                    $plan = tenant()->plan;
                    $planFeatures = $plan
                        ? $plan->features
                            ->pluck('feature_key')
                            ->map(fn (string $feature) => Tenant::resolveFeatureKey($feature))
                            ->unique()
                            ->values()
                        : collect();
                } catch (\Exception $e) {
                    $planFeatures = collect();
                }
                $view->with('planFeatures', $planFeatures);
            } else {
                $view->with('planFeatures', collect());
            }
        });

        view()->composer('layouts.app', function ($view): void {
            $headerNotifications = collect();
            $headerUnreadCount = 0;

            if (Auth::check()) {
                try {
                    $importantTypes = config('notifications.important_types', []);
                    $limit = (int) config('notifications.dropdown_limit', 8);
                    $user = Auth::user();

                    $headerNotifications = $user->notifications()
                        ->whereIn('type', $importantTypes)
                        ->latest()
                        ->limit($limit)
                        ->get()
                        ->map(fn (DatabaseNotification $notification): array => NotificationPresenter::present($notification));

                    $headerUnreadCount = $user->unreadNotifications()
                        ->whereIn('type', $importantTypes)
                        ->count();
                } catch (\Throwable $e) {
                    Log::warning('Failed to load header notifications', [
                        'user_id' => Auth::id(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $view->with(compact('headerNotifications', 'headerUnreadCount'));
        });

        // Atelier Design System: force Vite assets to use the global (non-tenant)
        // asset URL. Without this, Stancl Tenancy's `asset_helper_tenancy` rewrites
        // Vite build URLs to `/tenancy/assets/{path}` which depends on a working
        // tenant asset route. The global asset URL bypasses the tenancy asset
        // abstraction and serves files directly from the public path.
        \Illuminate\Support\Facades\Vite::createAssetPathsUsing(function (string $path, ?bool $secure = null) {
            // stancl/tenancy global_asset helper — uses the original (non-tenant) URL generator.
            return global_asset($path);
        });
    }
}
