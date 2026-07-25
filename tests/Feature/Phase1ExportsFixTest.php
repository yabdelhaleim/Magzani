<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\CashTransaction;
use App\Services\Accounting\ExpenseService;
use App\Services\Accounting\PaymentService;
use App\Services\CashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * اختبارات المرحلة الأولى:
 *  - تنفيذ 4 دوال Export كانت ترجع "coming soon"
 *  - إصلاح SalesController (is_active=true + try-catch)
 *
 * يغطي:
 *  - اختبار Backend: استدعاء الخدمات مباشرة للتحقق من توليد ملف Excel صالح
 *  - اختبار Frontend (محاكاة): زيارة Routes كزائر/مستخدم للتأكد من عدم وجود 500
 */
class Phase1ExportsFixTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Warehouse $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // تنظيف الـ test database
        try {
            DB::connection('mysql')->table('tenants')->where('id', 'phase1test')->delete();
        } catch (\Exception $e) {
            // ignore
        }

        // إنشاء خطة + مستأجر
        Plan::query()->delete();
        Plan::create([
            'slug' => 'basic',
            'name' => 'الباقة الأساسية',
            'price' => 19.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'pos', 'manufacturing', 'warehouses', 'purchase'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'id'   => 'phase1test',
            'plan_id' => 'basic',
        ]);
        $this->tenant->domains()->create([
            'domain' => 'phase1test.localhost',
        ]);

        tenancy()->initialize($this->tenant);

        // تجهيز مخزن + منتج (داخل سياق المستأجر)
        $this->warehouse = Warehouse::create([
            'name'     => 'المخزن الرئيسي',
            'code'     => 'WH-001',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name'          => 'منتج اختبار',
            'code'          => 'P-001',
            'sku'           => 'SKU-001',
            'category'      => 'عام',
            'base_unit'     => 'piece',
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
            'is_active'     => true,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // ignore
            }
        }
        parent::tearDown();
    }

    /**
     * ✅ Backend Test: ExpenseService::exportReport لم يعد يرجع "coming soon"
     */
    public function test_expense_service_export_returns_excel_not_placeholder(): void
    {
        // تجهيز مصروف
        Expense::create([
            'category'        => 'rent',
            'amount'          => 1500.50,
            'expense_date'    => now()->toDateString(),
            'payment_method'  => 'cash',
            'description'     => 'إيجار شهر يناير',
            'reference_number' => 'REF-001',
        ]);

        // استدعاء الخدمة
        $response = app(ExpenseService::class)->exportReport([]);

        // التحقق أنه BinaryFileResponse وليس JSON placeholder
        $this->assertNotInstanceOf(\Illuminate\Http\JsonResponse::class, $response,
            'ExpenseService::exportReport should NOT return a JSON placeholder anymore');

        // يجب أن يكون ملف Excel صالح
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $response,
            'ExpenseService::exportReport must return BinaryFileResponse'
        );

        $this->assertStringContainsString(
            'expenses_',
            $response->headers->get('Content-Disposition') ?? '',
            'Filename must contain expenses_'
        );
    }

    /**
     * ✅ Backend Test: PaymentService::exportReport لم يعد يرجع "coming soon"
     */
    public function test_payment_service_export_returns_excel_not_placeholder(): void
    {
        Payment::create([
            'payable_type'    => 'App\\Models\\Supplier',
            'payable_id'      => 1,
            'amount'          => 2500.00,
            'payment_method'  => 'bank',
            'payment_date'    => now()->toDateString(),
            'reference_number' => 'PAY-001',
            'notes'           => 'دفعة للمورد',
        ]);

        $response = app(PaymentService::class)->exportReport([]);

        $this->assertNotInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $response
        );
    }

    /**
     * ✅ Backend Test: CashService::exportReport لم يعد يرجع "coming soon"
     */
    public function test_cash_service_export_returns_excel_not_placeholder(): void
    {
        CashTransaction::create([
            'transaction_number' => 'CT-001',
            'transaction_type'   => 'deposit',
            'amount'             => 5000.00,
            'transaction_date'   => now()->toDateString(),
            'description'        => 'إيداع افتتاحي',
            'created_by'         => 1,
        ]);

        $response = app(CashService::class)->exportReport([]);

        $this->assertNotInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $response
        );
    }

    /**
     * ✅ Export Class Direct Test: InventoryMovementExport ينتج Collection صحيحة
     */
    public function test_inventory_movement_export_collects_data(): void
    {
        DB::table('inventory_movements')->insert([
            'movement_number'  => 'MOV-001',
            'warehouse_id'     => $this->warehouse->id,
            'product_id'       => $this->product->id,
            'movement_type'    => 'purchase',
            'quantity'         => 100,
            'quantity_change'  => 100,
            'quantity_before'  => 0,
            'quantity_after'   => 100,
            'unit_cost'        => 10.00,
            'movement_date'    => now(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $export = new \App\Exports\InventoryMovementExport([]);
        $collection = $export->collection();

        $this->assertCount(1, $collection, 'Should collect 1 movement');
        $this->assertEquals('MOV-001', $collection->first()->movement_number);
        $this->assertEquals('شراء', $export->map($collection->first())[2],
            'Movement type purchase must translate to شراء');
    }

    /**
     * ✅ Frontend Simulation Test: SalesController::index لا يعطي 500 بعد إصلاح is_active
     */
    public function test_sales_controller_index_does_not_500_after_fix(): void
    {
        // يجب أن يكون auth::admin مع feature:pos
        $user = \App\Models\User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user, 'web')
            ->get('http://phase1test.localhost/invoices/sales')
            ->assertStatus(200);
    }

    /**
     * ✅ Frontend Test: SalesController::show يرجع 404 للفاتورة غير الموجودة
     * (تأكد أن try-catch الجديد يلتقط ModelNotFoundException)
     */
    public function test_sales_controller_show_returns_404_for_missing_invoice(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user, 'web')
            ->get('http://phase1test.localhost/invoices/sales/99999')
            ->assertStatus(404);
    }

    /**
     * ✅ Bug Fix Verification: is_active=true (boolean) يجب أن يجلب السجلات
     *
     * كان يستخدم ->where('is_active', 1) — يعمل على tinyint(1) في MySQL
     * تم استبداله بـ ->where('is_active', true) — وهو الـ standard
     *
     * هذا الاختبار يضمن أن السلوك لا يزال يعمل بعد التعديل.
     */
    public function test_is_active_boolean_query_works(): void
    {
        \App\Models\Customer::create([
            'name'      => 'عميل نشط',
            'is_active' => true,
        ]);

        \App\Models\Customer::create([
            'name'      => 'عميل معطل',
            'is_active' => false,
        ]);

        // نفس الـ query المستخدم في SalesController
        $active = \App\Models\Customer::where('is_active', true)->get();

        $this->assertCount(1, $active, 'Only the active customer should be returned');
        $this->assertEquals('عميل نشط', $active->first()->name);
    }
}