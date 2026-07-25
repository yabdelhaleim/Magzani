<?php

namespace Tests\Unit;

use ReflectionClass;
use Tests\TestCase;

/**
 * اختبارات المرحلة 3 - معالجة الأخطاء في Services.
 */
class Phase3ErrorHandlingTest extends TestCase
{
    /**
     * ✅ CustomerService::update() يحتوي على try-catch
     */
    public function test_customer_service_update_has_try_catch(): void
    {
        $reflection = new ReflectionClass(\App\Services\CustomerService::class);
        $method = $reflection->getMethod('update');
        $source = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $source,
            'CustomerService::update must have try block');
        $this->assertStringContainsString('catch', $source,
            'CustomerService::update must have catch block');
        $this->assertStringContainsString('Log::error', $source,
            'CustomerService::update must log errors');
        $this->assertStringContainsString('RuntimeException', $source,
            'CustomerService::update must throw RuntimeException for users');
    }

    /**
     * ✅ CustomerService::delete() يحتوي على try-catch
     */
    public function test_customer_service_delete_has_try_catch(): void
    {
        $reflection = new ReflectionClass(\App\Services\CustomerService::class);
        $method = $reflection->getMethod('delete');
        $source = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $source);
        $this->assertStringContainsString('catch', $source);
        $this->assertStringContainsString('Log::error', $source);
    }

    /**
     * ✅ CustomerService::updateBalance() يحتوي على try-catch مع التحقق من القيم السالبة
     */
    public function test_customer_service_update_balance_has_validation_and_try_catch(): void
    {
        $reflection = new ReflectionClass(\App\Services\CustomerService::class);
        $method = $reflection->getMethod('updateBalance');
        $source = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $source);
        $this->assertStringContainsString('catch', $source);
        $this->assertStringContainsString("amount < 0", $source,
            'Must validate non-negative amount');
        $this->assertStringContainsString('RuntimeException', $source);
    }

    /**
     * ✅ CustomerService لديه helper موحد handleDbError
     */
    public function test_customer_service_has_db_error_helper(): void
    {
        $reflection = new ReflectionClass(\App\Services\CustomerService::class);
        $this->assertTrue($reflection->hasMethod('handleDbError'),
            'CustomerService must have handleDbError helper method');

        $method = $reflection->getMethod('handleDbError');
        $this->assertTrue($method->isProtected(),
            'handleDbError must be protected');

        $source = $this->getMethodBody($method);
        $this->assertStringContainsString('Log::error', $source);
        $this->assertStringContainsString('RuntimeException', $source);
    }

    /**
     * ✅ SupplierService::create() يحتوي على معالجة أخطاء Query + رسائل ودية
     */
    public function test_supplier_service_create_has_friendly_errors(): void
    {
        $reflection = new ReflectionClass(\App\Services\SupplierService::class);
        $method = $reflection->getMethod('create');
        $source = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $source);
        $this->assertStringContainsString('QueryException', $source,
            'SupplierService::create must catch QueryException');
        $this->assertStringContainsString('Log::error', $source);

        // يجب أن تكون الرسائل بالعربية (ودية للمستخدم)
        $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $source,
            'SupplierService::create must have Arabic error messages');
    }

    /**
     * ✅ SupplierService::delete() يحتوي على try-catch
     */
    public function test_supplier_service_delete_has_try_catch(): void
    {
        $reflection = new ReflectionClass(\App\Services\SupplierService::class);
        $method = $reflection->getMethod('delete');
        $source = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $source);
        $this->assertStringContainsString('catch', $source);
        $this->assertStringContainsString('QueryException', $source);
    }

    /**
     * ✅ SupplierService::updateBalance() يتحقق من القيم السالبة
     */
    public function test_supplier_service_update_balance_validates_negative(): void
    {
        $reflection = new ReflectionClass(\App\Services\SupplierService::class);
        $method = $reflection->getMethod('updateBalance');
        $source = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $source);
        $this->assertStringContainsString("amount < 0", $source,
            'Must validate non-negative amount');
        $this->assertStringContainsString('add', $source);
        $this->assertStringContainsString('subtract', $source);
    }

    /**
     * ✅ كل الخدمات الحساسة لا ترمي استثناءات تقنية للمستخدم (PDOException)
     */
    public function test_services_dont_throw_technical_exceptions(): void
    {
        $serviceMethods = [
            \App\Services\CustomerService::class => ['create', 'update', 'delete', 'updateBalance'],
            \App\Services\SupplierService::class => ['create', 'delete', 'updateBalance'],
        ];

        foreach ($serviceMethods as $serviceClass => $methods) {
            foreach ($methods as $methodName) {
                $reflection = new ReflectionClass($serviceClass);
                if (! $reflection->hasMethod($methodName)) {
                    continue;
                }
                $source = $this->getMethodBody($reflection->getMethod($methodName));

                // يجب ألا يرمي PDOException مباشرة
                $this->assertStringNotContainsString(
                    "throw new \\PDOException",
                    $source,
                    "$serviceClass::$methodName must not throw PDOException directly"
                );

                // يجب أن يرمي RuntimeException بدلاً من ذلك
                if (str_contains($source, 'throw new')) {
                    $this->assertStringContainsString(
                        'RuntimeException',
                        $source,
                        "$serviceClass::$methodName should throw RuntimeException for user"
                    );
                }
            }
        }
    }

    private function getMethodBody(\ReflectionMethod $method): string
    {
        $fileName = $method->getFileName();
        if (! $fileName || ! file_exists($fileName)) {
            return '';
        }

        $source = file_get_contents($fileName);
        $start = $method->getStartLine();
        $length = $method->getEndLine() - $start + 1;

        $lines = explode("\n", $source);
        return implode("\n", array_slice($lines, $start - 1, $length));
    }
}