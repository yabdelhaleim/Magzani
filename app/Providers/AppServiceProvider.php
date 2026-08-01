<?php

namespace App\Providers;

use App\Models\Tenant;
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
        //
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
    }
}
