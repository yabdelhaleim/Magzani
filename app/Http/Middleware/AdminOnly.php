<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ✅ Admin-Only Middleware
 *
 * Protects routes that ONLY admins can access
 * Uses HTTP method + route pattern (not route names)
 *
 * Usage:
 * Route::delete('/products/{id}', ...)->middleware('admin.only')
 * Route::prefix('manufacturing')->middleware('admin.only')
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Must be authenticated first
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'يرجى تسجيل الدخول أولاً');
        }

        // Must be admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => '❌ هذه العملية للمدير فقط'
            ], 403);
        }

        return $next($request);
    }
}
