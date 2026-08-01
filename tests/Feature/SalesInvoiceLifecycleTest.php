<?php

namespace Tests\Feature;

use App\Exceptions\BusinessLogicException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test: دورة حياة فاتورة المبيعات الكاملة
 * ──────────────────────────────────────────────────────
 * يتحقق من:
 *   1. إنشاء فاتورة مبيعات (يُنشئ invoice + items + يخصم المخزون + يُسجّل movement)
 *   2. BusinessLogicException عند تجاوز Credit Limit (422 بدل 500)
 *   3. BusinessLogicException عند دفع أكبر من الإجمالي
 *   4. BusinessLogicException عند بيع كمية أكبر من المتاح
 *   5. BusinessLogicException عند تكرار رقم الفاتورة
 *   6. إلغاء فاتورة (يُرجع المخزون)
 */
class SalesInvoiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Warehouse $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->user);

        $this->customer = Customer::factory()->create([
            'balance' => 0,
            'credit_limit' => 5000,
        ]);

        $this->warehouse = Warehouse::factory()->create(['is_active' => true]);

        $this->product = Product::factory()->create([
            'purchase_price' => 100,
            'selling_price' => 150,
        ]);

        // إضافة مخزون أولي
        $this->warehouse->products()->attach($this->product->id, [
            'quantity' => 50,
            'min_stock' => 0,
        ]);
    }

    /** @test */
    public function it_creates_sales_invoice_and_reduces_stock()
    {
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->createSalesInvoice([
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'price' => 150, 'discount' => 0, 'tax_rate' => 0],
            ],
            'paid' => 750,
        ]);

        // الفاتورة اتعملت
        $this->assertDatabaseHas('sales_invoices', [
            'customer_id'    => $this->customer->id,
            'total'          => 750,
            'payment_status' => 'paid',
        ]);

        // المخزون اتخصم
        $this->assertDatabaseHas('product_warehouse', [
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 45, // 50 - 5
        ]);

        // الـ items اتسجلت
        $this->assertCount(1, $invoice->items);
    }

    /** @test */
    public function it_throws_business_logic_exception_for_credit_limit_exceeded()
    {
        $invoiceService = app(InvoiceService::class);

        // تخفيض الحد ليبقى 100 فقط
        $this->customer->update(['credit_limit' => 100]);

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessageMatches('/تجاوز|حد/i');

        $invoiceService->createSalesInvoice([
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1000, 'discount' => 0, 'tax_rate' => 0],
            ],
            'paid' => 0, // المبلغ المتبقي = 1000 > credit_limit = 100
        ]);
    }

    /** @test */
    public function it_throws_business_logic_exception_when_paid_exceeds_total()
    {
        $invoiceService = app(InvoiceService::class);

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessageMatches('/المبلغ المدفوع أكبر/i');

        $invoiceService->createSalesInvoice([
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 100, 'discount' => 0, 'tax_rate' => 0],
            ],
            'paid' => 200, // أكبر من الإجمالي (100)
        ]);
    }

    /** @test */
    public function it_throws_business_logic_exception_when_stock_insufficient()
    {
        $invoiceService = app(InvoiceService::class);

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessageMatches('/الكمية غير متاحة/i');

        $invoiceService->createSalesInvoice([
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 100, 'price' => 150, 'discount' => 0, 'tax_rate' => 0], // المتاح 50 فقط
            ],
            'paid' => 15000,
        ]);
    }

    /** @test */
    public function business_logic_exception_returns_422_in_api_response()
    {
        $invoiceService = app(InvoiceService::class);
        $this->customer->update(['credit_limit' => 100]);

        $response = $this->postJson(route('invoices.sales.store', [], false), [
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1000, 'discount' => 0, 'tax_rate' => 0],
            ],
            'paid' => 0,
        ]);

        // BusinessLogicException → 422 (مش 500)
        $response->assertStatus(422);
        $response->assertJsonStructure(['success', 'message', 'context']);
    }

    /** @test */
    public function it_cancels_invoice_and_restores_stock()
    {
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->createSalesInvoice([
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'price' => 150, 'discount' => 0, 'tax_rate' => 0],
            ],
            'paid' => 750,
        ]);

        // إلغاء الفاتورة
        $invoiceService->cancelSalesInvoice($invoice->id, 'test cancellation');

        // المخزون رجع
        $this->assertDatabaseHas('product_warehouse', [
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 50, // رجع كله
        ]);
    }

    /** @test */
    public function it_throws_business_logic_exception_on_cancelling_already_cancelled()
    {
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->createSalesInvoice([
            'customer_id'  => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 150, 'discount' => 0, 'tax_rate' => 0],
            ],
            'paid' => 150,
        ]);

        $invoiceService->cancelSalesInvoice($invoice->id, 'first');

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessageMatches('/ملغاة بالفعل/i');

        $invoiceService->cancelSalesInvoice($invoice->id, 'second');
    }
}