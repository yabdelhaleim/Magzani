<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPlanFeature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class SubscriptionFeatureTest extends TestCase
{
    // تمت إزالة RefreshDatabase لتفادي مشاكل عزل المعاملات (Transaction Isolation) بين اتصالي mysql و central

    protected $tenant;

    protected $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        // تنظيف الباقات السابقة لضمان عدم حدوث تكرار
        Plan::query()->delete();

        // 1. إنشاء باقة أساسية بدون ميزة التصنيع
        Plan::create([
            'slug' => 'basic',
            'name' => 'الباقة الأساسية',
            'price' => 19.00,
            'billing_period' => 'monthly',
            'features' => ['accounting'],
            'is_active' => true,
        ]);

        // 2. إنشاء باقة صناعية تدعم التصنيع
        Plan::create([
            'slug' => 'manufacturing',
            'name' => 'الباقة الصناعية',
            'price' => 79.00,
            'billing_period' => 'monthly',
            'features' => ['pos', 'manufacturing', 'accounting'],
            'is_active' => true,
        ]);
    }

    /**
     * دالة مساعدة لإنشاء مستأجر تجريبي بمعرف فريد
     */
    protected function createTestTenant(string $planId = 'basic', bool $isSuspended = false)
    {
        $this->tenantId = 't-test-'.uniqid();

        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => $planId,
            'is_suspended' => $isSuspended,
        ]);

        $tenant->domains()->create([
            'domain' => $this->tenantId.'.localhost',
        ]);

        return $tenant;
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // تجاهل أي أخطاء عند الحذف التلقائي
            }
        }
        parent::tearDown();
    }

    public function test_tenant_without_manufacturing_feature_is_forbidden(): void
    {
        $this->tenant = $this->createTestTenant('basic');

        tenancy()->initialize($this->tenant);

        // إنشاء مستخدم مشرف داخل قاعدة بيانات المستأجر
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@'.$this->tenantId.'.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // إنهاء الجلسة المؤقتة حتى يقوم الميدل وير بتهيئتها تلقائياً عبر الدومين
        tenancy()->end();

        // محاكاة طلب باستخدام الرابط المطلق للمستأجر
        $response = $this->actingAs($admin)
            ->get('http://'.$this->tenantId.'.localhost/manufacturing');

        $response->assertForbidden();
    }

    public function test_tenant_with_manufacturing_feature_is_allowed(): void
    {
        $this->tenant = $this->createTestTenant('manufacturing');

        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@'.$this->tenantId.'.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        tenancy()->end();

        // محاكاة طلب لصفحة التصنيع
        $response = $this->actingAs($admin)
            ->get('http://'.$this->tenantId.'.localhost/manufacturing');

        // يجب أن يسمح بالدخول ولا يُرجع 403
        $response->assertStatus(200);
    }

    public function test_custom_tenant_uses_its_selected_features_and_aliases(): void
    {
        $this->tenantId = 't-custom-'.uniqid();
        $this->tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => 'custom',
            'custom_features' => ['sales', 'warehouses', 'manufacturing', 'accounting'],
            'is_suspended' => false,
        ]);

        tenancy()->initialize($this->tenant);

        $middleware = app(CheckPlanFeature::class);
        $allowed = $middleware->handle(
            Request::create('/pos', 'GET'),
            fn () => response('allowed'),
            'pos'
        );
        $allowedWarehouseAlias = $middleware->handle(
            Request::create('/warehouses', 'GET'),
            fn () => response('allowed'),
            'warehouses'
        );
        $denied = $middleware->handle(
            Request::create('/accounting/advanced', 'GET', [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
            ]),
            fn () => response('allowed'),
            'accounting_advanced'
        );

        $this->assertSame(200, $allowed->getStatusCode());
        $this->assertSame(200, $allowedWarehouseAlias->getStatusCode());
        $this->assertSame(403, $denied->getStatusCode());
        $this->assertTrue($this->tenant->hasFeature('pos'));
        $this->assertTrue($this->tenant->hasFeature('multi_warehouse'));
        $this->assertFalse($this->tenant->hasFeature('accounting_advanced'));

        tenancy()->end();
    }

    public function test_super_admin_plan_update_syncs_feature_rows(): void
    {
        $plan = Plan::create([
            'slug' => 'sync-test',
            'name' => 'باقة اختبار المزامنة',
            'price' => 10.00,
            'billing_period' => 'monthly',
            'features' => ['accounting'],
            'is_active' => true,
        ]);

        $response = $this->put(route('super-admin.plans.update', $plan), [
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => $plan->price,
            'billing_period' => $plan->billing_period,
            'description' => $plan->description,
            'features' => ['sales', 'warehouses', 'accounting_advanced'],
            'value_props' => [],
            'display_label' => $plan->display_label,
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('super-admin.plans.index'));
        $this->assertDatabaseHas('plan_features', [
            'plan_id' => $plan->id,
            'feature_key' => 'pos',
            'is_enabled' => 1,
        ]);
        $this->assertDatabaseHas('plan_features', [
            'plan_id' => $plan->id,
            'feature_key' => 'multi_warehouse',
            'is_enabled' => 1,
        ]);
        $this->assertDatabaseHas('plan_features', [
            'plan_id' => $plan->id,
            'feature_key' => 'accounting_advanced',
            'is_enabled' => 1,
        ]);
        $this->assertDatabaseMissing('plan_features', [
            'plan_id' => $plan->id,
            'feature_key' => 'accounting',
        ]);
    }

    public function test_suspended_tenant_is_forbidden_from_everything(): void
    {
        $this->tenant = $this->createTestTenant('basic', true);

        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@'.$this->tenantId.'.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        tenancy()->end();

        // محاكاة طلب للوحة التحكم الرئيسية للمستأجر المعطل
        $response = $this->actingAs($admin)
            ->get('http://'.$this->tenantId.'.localhost/');

        $response->assertForbidden();
    }
}
