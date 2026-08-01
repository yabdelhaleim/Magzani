<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the landlord (central) super-admin dashboard.
 *
 * Central routes must never be reachable by an unauthenticated request or
 * by a regular tenant admin. The platform operator account lives in the
 * central database with the dedicated 'super_admin' role.
 */
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'يرجى تسجيل الدخول أولاً');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'الحساب غير نشط');
        }

        if (! $user->isSuperAdmin()) {
            abort(403, 'هذه الصفحة مخصصة لمسؤولي المنصة فقط.');
        }

        return $next($request);
    }
}
