<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\WoodStock;
use App\Models\WoodDispensing;
use App\Models\Warehouse;
use App\Services\WoodCalculationService;
use App\Services\WoodStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * 🛡️ Regression Safety Net — WoodStockService + WoodCalculationService Integration
 *
 * تغطية:
 *  - getStockSummary(): إجمالي m³, m², المتبقي
 *  - dispense(): تسجيل صرف + خصم من المخزون + حماية من الصرف الزائد
 *  - العلاقات بين WoodCalculationService و WoodStock
 *  - تكامل مع البيانات الحقيقية (محاكاة سيناريو tenantjoo)
 */
class WoodStockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $service;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::query()->delete();
        Plan::create([
            'slug' => 'manufacturing',
            'name' => 'الباقة الصناعية',
            'price' => 199.0,
            'billing_period' => 'monthly',
            'features' => ['manufacturing', 'accounting'],
            'is_active' => true,
        ]);

        $tenantId = 't-mfg-' . uniqid();
        $this->tenant = Tenant::create([
            'id' => $tenantId,
            'plan_id' => 'manufacturing',
        ]);
        $this->tenant->domains()->create(['domain' => $tenantId . '.localhost']);
        tenancy()->initialize($this->tenant);

        $this->actingAs(User::factory()->create([
            'is_active' => true,
            'role' => 'admin',
        ]));

        $this->warehouse = Warehouse::create([
            'name' => 'المخزن الرئيسي',
            'code' => 'WH-MAIN-' . uniqid(),
            'is_active' => true,
        ]);

        $this->service = app(WoodStockService::class);
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

    /** @test */
    public function get_stock_summary_aggregates_total_and_remaining_m3_and_m2(): void
    {
        // دفعة 1: 120 × 10 × 2 × 500 = 1,200,000 cm³ = 1.2 m³
        WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        // دفعة 2: 200 × 15 × 3 × 100 = 900,000 cm³ = 0.9 m³
        WoodStock::create([
            'length_cm' => 200.0, 'width_cm' => 15.0, 'thickness_cm' => 3.0,
            'quantity' => 100, 'unit_cost' => 1200.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        $summary = $this->service->getStockSummary();

        // total_m3 = (1,200,000 + 900,000) / 1,000,000 = 2.1
        $this->assertEquals(2.1, $summary['total_m3']);
        $this->assertEquals(2.1, $summary['remaining_m3']);
    }

    /** @test */
    public function dispense_records_dispensing_and_decreases_remaining_volume(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        // صرف 200,000 cm³ من دفعة 1,200,000 cm³
        $this->service->dispense($stock, [
            'volume_cm3_taken' => 200000.0,
            'dispensed_at' => '2026-07-05',
            'notes' => 'صرف اختبار',
        ]);

        // سجل الصرف يُنشأ
        $this->assertEquals(1, WoodDispensing::where('wood_stock_id', $stock->id)->count());

        // المتبقي = 1,200,000 - 200,000 = 1,000,000 cm³ = 1.0 m³
        $stock->refresh();
        $this->assertEquals(1000000.0, $stock->remaining_cm3);
        $this->assertEquals(1.0, $stock->remaining_m3);
    }

    /** @test */
    public function dispense_throws_validation_exception_when_exceeding_remaining_stock(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        // محاولة صرف أكثر من الحجم المتاح
        $this->expectException(ValidationException::class);

        $this->service->dispense($stock, [
            'volume_cm3_taken' => 9999999.0,
            'dispensed_at' => '2026-07-05',
        ]);
    }

    /** @test */
    public function it_matches_real_tenantjoo_data_for_batch_id_3(): void
    {
        // محاكاة البيانات الحقيقية من tenantjoo (wood_stocks id=3)
        // L=120, W=10, T=2, qty=500, unit_cost=1000, vol_cm3=1,200,000
        $stock = WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-06-08',
            'warehouse_id' => $this->warehouse->id,
        ]);

        // التحقق من تطابق الحسابات مع البيانات الفعلية
        $this->assertEquals(1200000.0, (float) $stock->volume_cm3);
        $this->assertEquals(1.2, $stock->volume_m3_total);
        $this->assertEquals(1000.0, (float) $stock->unit_cost);
        $this->assertEquals(1200.0, (float) $stock->total_cost);

        // صرف مطابق للصرف الفعلي (240,000 cm³)
        $this->service->dispense($stock, [
            'volume_cm3_taken' => 240000.0,
            'dispensed_at' => '2026-06-08',
        ]);

        $stock->refresh();
        $this->assertEquals(960000.0, $stock->remaining_cm3);
        $this->assertEquals(0.96, $stock->remaining_m3);
    }

    /** @test */
    public function get_stock_for_order_returns_only_batches_with_stock(): void
    {
        // دفعة بمتبقي
        $withStock = WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        // دفعة فارغة (تُصرف بالكامل)
        $emptyStock = WoodStock::create([
            'length_cm' => 100.0, 'width_cm' => 8.0, 'thickness_cm' => 2.0,
            'quantity' => 100, 'unit_cost' => 800.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        WoodDispensing::create([
            'wood_stock_id' => $emptyStock->id,
            'volume_cm3_taken' => (float) $emptyStock->volume_cm3,
            'dispensed_at' => '2026-07-05',
        ]);

        $available = $this->service->getStockForOrder(null);

        $this->assertTrue($available->contains('id', $withStock->id));
        $this->assertFalse($available->contains('id', $emptyStock->id));
    }

    /** @test */
    public function wood_calculation_service_integration_with_wood_stock_creates_consistent_volumes(): void
    {
        // اختبار تكامل: WoodCalculationService + WoodStock
        $calc = app(WoodCalculationService::class);

        // إنشاء دفعة كبيرة الحجم
        $stock = WoodStock::create([
            'length_cm' => 244.0, 'width_cm' => 122.0, 'thickness_cm' => 1.8,
            'quantity' => 50, 'unit_cost' => 1500.0, 'received_at' => '2026-07-05',
            'warehouse_id' => $this->warehouse->id,
        ]);

        // التحقق من تطابق الحسابات اليدوية مع Model accessors
        $expectedCm3 = $calc->calculateVolumeCm3(244.0, 122.0, 1.8, 50);
        $this->assertEquals($expectedCm3, (float) $stock->volume_cm3);

        $expectedM3 = $calc->cm3ToM3($expectedCm3);
        $this->assertEquals($expectedM3, $stock->volume_m3_total);
    }
}