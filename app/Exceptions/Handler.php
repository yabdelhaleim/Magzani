<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // تسجيل جميع الأخطاء
            if (app()->environment('production')) {
                \Log::error('Unhandled exception: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // معالجة أخطاء التحقق من البيانات
        if ($e instanceof ValidationException) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'يرجى التحقق من البيانات المدخلة');
        }

        // معالجة الأخطاء HTTP
        if ($this->isHttpException($e)) {
            $status = $e->getStatusCode();
            $message = match($status) {
                404 => 'الصفحة المطلوبة غير موجودة',
                403 => 'ليس لديك صلاحية للوصول لهذه الصفحة',
                500 => 'حدث خطأ في الخادم',
                503 => 'الخدمة غير متاحة حالياً',
                default => $e->getMessage() ?: 'حدث خطأ ما'
            };

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], $status);
            }

            return response()->view('errors.http', [
                'status' => $status,
                'message' => $message,
            ], $status);
        }

        // معالجة أخطاء قاعدة البيانات
        if ($e instanceof ModelNotFoundException) {
            $message = 'السجل المطلوب غير موجود';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 404);
            }

            return back()->with('error', $message);
        }

        // معالجة أخطاء المصادقة
        if ($e instanceof AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'يجب تسجيل الدخول أولاً');
        }

        // في بيئة الإنتاج، لا نعرض تفاصيل الخطأ
        if (!config('app.debug')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ ما. يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني',
                ], 500);
            }

            return response()->view('errors.500', [
                'message' => 'حدث خطأ ما. يرجى المحاولة مرة أخرى'
            ], 500);
        }

        return parent::render($request, $e);
    }
}
