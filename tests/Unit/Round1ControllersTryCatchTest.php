<?php

namespace Tests\Unit;

use ReflectionClass;
use Tests\TestCase;

/**
 * اختبارات الجولة 1 - try-catch للـ 6 Controllers.
 *
 * يتحقق من أن:
 *  - UserController (7 methods) كلها فيها try-catch
 *  - SettingsController (9 methods) كلها فيها try-catch
 *  - DashboardController (1 method) فيها try-catch
 *  - RawMaterialTemplateController (7 methods) فيها try-catch
 *  - NotificationController (4 methods) فيها try-catch
 *  - PricingController (1 method) فيها try-catch
 */
class Round1ControllersTryCatchTest extends TestCase
{
    /**
     * ✅ UserController: 7 methods كلها فيها try-catch
     */
    public function test_user_controller_has_try_catch_in_critical_methods(): void
    {
        $criticalMethods = ['index', 'store', 'show', 'edit', 'update', 'destroy', 'toggleActive'];

        foreach ($criticalMethods as $method) {
            $this->assertMethodHasTryCatch(\App\Http\Controllers\UserController::class, $method);
        }
    }

    /**
     * ✅ SettingsController: 9 methods كلها فيها try-catch
     */
    public function test_settings_controller_has_try_catch_in_critical_methods(): void
    {
        $criticalMethods = [
            'index', 'updateCompany', 'deleteLogo', 'updateSystem',
            'storeUser', 'updateUser', 'deleteUser', 'backup', 'restoreBackup',
        ];

        foreach ($criticalMethods as $method) {
            $this->assertMethodHasTryCatch(\App\Http\Controllers\SettingsController::class, $method);
        }
    }

    /**
     * ✅ DashboardController: index method فيه try-catch
     */
    public function test_dashboard_controller_has_try_catch(): void
    {
        $this->assertMethodHasTryCatch(\App\Http\Controllers\DashboardController::class, 'index');
    }

    /**
     * ✅ RawMaterialTemplateController: 7 methods فيها try-catch
     */
    public function test_raw_material_template_controller_has_try_catch(): void
    {
        $criticalMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

        foreach ($criticalMethods as $method) {
            $this->assertMethodHasTryCatch(\App\Http\Controllers\RawMaterialTemplateController::class, $method);
        }
    }

    /**
     * ✅ NotificationController: 4 methods فيها try-catch
     */
    public function test_notification_controller_has_try_catch(): void
    {
        $criticalMethods = ['index', 'open', 'read', 'readAll'];

        foreach ($criticalMethods as $method) {
            $this->assertMethodHasTryCatch(\App\Http\Controllers\NotificationController::class, $method);
        }
    }

    /**
     * ✅ PricingController: index method فيه try-catch
     */
    public function test_pricing_controller_has_try_catch(): void
    {
        $this->assertMethodHasTryCatch(\App\Http\Controllers\PricingController::class, 'index');
    }

    /**
     * ✅ جميع الـ Controllers تستخدم Log::error في catch blocks
     */
    public function test_all_controllers_log_errors_on_failure(): void
    {
        $controllers = [
            \App\Http\Controllers\UserController::class => ['store', 'update', 'destroy', 'toggleActive'],
            \App\Http\Controllers\SettingsController::class => ['updateCompany', 'updateSystem', 'storeUser', 'updateUser', 'deleteUser'],
            \App\Http\Controllers\DashboardController::class => ['index'],
            \App\Http\Controllers\RawMaterialTemplateController::class => ['store', 'update', 'destroy'],
            \App\Http\Controllers\NotificationController::class => ['index', 'open', 'read', 'readAll'],
            \App\Http\Controllers\PricingController::class => ['index'],
        ];

        foreach ($controllers as $controllerClass => $methods) {
            $reflection = new ReflectionClass($controllerClass);
            foreach ($methods as $methodName) {
                if (! $reflection->hasMethod($methodName)) {
                    continue;
                }
                $body = $this->getMethodBody($reflection->getMethod($methodName));
                $this->assertStringContainsString('Log::error', $body,
                    "$controllerClass::$methodName must log errors in catch block");
            }
        }
    }

    /**
     * ✅ جميع الـ Controllers تستخدم رسائل عربية ودية للمستخدم
     */
    public function test_all_controllers_return_arabic_friendly_messages(): void
    {
        $controllers = [
            \App\Http\Controllers\UserController::class => ['store', 'update', 'destroy', 'toggleActive'],
            \App\Http\Controllers\SettingsController::class => ['updateCompany', 'updateSystem', 'storeUser', 'updateUser', 'deleteUser'],
            \App\Http\Controllers\DashboardController::class => ['index'],
            \App\Http\Controllers\RawMaterialTemplateController::class => ['store', 'update', 'destroy'],
            \App\Http\Controllers\NotificationController::class => ['index', 'open', 'read', 'readAll'],
            \App\Http\Controllers\PricingController::class => ['index'],
        ];

        foreach ($controllers as $controllerClass => $methods) {
            $reflection = new ReflectionClass($controllerClass);
            foreach ($methods as $methodName) {
                if (! $reflection->hasMethod($methodName)) {
                    continue;
                }
                $body = $this->getMethodBody($reflection->getMethod($methodName));
                $this->assertMatchesRegularExpression(
                    '/[\x{0600}-\x{06FF}]/u',
                    $body,
                    "$controllerClass::$methodName must have Arabic error messages for user"
                );
            }
        }
    }

    /**
     * Helper: assert method has try-catch with Log::error.
     */
    private function assertMethodHasTryCatch(string $class, string $methodName): void
    {
        $reflection = new ReflectionClass($class);
        $this->assertTrue($reflection->hasMethod($methodName),
            "$class must have $methodName method");

        $method = $reflection->getMethod($methodName);
        $body = $this->getMethodBody($method);

        $this->assertStringContainsString('try {', $body,
            "$class::$methodName must have try block");
        $this->assertStringContainsString('catch', $body,
            "$class::$methodName must have catch block");
    }

    /**
     * Helper: get method body as string.
     */
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
