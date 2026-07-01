<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\PosPanel;

class PosFeatureTest extends TestCase
{
    protected $tenant;
    protected $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Clean up plans
        Plan::query()->delete();

        // 2. Create basic plan (without POS)
        Plan::create([
            'slug' => 'basic',
            'name' => 'الباقة الأساسية',
            'price' => 19.00,
            'billing_period' => 'monthly',
            'features' => ['accounting'],
            'is_active' => true,
        ]);

        // 3. Create POS plan
        Plan::create([
            'slug' => 'pos-plan',
            'name' => 'باقة نقاط البيع',
            'price' => 39.00,
            'billing_period' => 'monthly',
            'features' => ['pos', 'accounting'],
            'is_active' => true,
        ]);
    }

    protected function createTestTenant(string $planId = 'basic')
    {
        $this->tenantId = 't-pos-' . uniqid();
        
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => $planId,
            'is_suspended' => false,
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
                // Ignore
            }
        }
        parent::tearDown();
    }

    public function test_tenant_without_pos_feature_is_forbidden(): void
    {
        $this->tenant = $this->createTestTenant('basic');

        tenancy()->initialize($this->tenant);

        // Create admin user in tenant context
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        tenancy()->end();

        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/pos');

        $response->assertForbidden();
    }

    public function test_tenant_with_pos_feature_can_access(): void
    {
        $this->tenant = $this->createTestTenant('pos-plan');

        tenancy()->initialize($this->tenant);

        // Create admin user in tenant context
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create default warehouse and customer (since POS needs it)
        $warehouse = Warehouse::create([
            'name' => 'المستودع الرئيسي',
            'code' => 'WH-MAIN',
            'status' => 'active',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'عميل نقدي',
            'code' => 'CUST-CASH',
            'phone' => '0000000000',
            'is_active' => true,
        ]);

        tenancy()->end();

        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/pos');

        $response->assertStatus(200);
    }

    public function test_pos_livewire_component_functionality(): void
    {
        $this->tenant = $this->createTestTenant('pos-plan');

        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'المستودع الرئيسي',
            'code' => 'WH-MAIN',
            'status' => 'active',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'عميل نقدي',
            'code' => 'CUST-CASH',
            'phone' => '0000000000',
            'is_active' => true,
        ]);

        // Create a product and add stock to warehouse
        $product = Product::create([
            'name' => 'موبايل سامسونج',
            'code' => 'PROD-SAM',
            'sku' => 'SAM-GALAXY',
            'barcode' => '123456789',
            'purchase_price' => 100,
            'selling_price' => 150,
            'is_active' => true,
        ]);

        DB::table('product_warehouse')->insert([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'average_cost' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run Livewire component tests inside tenant context
        $this->actingAs($admin);

        // Create an active shift for the user
        \App\Models\PosShift::create([
            'user_id'         => $admin->id,
            'opened_at'       => now(),
            'opening_balance' => 100.00,
            'status'          => 'open',
            'total_sales'     => 0,
            'total_returns'   => 0,
            'sales_count'     => 0,
            'returns_count'   => 0,
        ]);

        Livewire::test(PosPanel::class)
            ->assertSet('selectedCustomerId', $customer->id)
            ->assertSet('selectedWarehouseId', $warehouse->id)
            // Add product to cart
            ->call('addToCart', $product->id)
            ->assertCount('cart', 1)
            // Verify totals
            ->assertSet('subtotal', 150.00)
            ->assertSet('grand_total', 150.00)
            // Increment qty
            ->call('incrementQuantity', 0)
            ->assertSet('cart.0.quantity', 2)
            ->assertSet('subtotal', 300.00)
            // Submit invoice
            ->call('submitInvoice')
            ->assertHasNoErrors()
            ->assertCount('cart', 0); // Cart is cleared after checkout

        // Verify stock was decremented in database
        $remainingStock = DB::table('product_warehouse')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->value('quantity');

        $this->assertEquals(8, $remainingStock); // 10 - 2 = 8

        tenancy()->end();
    }

    public function test_tenant_without_sales_feature_cannot_access_sales_invoices(): void
    {
        $this->tenant = $this->createTestTenant('basic'); // basic has no sales

        tenancy()->initialize($this->tenant);
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        tenancy()->end();

        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/invoices/sales');

        $response->assertForbidden();
    }

    public function test_tenant_with_sales_feature_can_access_sales_invoices(): void
    {
        // Update basic plan features to include sales
        Plan::where('slug', 'basic')->first()->update([
            'features' => ['sales']
        ]);

        $this->tenant = $this->createTestTenant('basic');

        tenancy()->initialize($this->tenant);
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        tenancy()->end();

        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/invoices/sales');

        $response->assertStatus(200);
    }

    public function test_tenant_without_warehouses_feature_cannot_access_warehouses(): void
    {
        $this->tenant = $this->createTestTenant('basic'); // basic has ['sales'] now or default

        tenancy()->initialize($this->tenant);
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        tenancy()->end();

        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/warehouses');

        $response->assertForbidden();
    }

    public function test_tenant_with_warehouses_feature_can_access_warehouses(): void
    {
        // Update basic plan features to include warehouses
        Plan::where('slug', 'basic')->first()->update([
            'features' => ['warehouses']
        ]);

        $this->tenant = $this->createTestTenant('basic');

        tenancy()->initialize($this->tenant);
        $admin = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'admin@' . $this->tenantId . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        tenancy()->end();

        $response = $this->actingAs($admin)
            ->get('http://' . $this->tenantId . '.localhost/warehouses');

        $response->assertStatus(200);
    }
}
