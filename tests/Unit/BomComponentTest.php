<?php

namespace Tests\Unit;

use App\Models\BomComponent;
use App\Models\ManufacturingCost;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 🛡️ Regression Safety Net — BomComponent
 *
 * يغطي:
 *  - calculateVolume(): صيغة L×W×T×Q (المعادلة الأساسية للـ BOM)
 *  - العلاقة مع ManufacturingCost
 *  - الحقول والـ casts
 */
class BomComponentTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $manufacturingCost;

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

        // ManufacturingCost أبسط لاكتشاف أي كسر
        $this->manufacturingCost = ManufacturingCost::create([
            'total_cost' => 100.0,
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
    public function calculate_volume_multiplies_length_width_thickness_quantity(): void
    {
        $bom = BomComponent::create([
            'manufacturing_cost_id' => $this->manufacturingCost->id,
            'component_name' => 'لوح خشبي',
            'quantity' => 10,
            'length_cm' => 120.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
        ]);

        // 120 × 10 × 2 × 10 = 24,000 cm³
        $this->assertEquals(24000.0, $bom->calculateVolume());
    }

    /** @test */
    public function calculate_volume_works_with_fractional_dimensions(): void
    {
        $bom = BomComponent::create([
            'manufacturing_cost_id' => $this->manufacturingCost->id,
            'component_name' => 'لوح بسمك كسري',
            'quantity' => 5,
            'length_cm' => 200.0,
            'width_cm' => 15.0,
            'thickness_cm' => 1.8,
        ]);

        // 200 × 15 × 1.8 × 5 = 27,000 cm³
        $this->assertEquals(27000.0, $bom->calculateVolume());
    }

    /** @test */
    public function bom_component_belongs_to_manufacturing_cost(): void
    {
        $bom = BomComponent::create([
            'manufacturing_cost_id' => $this->manufacturingCost->id,
            'component_name' => 'لوح اختبار',
            'quantity' => 1,
            'length_cm' => 100.0,
            'width_cm' => 10.0,
            'thickness_cm' => 2.0,
        ]);

        $this->assertNotNull($bom->manufacturingCost);
        $this->assertEquals($this->manufacturingCost->id, $bom->manufacturingCost->id);
    }
}