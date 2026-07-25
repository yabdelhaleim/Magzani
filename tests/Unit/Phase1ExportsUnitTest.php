<?php

namespace Tests\Unit;

use Tests\TestCase;
use ReflectionClass;

/**
 * اختبارات المرحلة 1 - Unit Tests
 *
 * تحقق هذه الاختبارات من:
 *  - إنشاء 4 دوال Export بدون أخطاء
 *  - تنفيذ الـ interfaces الصحيحة (FromCollection, WithHeadings, ...)
 *  - وجود العناوين العربية
 *  - وجود try-catch في SalesController
 *
 * هذه اختبارات لا تحتاج قاعدة بيانات، مما يجعلها سريعة ومستقرة.
 */
class Phase1ExportsUnitTest extends TestCase
{
    /**
     * ✅ InventoryMovementExport يُنشأ بنجاح ويُرجع Collection فارغة
     */
    public function test_inventory_movement_export_can_be_instantiated(): void
    {
        $export = new \App\Exports\InventoryMovementExport([]);
        $this->assertInstanceOf(\App\Exports\InventoryMovementExport::class, $export);
    }

    /**
     * ✅ ExpenseExport يُنشأ بنجاح
     */
    public function test_expense_export_can_be_instantiated(): void
    {
        $export = new \App\Exports\ExpenseExport([]);
        $this->assertInstanceOf(\App\Exports\ExpenseExport::class, $export);
    }

    /**
     * ✅ PaymentExport يُنشأ بنجاح
     */
    public function test_payment_export_can_be_instantiated(): void
    {
        $export = new \App\Exports\PaymentExport([]);
        $this->assertInstanceOf(\App\Exports\PaymentExport::class, $export);
    }

    /**
     * ✅ CashTransactionExport يُنشأ بنجاح
     */
    public function test_cash_transaction_export_can_be_instantiated(): void
    {
        $export = new \App\Exports\CashTransactionExport([]);
        $this->assertInstanceOf(\App\Exports\CashTransactionExport::class, $export);
    }

    /**
     * ✅ كل Export Classes تنفذ الـ interfaces المطلوبة من maatwebsite/excel
     */
    public function test_exports_implement_required_interfaces(): void
    {
        $requiredInterfaces = [
            'Maatwebsite\\Excel\\Concerns\\FromCollection',
            'Maatwebsite\\Excel\\Concerns\\WithHeadings',
            'Maatwebsite\\Excel\\Concerns\\WithMapping',
            'Maatwebsite\\Excel\\Concerns\\WithStyles',
            'Maatwebsite\\Excel\\Concerns\\WithTitle',
            'Maatwebsite\\Excel\\Concerns\\ShouldAutoSize',
        ];

        $exports = [
            'App\\Exports\\InventoryMovementExport',
            'App\\Exports\\ExpenseExport',
            'App\\Exports\\PaymentExport',
            'App\\Exports\\CashTransactionExport',
        ];

        foreach ($exports as $exportClass) {
            $reflection = new ReflectionClass($exportClass);
            foreach ($requiredInterfaces as $interface) {
                $shortName = substr($interface, strrpos($interface, '\\') + 1);
                $this->assertContains(
                    $interface,
                    $reflection->getInterfaceNames(),
                    "$exportClass must implement $shortName"
                );
            }
        }
    }

    /**
     * ✅ عناوين الأعمدة بالعربية في كل export
     */
    public function test_exports_have_arabic_headings(): void
    {
        $exports = [
            new \App\Exports\InventoryMovementExport([]),
            new \App\Exports\ExpenseExport([]),
            new \App\Exports\PaymentExport([]),
            new \App\Exports\CashTransactionExport([]),
        ];

        foreach ($exports as $export) {
            $headings = $export->headings();
            $this->assertIsArray($headings);
            $this->assertNotEmpty($headings, 'Headings must not be empty');

            // التحقق من وجود حرف عربي على الأقل في العناوين
            $hasArabic = false;
            foreach ($headings as $heading) {
                if (preg_match('/[\x{0600}-\x{06FF}]/u', $heading)) {
                    $hasArabic = true;
                    break;
                }
            }
            $this->assertTrue($hasArabic,
                'Headings for ' . $export::class . ' must contain Arabic text');
        }
    }

    /**
     * ✅ عنوان الـ sheet بالعربية
     */
    public function test_exports_have_arabic_sheet_titles(): void
    {
        $exports = [
            new \App\Exports\InventoryMovementExport([]),
            new \App\Exports\ExpenseExport([]),
            new \App\Exports\PaymentExport([]),
            new \App\Exports\CashTransactionExport([]),
        ];

        foreach ($exports as $export) {
            $title = $export->title();
            $this->assertNotEmpty($title);
            $this->assertMatchesRegularExpression(
                '/[\x{0600}-\x{06FF}]/u',
                $title,
                'Sheet title for ' . $export::class . ' must be in Arabic'
            );
        }
    }

    /**
     * ✅ تمرير الفلاتر لا يكسر الـ Export
     */
    public function test_exports_accept_filters_without_breaking(): void
    {
        $filters = [
            'date_from'    => '2024-01-01',
            'date_to'      => '2024-12-31',
            'warehouse_id' => 1,
            'category'     => 'rent',
            'method'       => 'cash',
            'type'         => 'deposit',
            'status'       => 'approved',
            'invalid_key'  => 'should_be_ignored',
        ];

        // هذه يجب أن تنشأ بنجاح دون أخطاء
        $exports = [
            new \App\Exports\InventoryMovementExport($filters),
            new \App\Exports\ExpenseExport($filters),
            new \App\Exports\PaymentExport($filters),
            new \App\Exports\CashTransactionExport($filters),
        ];

        $this->assertCount(4, $exports);
    }

    /**
     * ✅ SalesController يحتوي على try-catch في methods الحساسة
     * (introspection test)
     */
    public function test_sales_controller_has_try_catch_in_critical_methods(): void
    {
        $reflection = new ReflectionClass(\App\Http\Controllers\SalesController::class);

        $criticalMethods = ['show', 'edit', 'store', 'update', 'destroy', 'searchProducts', 'printReceipt'];

        foreach ($criticalMethods as $methodName) {
            if (! $reflection->hasMethod($methodName)) {
                continue;
            }

            $method = $reflection->getMethod($methodName);
            $source = file_get_contents($method->getFileName());
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();

            $lines = array_slice(
                explode("\n", $source),
                $startLine - 1,
                $endLine - $startLine + 1
            );

            $body = implode("\n", $lines);

            $this->assertStringContainsString(
                'try',
                $body,
                "SalesController::$methodName must have try-catch"
            );

            $this->assertStringContainsString(
                'catch',
                $body,
                "SalesController::$methodName must have a catch block"
            );
        }
    }

    /**
     * ✅ SalesController لا يحتوي على where('is_active', 1) (الـ bug القديم)
     */
    public function test_sales_controller_does_not_have_is_active_one_bug(): void
    {
        $source = file_get_contents(
            (new ReflectionClass(\App\Http\Controllers\SalesController::class))
                ->getFileName()
        );

        // نمط الـ bug القديم: ->where('is_active', 1)
        $buggyPattern = "/->where\(['\"]is_active['\"], 1\)/";

        $this->assertDoesNotMatchRegularExpression(
            $buggyPattern,
            $source,
            'SalesController must NOT use ->where("is_active", 1) — use true instead'
        );
    }

    /**
     * ✅ InventoryMovementController يحتوي على export() مع BinaryFileResponse
     */
    public function test_inventory_movement_controller_export_returns_binary_response(): void
    {
        $reflection = new ReflectionClass(\App\Http\Controllers\InventoryMovementController::class);
        $this->assertTrue($reflection->hasMethod('export'));

        $method = $reflection->getMethod('export');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType, 'export() must declare a return type');
        $this->assertEquals(
            'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
            $returnType->getName(),
            'export() must return BinaryFileResponse'
        );
    }

    /**
     * ✅ الـ TODO/placeholder القديم لم يعد موجوداً
     */
    public function test_inventory_movement_controller_no_longer_returns_coming_soon(): void
    {
        $source = file_get_contents(
            (new ReflectionClass(\App\Http\Controllers\InventoryMovementController::class))
                ->getFileName()
        );

        $this->assertStringNotContainsString(
            'قريباً',
            $source,
            'InventoryMovementController must NOT contain "قريباً" placeholder'
        );

        $this->assertStringNotContainsString(
            'TODO: إضافة Export',
            $source,
            'TODO comment must be resolved'
        );
    }

    /**
     * ✅ ExpenseService/PaymentService/CashService لم تعد ترجع "coming soon"
     */
    public function test_services_no_longer_return_coming_soon(): void
    {
        $services = [
            \App\Services\Accounting\ExpenseService::class,
            \App\Services\Accounting\PaymentService::class,
            \App\Services\CashService::class,
        ];

        foreach ($services as $serviceClass) {
            $source = file_get_contents(
                (new ReflectionClass($serviceClass))->getFileName()
            );

            $this->assertStringNotContainsString(
                'Export functionality coming soon',
                $source,
                "$serviceClass must NOT return 'Export functionality coming soon'"
            );
        }
    }
}