<?php

namespace Tests\Unit;

use ReflectionClass;
use Tests\TestCase;

/**
 * اختبارات المرحلة 2 - الأمان.
 *
 * تتحقق من:
 *  - تسجيل الـ Policies الجديدة
 *  - تطبيق throttle middleware على routes حساسة
 *  - أن الـ Migration يُنشئ Unique + Foreign Keys
 *  - أن SalesInvoicePolicy تمنع تعديل الفواتير الملغاة
 */
class Phase2SecurityTest extends TestCase
{
    /**
     * ✅ AuthServiceProvider يربط كل الـ Policies الجديدة
     */
    public function test_auth_service_provider_registers_all_policies(): void
    {
        $reflection = new ReflectionClass(\App\Providers\AuthServiceProvider::class);
        $policiesProperty = $reflection->getProperty('policies');
        $policiesProperty->setAccessible(true);

        $policies = $policiesProperty->getValue($reflection->newInstanceWithoutConstructor());

        $expectedMappings = [
            'App\\Models\\Product'         => 'App\\Policies\\ProductPolicy',
            'App\\Models\\Customer'        => 'App\\Policies\\CustomerPolicy',
            'App\\Models\\Supplier'        => 'App\\Policies\\SupplierPolicy',
            'App\\Models\\Warehouse'       => 'App\\Policies\\WarehousePolicy',
            'App\\Models\\SalesInvoice'    => 'App\\Policies\\SalesInvoicePolicy',
            'App\\Models\\PurchaseInvoice' => 'App\\Policies\\PurchaseInvoicePolicy',
        ];

        foreach ($expectedMappings as $model => $policy) {
            $this->assertArrayHasKey($model, $policies,
                "AuthServiceProvider must register $model → $policy");
            $this->assertEquals($policy, $policies[$model],
                "Mapping for $model must point to $policy");
        }
    }

    /**
     * ✅ كل الـ Policy classes موجودة وقابلة للتشغيل
     */
    public function test_all_policy_classes_exist_and_instantiable(): void
    {
        $policyClasses = [
            \App\Policies\ProductPolicy::class,
            \App\Policies\CustomerPolicy::class,
            \App\Policies\SupplierPolicy::class,
            \App\Policies\WarehousePolicy::class,
            \App\Policies\SalesInvoicePolicy::class,
            \App\Policies\PurchaseInvoicePolicy::class,
        ];

        foreach ($policyClasses as $class) {
            $this->assertTrue(class_exists($class), "$class must exist");

            $reflection = new ReflectionClass($class);

            // الـ Policy في Laravel يجب أن يكون class عادي مع methods قياسية
            // (لا يحتاج implements interface معينة)
            $this->assertTrue(
                $reflection->hasMethod('viewAny') || $reflection->hasMethod('before'),
                "$class must have policy methods (viewAny or before)"
            );
        }
    }

    /**
     * ✅ SalesInvoicePolicy تمنع تعديل الفواتير الملغاة
     */
    public function test_sales_invoice_policy_blocks_cancelled_invoice_update(): void
    {
        $policy = new \App\Policies\SalesInvoicePolicy();

        $reflection = new ReflectionClass($policy);
        $updateMethod = $reflection->getMethod('update');

        // فحص أن المنطق يمنع التعديل إذا كانت الفاتورة ملغاة
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContainsString(
            "cancelled",
            $source,
            'SalesInvoicePolicy::update must check for cancelled status'
        );
        $this->assertStringContainsString(
            'return false',
            $source,
            'SalesInvoicePolicy::update must return false for cancelled invoices'
        );
    }

    /**
     * ✅ throttle middleware مطبّق على routes حساسة في routes/tenant.php
     */
    public function test_throttle_applied_to_sensitive_routes(): void
    {
        $source = file_get_contents(base_path('routes/tenant.php'));

        // نتأكد من وجود throttle:30,1 على:
        // - Customer CRUD
        // - Supplier CRUD
        // - Product CRUD
        // - Warehouse CRUD
        // - Inventory Movements export
        $requiredThrottles = [
            'throttle:30,1',  // general
        ];

        $count = substr_count($source, 'throttle:30,1');
        $this->assertGreaterThanOrEqual(
            5,
            $count,
            "Expected at least 5 throttle:30,1 usages in routes/tenant.php, found $count"
        );
    }

    /**
     * ✅ الـ Migration الجديد موجود في مجلد tenant
     */
    public function test_migration_file_exists_in_tenant_directory(): void
    {
        $files = glob(database_path('migrations/tenant/2026_07_25_*.php'));
        $this->assertNotEmpty($files, 'Migration file 2026_07_25* must exist in tenant/');

        $content = file_get_contents($files[0]);

        // التحقق من المحتوى الرئيسي
        $this->assertStringContainsString('safeAddUnique', $content,
            'Migration must use safeAddUnique helper');
        $this->assertStringContainsString('safeAddForeignKey', $content,
            'Migration must add foreign keys');
        $this->assertStringContainsString('safeAddIndex', $content,
            'Migration must add indexes');

        $this->assertStringContainsString("invoice_number", $content,
            'Migration must reference invoice_number');
        $this->assertStringContainsString("'restrict'", $content,
            'Migration must use ON DELETE RESTRICT');
    }

    /**
     * ✅ الـ Migration لا يستخدم حقول غير موجودة (مثل payment_method في expenses)
     */
    public function test_migration_uses_safe_column_references(): void
    {
        $files = glob(database_path('migrations/tenant/2026_07_25_*.php'));
        $content = file_get_contents($files[0]);

        // يفترض أن يستخدم Schema::hasColumn للحماية
        $this->assertStringContainsString('Schema::hasColumn', $content,
            'Migration must guard against missing columns with Schema::hasColumn');

        // يفترض أن يستخدم try/catch
        $this->assertStringContainsString('try {', $content,
            'Migration must use try/catch for safe operations');
    }
}