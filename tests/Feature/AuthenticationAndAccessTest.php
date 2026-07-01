<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationAndAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // تنظيف وحذف أي سجل مستأجر سابق لتفادي تعارض البيانات
        try {
            DB::connection('mysql')->table('tenants')->where('id', 'test')->delete();
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `tenanttest` ");
        } catch (\Exception $e) {
            // تجاهل الأخطاء
        }

        // إنشاء الباقة الافتراضية
        Plan::query()->delete();
        Plan::create([
            'slug' => 'basic',
            'name' => 'الباقة الأساسية',
            'price' => 19.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'pos', 'manufacturing', 'sales', 'purchases', 'warehouses'],
            'is_active' => true,
        ]);

        // إنشاء المستأجر التجريبي وتهيئته
        $this->tenant = Tenant::create([
            'id' => 'test',
            'plan_id' => 'basic',
        ]);
        $this->tenant->domains()->create([
            'domain' => 'test.localhost',
        ]);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                tenancy()->end();
                \Illuminate\Support\Facades\DB::disconnect('tenant');
                \Illuminate\Support\Facades\DB::purge('tenant');
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // تجاهل أخطاء الحذف
            }
        }
        parent::tearDown();
    }

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'uat@example.com',
            'password' => bcrypt('secret-pass'),
            'is_active' => true,
            'role' => 'admin',
        ]);

        // إنهاء تهيئة الجلسة لكي تمر عبر الميدل وير الفعلي
        tenancy()->end();

        $response = $this->post('http://test.localhost/login', [
            'email' => 'uat@example.com',
            'password' => 'secret-pass',
        ]);

        // نقوم بإعادة تهيئة الجلسة لفحص حالة المصادقة
        tenancy()->initialize($this->tenant);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_wrong_password_fails_validation(): void
    {
        User::factory()->create([
            'email' => 'uat@example.com',
            'password' => bcrypt('secret-pass'),
            'is_active' => true,
            'role' => 'admin',
        ]);

        tenancy()->end();

        $response = $this->from('http://test.localhost/login')->post('http://test.localhost/login', [
            'email' => 'uat@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_with_inactive_user_fails_validation(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('secret-pass'),
            'is_active' => false,
            'role' => 'admin',
        ]);

        tenancy()->end();

        $response = $this->from('http://test.localhost/login')->post('http://test.localhost/login', [
            'email' => 'inactive@example.com',
            'password' => 'secret-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        tenancy()->end();

        $response = $this->actingAs($admin)->get('http://test.localhost/');

        $response->assertOk();
    }

    public function test_employee_is_redirected_away_from_dashboard(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        tenancy()->end();

        $response = $this->actingAs($employee)->get('http://test.localhost/');

        $response->assertRedirect(route('invoices.sales.index'));
    }

    public function test_admin_can_open_sales_invoice_create(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $url = 'http://test.localhost/invoices/sales/create';

        tenancy()->end();

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();
    }

    public function test_employee_cannot_open_sales_invoice_create(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $url = 'http://test.localhost/invoices/sales/create';

        tenancy()->end();

        $response = $this->actingAs($employee)->get($url);

        $response->assertForbidden();
        $response->assertJsonFragment(['success' => false]);
    }
}
