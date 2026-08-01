<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * استثناء للأخطاء التجارية المقصودة (Business Logic Errors)
 * ───────────────────────────────────────────────────────────────
 * يختلف عن RuntimeException العادي:
 *   - RuntimeException → يُرسم 500 Internal Server Error (خلل في النظام)
 *   - BusinessLogicException → يُرسم 422 Unprocessable Entity (خطأ متوقع)
 *
 * استخدمه للأخطاء التي يتوقعها منطق الأعمال مثل:
 *   - تجاوز حد الائتمان
 *   - عدم توفر المخزون
 *   - محاولة إكمال أمر في حالة غير مسموحة
 *   - تعارض في البيانات (مثل تكرار رقم)
 *
 * ⚠️ لا تستخدمه لأخطاء الـ infrastructure (DB connection, API timeout, ...).
 */
class BusinessLogicException extends RuntimeException
{
    /**
     * سياق إضافي للخطأ (مثل: customer_id, invoice_number, ...).
     */
    private array $context;

    public function __construct(string $message, array $context = [], int $code = 0)
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    /**
     * إرجاع السياق الإضافي المرتبط بالخطأ.
     */
    public function getContext(): array
    {
        return $this->context;
    }
}