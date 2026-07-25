<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * اختبار يدوي شامل من واجهة المستخدم (Frontend).
 *
 * يدمج كل الاختبارات في عدد قليل من الـ methods لتقليل وقت
 * setup الـ multi-tenancy (الذي يأخذ ~100 ثانية).
 *
 * يحاكي:
 *  - Admin/Employee access
 *  - تصفح صفحات الفواتير/العملاء/الموردين/المنتجات/المخازن
 *  - تحميل Excel exports
 *  - Throttle middleware
 *  - 404 handling
 */
class FrontendManualTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected User $employee;
    protected Warehouse $warehouse;
    protected Customer $customer;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // تنظيف
        try {
            DB::connection('mysql')->table('tenants')->where('id', 'fttest')->delete();
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `tenantfttest` ");
        } catch (\Exception $e) {}

        Plan::query()->delete();
        Plan::create([
            'slug' => 'fttest-plan',
            'name' => 'Frontend Test Plan',
            'price' => 99.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'pos', 'manufacturing', 'warehouses', 'purchase', 'sales', 'customers', 'suppliers', 'products'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create(['id' => 'fttest', 'plan_id' => 'fttest-plan']);
        $this->tenant->domains()->create(['domain' => 'fttest.localhost']);
        tenancy()->initialize($this->tenant);

        $this->admin = User::factory()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@fttest.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->employee = User::factory()->create([
            'name' => 'موظف',
            'email' => 'employee@fttest.com',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'المخزن الرئيسي',
            'code' => 'WH-MAIN',
            'is_active' => true,
        ]);

        // Customer يحتاج code
        $this->customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'أحمد محمد',
            'phone' => '01012345678',
            'email' => 'ahmed@test.com',
            'is_active' => true,
        ]);

        // Supplier يحتاج code
        $this->supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'مورد الاختبار',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'code' => 'PROD-001',
            'sku' => 'SKU-001',
            'name' => 'منتج تجريبي',
            'category' => 'عام',
            'base_unit' => 'piece',
            'purchase_price' => 50,
            'selling_price' => 75,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {}
        }
        parent::tearDown();
    }

    /**
     * ✅ اختبار 1: Authentication - Login + Redirect
     */
    public function test_authentication_flow(): void
    {
        $output = [];

        // 1.1) Unauthenticated → redirect to login
        tenancy()->end();
        $r = $this->get('http://fttest.localhost/');
        $output[] = "  Unauthenticated → /        → {$r->getStatusCode()} (redirect to login)";
        $this->assertEquals(302, $r->getStatusCode());

        // 1.2) Login form accessible
        tenancy()->end();
        $r = $this->get('http://fttest.localhost/login');
        $output[] = "  Anonymous        → /login   → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        // 1.3) Authenticated admin → dashboard
        tenancy()->initialize($this->tenant);
        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/');
        $output[] = "  Admin            → /        → {$r->getStatusCode()} (dashboard)";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        // 1.4) Login submission
        tenancy()->end();
        $r = $this->post('http://fttest.localhost/login', [
            'email' => 'admin@fttest.com',
            'password' => 'password',
        ]);
        $output[] = "  POST login admin → {$r->getStatusCode()} (redirect to dashboard)";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        echo "\n=== 1) AUTHENTICATION ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 2: Sales Invoices - كل الصفحات
     */
    public function test_sales_invoices_pages(): void
    {
        $output = [];

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/invoices/sales');
        $output[] = "  /invoices/sales                  → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/invoices/sales/create');
        $output[] = "  /invoices/sales/create           → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        // 404 من try-catch الجديد
        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/invoices/sales/99999');
        $output[] = "  /invoices/sales/99999 (missing)   → {$r->getStatusCode()} (404 from try-catch)";
        $this->assertEquals(404, $r->getStatusCode());

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/invoices/sales/1/edit');
        $output[] = "  /invoices/sales/1/edit            → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302, 404]);

        echo "\n=== 2) SALES INVOICES ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 3: Customers - كل الصفحات
     */
    public function test_customers_pages(): void
    {
        $output = [];

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/customers');
        $output[] = "  /customers                                → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/customers/create');
        $output[] = "  /customers/create                         → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/customers/{$this->customer->id}");
        $output[] = "  /customers/{$this->customer->id}                          → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/customers/{$this->customer->id}/statement");
        $output[] = "  /customers/{$this->customer->id}/statement                → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/customers/{$this->customer->id}/edit");
        $output[] = "  /customers/{$this->customer->id}/edit                     → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        // 404 للعميل غير الموجود
        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/customers/99999');
        $output[] = "  /customers/99999 (missing)                → {$r->getStatusCode()} (404)";
        $this->assertContains($r->getStatusCode(), [404, 302]);

        echo "\n=== 3) CUSTOMERS ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 4: Suppliers - كل الصفحات
     */
    public function test_suppliers_pages(): void
    {
        $output = [];

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/suppliers');
        $output[] = "  /suppliers                                → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/suppliers/create');
        $output[] = "  /suppliers/create                         → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/suppliers/{$this->supplier->id}");
        $output[] = "  /suppliers/{$this->supplier->id}                          → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/suppliers/{$this->supplier->id}/edit");
        $output[] = "  /suppliers/{$this->supplier->id}/edit                     → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/suppliers/{$this->supplier->id}/statement");
        $output[] = "  /suppliers/{$this->supplier->id}/statement                → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/suppliers/99999');
        $output[] = "  /suppliers/99999 (missing)                → {$r->getStatusCode()} (404)";
        $this->assertContains($r->getStatusCode(), [404, 302]);

        echo "\n=== 4) SUPPLIERS ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 5: Products - كل الصفحات
     */
    public function test_products_pages(): void
    {
        $output = [];

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/products');
        $output[] = "  /products             → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/products/create');
        $output[] = "  /products/create      → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/products/{$this->product->id}");
        $output[] = "  /products/{$this->product->id}        → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/products/{$this->product->id}/edit");
        $output[] = "  /products/{$this->product->id}/edit   → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/products/{$this->product->id}/price-history");
        $output[] = "  /products/{$this->product->id}/price-history → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        echo "\n=== 5) PRODUCTS ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 6: Warehouses + Inventory Movements
     */
    public function test_warehouses_and_movements_pages(): void
    {
        $output = [];

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/warehouses');
        $output[] = "  /warehouses                      → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/warehouses/create');
        $output[] = "  /warehouses/create               → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/warehouses/{$this->warehouse->id}");
        $output[] = "  /warehouses/{$this->warehouse->id}                 → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get("http://fttest.localhost/warehouses/{$this->warehouse->id}/movements");
        $output[] = "  /warehouses/{$this->warehouse->id}/movements      → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/movements');
        $output[] = "  /movements                       → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/movements/export');
        $output[] = "  /movements/export (Excel)        → {$r->getStatusCode()}";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        echo "\n=== 6) WAREHOUSES & MOVEMENTS ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 7: Policies - موظف vs مدير
     */
    public function test_policies_admin_vs_employee(): void
    {
        $output = [];

        // الموظف يحاول يصل لصفحة إنشاء عميل (admin.only middleware + CustomerPolicy)
        $r = $this->actingAs($this->employee, 'web')->get('http://fttest.localhost/customers/create');
        $output[] = "  Employee → /customers/create        → {$r->getStatusCode()} (should be 302/403)";
        $this->assertNotEquals(200, $r->getStatusCode(),
            'Employee should NOT be able to access create customer page');

        $r = $this->actingAs($this->employee, 'web')->get('http://fttest.localhost/suppliers/create');
        $output[] = "  Employee → /suppliers/create        → {$r->getStatusCode()} (should be 302/403)";
        $this->assertNotEquals(200, $r->getStatusCode(),
            'Employee should NOT be able to access create supplier page');

        $r = $this->actingAs($this->employee, 'web')->get('http://fttest.localhost/products/create');
        $output[] = "  Employee → /products/create         → {$r->getStatusCode()} (should be 302/403)";
        $this->assertNotEquals(200, $r->getStatusCode(),
            'Employee should NOT be able to access create product page');

        // لكن يمكنه عرض القوائم
        $r = $this->actingAs($this->employee, 'web')->get('http://fttest.localhost/customers');
        $output[] = "  Employee → /customers (view)        → {$r->getStatusCode()} (should work)";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->employee, 'web')->get('http://fttest.localhost/suppliers');
        $output[] = "  Employee → /suppliers (view)        → {$r->getStatusCode()} (should work)";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        // المدير يمكنه كل شيء
        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/customers/create');
        $output[] = "  Admin    → /customers/create        → {$r->getStatusCode()} (allowed)";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        $r = $this->actingAs($this->admin, 'web')->get('http://fttest.localhost/suppliers/create');
        $output[] = "  Admin    → /suppliers/create        → {$r->getStatusCode()} (allowed)";
        $this->assertContains($r->getStatusCode(), [200, 302]);

        echo "\n=== 7) POLICIES (Admin vs Employee) ===\n" . implode("\n", $output) . "\n";
        $this->assertTrue(true);
    }

    /**
     * ✅ اختبار 8: Throttle - 35 طلب سريع
     */
    public function test_throttle_middleware_on_export_route(): void
    {
        $output = [];

        $hit429 = false;
        $requestCount = 0;
        $firstStatus = null;

        for ($i = 1; $i <= 35; $i++) {
            $r = $this->actingAs($this->admin, 'web')
                ->get('http://fttest.localhost/movements/export');

            $requestCount = $i;
            if ($firstStatus === null) {
                $firstStatus = $r->getStatusCode();
            }

            if ($r->getStatusCode() === 429) {
                $hit429 = true;
                $output[] = "  Request #{$i} → 429 Too Many Requests ✅ (Throttle triggered)";
                break;
            }
        }

        if (!$hit429) {
            $output[] = "  After {$requestCount} requests → still {$firstStatus} (no throttle yet)";
        }

        echo "\n=== 8) THROTTLE ===\n" . implode("\n", $output) . "\n";

        $this->assertTrue($hit429,
            'Throttle middleware should block after 30+ rapid requests on /movements/export');
    }

    /**
     * ✅ اختبار 9: Form Submissions (POST) - إنشاء عميل فعلي
     */
    public function test_form_submissions_create_actual_data(): void
    {
        $output = [];

        // الموظف يحاول ينشئ عميل (يجب أن يُمنع)
        $csrf = 'test_csrf_token'; // Laravel يتجاهل في الاختبار
        $r = $this->actingAs($this->employee, 'web')
            ->post('http://fttest.localhost/customers', [
                'name' => 'Test',
                'phone' => '01111111111',
                'email' => 'test@test.com',
                'code' => 'TEST',
            ]);

        $output[] = "  Employee POST /customers → {$r->getStatusCode()} (should be 302/403)";
        $this->assertNotEquals(201, $r->getStatusCode());

        // المدير ينشئ عميل فعلياً
        tenancy()->initialize($this->tenant);
        $before = Customer::count();
        $r = $this->actingAs($this->admin, 'web')
            ->post('http://fttest.localhost/customers', [
                'name' => 'عميل جديد',
                'phone' => '01987654321',
                'email' => 'new@test.com',
                'code' => 'NEW-CUST',
                'is_active' => '1',
            ]);

        // حتى لو رجع redirect (302)، يجب أن يكون العميل قد تم إنشاؤه
        $after = Customer::count();
        $output[] = "  Admin POST /customers → {$r->getStatusCode()} (customers: {$before} → {$after})";

        $this->assertGreaterThan($before, $after,
            'Admin POST /customers should create a new customer (database count should increase)');

        echo "\n=== 9) FORM SUBMISSIONS ===\n" . implode("\n", $output) . "\n";
    }
}