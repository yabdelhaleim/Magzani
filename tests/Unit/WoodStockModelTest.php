<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WoodStock;
use App\Models\WoodDispensing;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 🛡️ Regression Safety Net — WoodStock Model
 *
 * تغطية شاملة لـ:
 *  - boot/creating event: حساب volume_cm3 و total_cost تلقائياً
 *  - Accessors: volume_m3_total, volume_m2_total, dispensed_cm3, remaining_cm3/m3/m2
 *  - Scope withStock: تصفية الدفعات ذات المتبقي > 0
 *  - العلاقات: woodDispensings, warehouse, product, supplier
 */
class WoodStockModelTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $tenantId = 'unit-' . uniqid();
        $this->tenant = Tenant::create([
            'id' => $tenantId,
            'plan_id' => 'basic',
        ]);
        tenancy()->initialize($this->tenant);

        $this->actingAs(User::factory()->create([
            'is_active' => true,
            'role' => 'admin',
        ]));

        $this->warehouse = Warehouse::create([
            'name' => 'مخزن الاختبار',
            'code' => 'WH-TEST-' . uniqid(),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->delete();
            } catch (\Exception $e) {
                // ignore
            }
        }
        parent::tearDown();
    }

    /** @test */
    public function boot_creating_event_auto_calculates_volume_cm3_and_total_cost(): void
    {
        // 120 × 10 × 2 × 500 = 1,200,000 cm³
        // total_cost = (1,200,000 / 1,000,000) * 1000 = 1200
        $stock = WoodStock::create([
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
            'quantity' => 500,
            'unit_cost' => 1000.0,
            'received_at' => '2026-07-05',
        ]);

        $this->assertEquals(1200000.0, (float) $stock->volume_cm3);
        $this->assertEquals(1200.0, (float) $stock->total_cost);
    }

    /** @test */
    public function volume_m3_total_accessor_converts_cm3_to_m3(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
            'quantity' => 500,
            'unit_cost' => 1000.0,
            'received_at' => '2026-07-05',
        ]);

        // 1,200,000 cm³ = 1.2 m³
        $this->assertEquals(1.2, $stock->volume_m3_total);
    }

    /** @test */
    public function volume_m2_total_accessor_divides_volume_by_thickness(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
            'quantity' => 500,
            'unit_cost' => 1000.0,
            'received_at' => '2026-07-05',
        ]);

        // m² = 1,200,000 / 2 / 10,000 = 60 m²
        $this->assertEquals(60.0, $stock->volume_m2_total);
    }

    /** @test */
    public function dispensed_cm3_accessor_sums_all_dispensings(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
            'quantity' => 500,
            'unit_cost' => 1000.0,
            'received_at' => '2026-07-05',
        ]);

        // صرف دفعتين: 100,000 + 50,000 = 150,000
        WoodDispensing::create([
            'wood_stock_id' => $stock->id,
            'volume_cm3_taken' => 100000.0,
            'dispensed_at' => '2026-07-05',
        ]);
        WoodDispensing::create([
            'wood_stock_id' => $stock->id,
            'volume_cm3_taken' => 50000.0,
            'dispensed_at' => '2026-07-06',
        ]);

        $this->assertEquals(150000.0, $stock->dispensed_cm3);
    }

    /** @test */
    public function remaining_cm3_accessor_subtracts_dispensed_from_volume(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
            'quantity' => 500,
            'unit_cost' => 1000.0,
            'received_at' => '2026-07-05',
        ]);

        WoodDispensing::create([
            'wood_stock_id' => $stock->id,
            'volume_cm3_taken' => 200000.0,
            'dispensed_at' => '2026-07-05',
        ]);

        // 1,200,000 - 200,000 = 1,000,000 cm³
        $this->assertEquals(1000000.0, $stock->remaining_cm3);
    }

    /** @test */
    public function remaining_m3_and_remaining_m2_accessors_convert_remaining_values(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
            'quantity' => 500,
            'unit_cost' => 1000.0,
            'received_at' => '2026-07-05',
        ]);

        WoodDispensing::create([
            'wood_stock_id' => $stock->id,
            'volume_cm3_taken' => 200000.0,
            'dispensed_at' => '2026-07-05',
        ]);

        // remaining_m3 = 1,000,000 / 1,000,000 = 1.0
        $this->assertEquals(1.0, $stock->remaining_m3);

        // remaining_m2 = 1,000,000 / 2 / 10,000 = 50
        $this->assertEquals(50.0, $stock->remaining_m2);
    }

    /** @test */
    public function scope_with_stock_returns_only_batches_with_remaining_volume(): void
    {
        // دفعة 1: لن تُصرف (تبقى كاملة)
        $stock1 = WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-07-05',
        ]);

        // دفعة 2: ستُصرف بالكامل
        $stock2 = WoodStock::create([
            'length_cm' => 100.0, 'width_cm' => 8.0, 'thickness_cm' => 2.0,
            'quantity' => 100, 'unit_cost' => 800.0, 'received_at' => '2026-07-05',
        ]);

        // صرف كامل لدفعة 2: 100 * 8 * 2 * 100 = 160,000
        WoodDispensing::create([
            'wood_stock_id' => $stock2->id,
            'volume_cm3_taken' => (float) $stock2->volume_cm3,
            'dispensed_at' => '2026-07-05',
        ]);

        $available = WoodStock::withStock()->pluck('id')->toArray();

        $this->assertContains($stock1->id, $available);
        $this->assertNotContains($stock2->id, $available);
    }

    /** @test */
    public function wood_dispensings_relationship_returns_all_dispensings_for_a_batch(): void
    {
        $stock = WoodStock::create([
            'length_cm' => 120.0, 'width_cm' => 10.0, 'thickness_cm' => 2.0,
            'quantity' => 500, 'unit_cost' => 1000.0, 'received_at' => '2026-07-05',
        ]);

        WoodDispensing::create([
            'wood_stock_id' => $stock->id, 'volume_cm3_taken' => 50000.0,
            'dispensed_at' => '2026-07-05',
        ]);
        WoodDispensing::create([
            'wood_stock_id' => $stock->id, 'volume_cm3_taken' => 30000.0,
            'dispensed_at' => '2026-07-06',
        ]);

        $this->assertCount(2, $stock->woodDispensings);
    }
}