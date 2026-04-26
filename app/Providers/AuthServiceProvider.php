<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => \App\Policies\ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // التحقق من الصلاحيات قبل كل شيء
        \Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // تعريف بوابة عامة للصلاحيات
        \Gate::define('warehouse.transfers.read', function ($user) {
            return $user->hasPermission('warehouse.transfers.read');
        });

        \Gate::define('warehouse.transfers.create', function ($user) {
            return $user->hasPermission('warehouse.transfers.create');
        });

        \Gate::define('warehouse.transfers.update', function ($user) {
            return $user->hasPermission('warehouse.transfers.update');
        });

        \Gate::define('warehouse.transfers.delete', function ($user) {
            return $user->hasPermission('warehouse.transfers.delete');
        });

        // تحديد صلاحية المستخدم
        \Gate::define('users.permissions', function ($user) {
            return $user->isAdmin() || $user->hasPermission('users.permissions');
        });
    }
}
