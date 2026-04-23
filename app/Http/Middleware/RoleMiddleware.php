<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Usage in routes:
     * - role:admin - only admins can access
     * 
     * Admin: can access everything
     * Employee: cannot access accounting, reports, and settings
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يرجى تسجيل الدخول أولاً');
        }

        $user = Auth::user();

        // If user is not active, log them out
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'الحساب غير نشط');
        }

        // Check if user has role column
        if (!$user->role) {
            // If no role, set as employee by default
            $user->role = 'employee';
            $user->save();
        }

        // Admin can access everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // For employees, check which routes they can access
        $currentRoute = $request->route()->getName();
        
        // Routes that only admin can access
        $adminOnlyRoutes = [
            'accounting.treasury',
            'accounting.payments',
            'accounting.expenses.index',
            'accounting.expenses.store',
            'accounting.expenses.update',
            'accounting.expenses.destroy',
            'accounting.statistics',
            'accounting.deposits.store',
            'accounting.withdrawals.store',
            'accounting.transactions.update',
            'accounting.transactions.destroy',
            'reports.inventory',
            'reports.financial',
            'reports.profit-loss',
            'settings.index',
            'settings.update',
            'dashboard',
            'warehouses.index',
            'warehouses.create',
            'warehouses.store',
            'warehouses.show',
            'warehouses.edit',
            'warehouses.update',
            'warehouses.destroy',
            'warehouses.add-product',
            'warehouses.products.store',
            'warehouses.low-stock',
            'warehouses.movements',
            'warehouses.search',
            'stock-counts.index',
            'stock-counts.create',
            'stock-counts.store',
            'stock-counts.show',
            'stock-counts.start',
            'stock-counts.count',
            'stock-counts.complete',
            'stock-counts.cancel',
            'stock-counts.items.approve',
            'stock-counts.approve-all',
            'stock-counts.items.count',
            'stock-counts.warehouse-products',
            'stock-counts.print',
            'manufacturing.index',
            'manufacturing.create',
            'manufacturing.store',
            'manufacturing.calculate',
            'manufacturing.show',
            'manufacturing.edit',
            'manufacturing.update',
            'manufacturing.destroy',
            'manufacturing.confirm',
        ];

        // Check if current route is admin-only
        foreach ($adminOnlyRoutes as $adminRoute) {
            if (str_starts_with($currentRoute, $adminRoute) || $currentRoute === $adminRoute) {
                return redirect()->route('invoices.sales.index')->with('error', 'ليس لديك صلاحية للوصول لهذه الصفحة');
            }
        }

        return $next($request);
    }
}
