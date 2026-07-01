<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        // 1. التحقق من وجود مستأجر نشط
        if (!function_exists('tenant') || !tenant()) {
            return $next($request);
        }

        // 2. التحقق من حالة إيقاف الحساب
        if (tenant('is_suspended')) {
            abort(403, 'هذا الحساب معطل حالياً لعدم سداد الاشتراك أو بقرار من الإدارة. يرجى مراجعة الدعم الفني.');
        }

        // إذا لم يتم تحديد ميزة معينة، فهذا يعني أننا نقوم بفحص حالة الحساب فقط (مثل لوحة التحكم)
        if (!$feature) {
            return $next($request);
        }

        // 3. التحقق من صلاحيات الميزات للباقة
        if (!tenant()->hasFeature($feature)) {
            abort(403, 'هذه الميزة غير متوفرة في باقة اشتراكك الحالية أو المخصصة. يرجى الترقية لتفعيلها.');
        }

        return $next($request);
    }
}
