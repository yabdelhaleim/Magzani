<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\AccountingSetting;
use App\Models\AccountingPostingFailure;
use App\Models\SalesInvoice;
use App\Models\JournalEntry;
use App\Services\InvoiceService;
use App\Services\ManufacturingOrderService;
use App\Services\Accounting\PostingFailureRetryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostingFailureManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $tenantId = 'test';

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up previous test tenant to avoid conflicts
        try {
            DB::connection('mysql')->table('tenants')->where('id', $this->tenantId)->delete();
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `tenanttest` ");
        } catch (\Exception $e) {
            // Ignore
        }

        Plan::query()->delete();

        Plan::create([
            'slug' => 'advanced-accounting',
            'name' => 'باقة الحسابات المتقدمة',
            'price' => 99.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced', 'manufacturing', 'sales', 'purchases', 'warehouses'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => 'advanced-accounting',
            'is_suspended' => false,
        ]);

        $this->tenant->domains()->create([
            'domain' => $this->tenantId . '.localhost',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                tenancy()->end();
                DB::disconnect('tenant');
                DB::purge('tenant');
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // Ignore
            }
        }
        parent::tearDown();
    }

    private function setupTenantEnvironment()
    {
        tenancy()->initialize($this->tenant);

        // Run seeders
        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();
        (new \Database\Seeders\PermissionAndRoleSeeder())->run();

        // Create Fiscal Year & Period
        $fiscalYear = FiscalYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
            'is_current' => true,
        ]);

        FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);
    }

    /**
     * Test global warning banner visibility and cache behavior.
     */
    public function test_global_warning_banner_and_cache_behavior(): void
    {
        $this->setupTenantEnvironment();

        // Create admin user inside tenant database
        $admin = User::create([
            'name' => 'المدير العام',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Initially no failure, cache should count 0
        $this->assertEquals(0, AccountingPostingFailure::where('resolved', false)->count());

        tenancy()->end();

        $response = $this->actingAs($admin)->get('http://test.localhost/');
        $response->assertStatus(200);
        $response->assertDontSee('يوجد قيود محاسبية معلّقة لم يتم ترحيلها!');

        // Re-initialize tenant to add a failure
        tenancy()->initialize($this->tenant);

        $failure = AccountingPostingFailure::create([
            'source_type' => 'sales_invoice',
            'source_id' => 123,
            'event_key' => 'sales_invoice:123:confirmed',
            'description' => 'فاتورة تجريبية فاشلة',
            'error_class' => \Exception::class,
            'error_message' => 'الفترة المالية مغلقة',
            'attempts' => 1,
            'failed_at' => now(),
            'resolved' => false,
        ]);

        tenancy()->end();

        // Verify cache is cleared instantly on model save, banner is now visible
        $response = $this->actingAs($admin)->get('http://test.localhost/');
        $response->assertSee('يوجد قيود محاسبية معلّقة لم يتم ترحيلها!');

        // Check details page to verify custom type labels
        $response = $this->actingAs($admin)->get('http://test.localhost/accounting/posting-failures');
        $response->assertStatus(200);
        $response->assertSee('فاتورة مبيعات');

        // Re-initialize to resolve
        tenancy()->initialize($this->tenant);
        $failure->update(['resolved' => true, 'resolved_at' => now()]);
        tenancy()->end();

        // Verify banner disappears
        $response = $this->actingAs($admin)->get('http://test.localhost/');
        $response->assertDontSee('يوجد قيود محاسبية معلّقة لم يتم ترحيلها!');
    }

    /**
     * Test strict posting mode blocks operations when unresolved failures exceed threshold.
     */
    public function test_strict_posting_mode_blocks_operations(): void
    {
        $this->setupTenantEnvironment();

        // Configure Settings: strict posting mode = active, max failures = 2
        $settings = AccountingSetting::first();
        $settings->update([
            'strict_posting_mode' => true,
            'max_posting_failures' => 2,
        ]);

        // Add 2 unresolved failures
        AccountingPostingFailure::create([
            'source_type' => 'sales_invoice',
            'source_id' => 101,
            'event_key' => 'sales_invoice:101:confirmed',
            'description' => 'فشل ترحيل 1',
            'error_class' => \Exception::class,
            'error_message' => 'خطأ 1',
            'resolved' => false,
        ]);
        AccountingPostingFailure::create([
            'source_type' => 'sales_invoice',
            'source_id' => 102,
            'event_key' => 'sales_invoice:102:confirmed',
            'description' => 'فشل ترحيل 2',
            'error_class' => \Exception::class,
            'error_message' => 'خطأ 2',
            'resolved' => false,
        ]);

        // Verify checkStrictPostingLimit does not throw since unresolved count (2) is not > max (2)
        $this->assertNull(AccountingSetting::checkStrictPostingLimit());

        // Add a third failure (unresolved count = 3, which is > max 2)
        AccountingPostingFailure::create([
            'source_type' => 'sales_invoice',
            'source_id' => 103,
            'event_key' => 'sales_invoice:103:confirmed',
            'description' => 'فشل ترحيل 3',
            'error_class' => \Exception::class,
            'error_message' => 'خطأ 3',
            'resolved' => false,
        ]);

        // Now, checking strict posting limit should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('❌ لا يمكن إكمال هذه العملية بسبب وجود قيود محاسبية معلّقة لم يتم ترحيلها بنجاح');
        
        AccountingSetting::checkStrictPostingLimit();
    }

    /**
     * Test role-based permissions access for reading and managing posting failures.
     */
    public function test_gates_and_role_access(): void
    {
        $this->setupTenantEnvironment();

        // Create standard employee without the read/manage permissions
        $employee = User::create([
            'name' => 'موظف عادي',
            'email' => 'employee@test.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        // Create an accountant user with role "employee" but with direct posting-failure permissions
        $accountant = User::create([
            'name' => 'المحاسب المعتمد',
            'email' => 'accountant@test.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $readPermission = Permission::where('name', 'accounting.posting-failures.read')->first();
        $managePermission = Permission::where('name', 'accounting.posting-failures.manage')->first();

        $this->assertNotNull($readPermission);
        $this->assertNotNull($managePermission);

        $accountant->permissions()->attach([$readPermission->id, $managePermission->id]);

        $this->assertTrue($accountant->hasPermission('accounting.posting-failures.read'));

        // Clear cached relationships
        $employee->unsetRelations();
        $accountant->unsetRelations();

        tenancy()->end();

        // Accessing the page should return forbidden error for employee
        $response = $this->actingAs($employee)->get('http://test.localhost/accounting/posting-failures');
        $response->assertForbidden();

        // Accessing the index page should load successfully for accountant
        $response = $this->actingAs($accountant)->get('http://test.localhost/accounting/posting-failures');
        $response->assertStatus(200);
        $response->assertSee('مراجعة الترحيلات الفاشلة');
    }

    /**
     * Test retry increments attempts and resolves properly.
     */
    public function test_retry_increments_attempts_on_failure(): void
    {
        $this->setupTenantEnvironment();

        $admin = User::create([
            'name' => 'المدير العام',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create a failure record
        $failure = AccountingPostingFailure::create([
            'source_type' => 'sales_invoice',
            'source_id' => 999, // Non-existent sales invoice to force a retry failure
            'event_key' => 'sales_invoice:999:confirmed',
            'description' => 'ترحيل فاشل سيفشل الإعادة',
            'error_class' => \Exception::class,
            'error_message' => 'الخزينة غير معرفة',
            'attempts' => 1,
            'resolved' => false,
        ]);

        tenancy()->end();

        // Dispatch retry request via route
        $response = $this->actingAs($admin)->post('http://test.localhost/accounting/posting-failures/' . $failure->id . '/retry');
        $response->assertRedirect();
        
        tenancy()->initialize($this->tenant);
        $failure->refresh();
        $this->assertEquals(2, $failure->attempts); // attempts incremented
        $this->assertFalse($failure->resolved);
    }

    /**
     * Test successful retry flow resolves failure, generates journal entry, and removes global warning.
     */
    public function test_retry_successfully_resolves_failure(): void
    {
        $this->setupTenantEnvironment();

        $admin = User::create([
            'name' => 'المدير العام',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create dependencies
        $customer = Customer::create([
            'name' => 'عميل تجريبي',
            'code' => 'CUST-TEST',
            'phone' => '0123456789',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'المخزن الرئيسي',
            'code' => 'WH-001',
        ]);

        $invoice = SalesInvoice::create([
            'invoice_number' => 'INV-TEST-001',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-20',
            'subtotal' => 1000.00,
            'tax_amount' => 0.00,
            'total' => 1000.00,
            'status' => 'confirmed',
            'payment_type' => 'credit',
        ]);

        // Close June/July period
        $period = FiscalPeriod::where('name', 'يوليو 2026')->firstOrFail();
        $period->update(['is_closed' => true]);

        // Post - it fails
        $postingService = app(\App\Services\Accounting\PostingService::class);
        $entry = $postingService->postSalesInvoice($invoice);
        $this->assertNull($entry);

        // Check failure is recorded
        $failure = AccountingPostingFailure::where('resolved', false)->first();
        $this->assertNotNull($failure);
        $this->assertEquals('sales_invoice', $failure->source_type);
        $this->assertEquals($invoice->id, $failure->source_id);

        // Open period
        $period->update(['is_closed' => false]);

        tenancy()->end();

        // Dashboard shows warning banner
        $response = $this->actingAs($admin)->get('http://test.localhost/');
        $response->assertSee('يوجد قيود محاسبية معلّقة لم يتم ترحيلها!');

        // Call retry endpoint
        $response = $this->actingAs($admin)->post("http://test.localhost/accounting/posting-failures/{$failure->id}/retry");
        $response->assertRedirect();

        tenancy()->initialize($this->tenant);

        // Verify solved
        $failure->refresh();
        $this->assertTrue($failure->resolved);
        $this->assertNotNull($failure->resolved_at);

        $postedEntry = JournalEntry::where('source_event_key', "sales_invoice:{$invoice->id}:confirmed")->first();
        $this->assertNotNull($postedEntry);
        // JournalEntry stores debit total in total_debit (not amount)
        $this->assertEquals('1000.00', $postedEntry->total_debit);

        tenancy()->end();

        // Dashboard doesn't show warning banner anymore
        $response = $this->actingAs($admin)->get('http://test.localhost/');
        $response->assertDontSee('يوجد قيود محاسبية معلّقة لم يتم ترحيلها!');
    }
}
