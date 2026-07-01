<?php

namespace App\Providers;

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
        \App\Models\Tenant::observe(\App\Observers\TenantObserver::class);

        view()->composer('*', function ($view) {
            if (function_exists('tenant') && tenant()) {
                try {
                    $plan = tenant()->plan;
                    $planFeatures = $plan ? $plan->features->pluck('feature_key') : collect();
                } catch (\Exception $e) {
                    $planFeatures = collect();
                }
                $view->with('planFeatures', $planFeatures);
            } else {
                $view->with('planFeatures', collect());
            }
        });
    }


}
