<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        $this->tenantId = 't-test-' . uniqid();
        
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => $planId,
            'is_suspended' => $isSuspended,
        ]);

        $tenant->domains()->create([
            'domain' => $this->tenantId . '.localhost',
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
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // إنهاء الجلسة المؤقتة حتى يقوم الميدل وير بتهيئتها تلقائياً عبر الدومين
        tenancy()->end();

        // محاكاة طلب باستخدام الرابط المطلق للمستأجر
        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/manufacturing');

        $response->assertForbidden();
    }

    public function test_tenant_with_manufacturing_feature_is_allowed(): void
    {
        $this->tenant = $this->createTestTenant('manufacturing');

        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        tenancy()->end();

        // محاكاة طلب لصفحة التصنيع
        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/manufacturing');

        // يجب أن يسمح بالدخول ولا يُرجع 403
        $response->assertStatus(200);
    }

    public function test_suspended_tenant_is_forbidden_from_everything(): void
    {
        $this->tenant = $this->createTestTenant('basic', true);

        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        tenancy()->end();

        // محاكاة طلب للوحة التحكم الرئيسية للمستأجر المعطل
        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/');

        $response->assertForbidden();
    }
}
