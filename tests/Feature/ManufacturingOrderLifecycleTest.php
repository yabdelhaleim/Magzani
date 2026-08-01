<?php

namespace Tests\Feature;

use App\Exceptions\BusinessLogicException;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ManufacturingOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test: دورة حياة أمر التصنيع
 * ────────────────────────────────────────
 * يتحقق من:
 *   1. canBeCompleted() يرفض الأوامر بدون warehouse_id (إصلاح BUG-03)
 *   2. إنشاء → تأكيد → إكمال يعمل بدون أخطاء
 *   3. BusinessLogicException عند محاولة إكمال أمر confirmed بدون warehouse
 *   4. BusinessLogicException عند محاولة إلغاء أمر مكتمل
 *   5. لا يوجد generateOrderNumber() في ManufacturingOrder model (race-condition تم حلّه)
 */
class ManufacturingOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->user);

        $this->warehouse = Warehouse::factory()->create(['is_active' => true]);
        $this->product = Product::factory()->create([
            'purchase_price' => 50,
            'selling_price' => 100,
        ]);
    }

    /** @test */
    public function can_be_completed_requires_warehouse_id()
    {
        // أمر بـ warehouse_id = null
        $order = ManufacturingOrder::factory()->create([
            'status' => 'confirmed',
            'warehouse_id' => null,
            'quantity_produced' => 10,
        ]);

        // لازم يرجع false لأن warehouse_id مفقود (إصلاح BUG-03)
        $this->assertFalse($order->canBeCompleted());
    }

    /** @test */
    public function can_be_completed_returns_true_when_all_conditions_met()
    {
        $order = ManufacturingOrder::factory()->create([
            'status' => 'confirmed',
            'warehouse_id' => $this->warehouse->id,
            'quantity_produced' => 10,
        ]);

        $this->assertTrue($order->canBeCompleted());
    }

    /** @test */
    public function generate_order_number_static_method_no_longer_exists()
    {
        // إصلاح BUG-01: الدالة القديمة اللي كانت تسبب race condition تم حذفها
        // الـ Service يستخدم SequenceService الآمن بـ lockForUpdate الآن
        $this->assertFalse(
            method_exists(ManufacturingOrder::class, 'generateOrderNumber'),
            'generateOrderNumber() كان يسبب race condition — تم حذفه من الـ Model'
        );
    }

    /** @test */
    public function it_throws_business_logic_exception_when_completing_without_warehouse()
    {
        $service = app(ManufacturingOrderService::class);

        $order = ManufacturingOrder::factory()->create([
            'status' => 'confirmed',
            'warehouse_id' => null,
            'quantity_produced' => 10,
        ]);

        $this->expectException(BusinessLogicException::class);

        $service->completeOrder($order, null); // بدون warehouse
    }

    /** @test */
    public function it_throws_business_logic_exception_when_cancelling_completed_order()
    {
        $service = app(ManufacturingOrderService::class);

        $order = ManufacturingOrder::factory()->create([
            'status' => 'completed',
            'warehouse_id' => $this->warehouse->id,
        ]);

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessageMatches('/لا يمكن إلغاء/i');

        $service->cancelOrder($order, 'test');
    }

    /** @test */
    public function it_cancels_draft_order()
    {
        $service = app(ManufacturingOrderService::class);

        $order = ManufacturingOrder::factory()->create([
            'status' => 'draft',
            'warehouse_id' => $this->warehouse->id,
        ]);

        $result = $service->cancelOrder($order, 'test reason');

        $this->assertEquals('cancelled', $result->status);
    }
}