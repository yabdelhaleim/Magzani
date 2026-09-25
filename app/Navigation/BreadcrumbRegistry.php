<?php

namespace App\Navigation;

use Illuminate\Support\Facades\Route;

/**
 * BreadcrumbRegistry — derives breadcrumbs from the current route name.
 *
 * Convention: route names use dot-notation (e.g. "invoices.sales.index"),
 * we split on "." and translate each segment.
 */
class BreadcrumbRegistry
{
    /**
     * Build a breadcrumb trail for the current route.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    public function current(): array
    {
        $name = Route::currentRouteName();
        if (! $name) {
            return [];
        }

        $segments = explode('.', $name);
        $items = [];
        $items[] = ['label' => 'الرئيسية', 'url' => route('dashboard')];

        // Skip "index" leaves, but keep their parent
        array_pop($segments);
        foreach ($segments as $i => $segment) {
            $items[] = [
                'label' => __($this->translate($segment)),
                'url'   => $i === count($segments) - 1 ? null : null,
            ];
        }

        return $items;
    }

    /**
     * Translate common path segments into human-readable Arabic labels.
     */
    protected function translate(string $segment): string
    {
        return match ($segment) {
            'dashboard'   => 'dashboard.title',
            'invoices'    => 'invoices.title',
            'sales'       => 'sales.title',
            'purchases'   => 'purchases.title',
            'products'    => 'products.title',
            'warehouses'  => 'warehouses.title',
            'customers'   => 'customers.title',
            'suppliers'   => 'suppliers.title',
            'pos'         => 'pos.title',
            'manufacturing'=> 'manufacturing.title',
            'accounting'  => 'accounting.title',
            'reports'     => 'reports.title',
            'settings'    => 'settings.title',
            'users'       => 'users.title',
            'permissions' => 'permissions.title',
            default       => $segment,
        };
    }
}
