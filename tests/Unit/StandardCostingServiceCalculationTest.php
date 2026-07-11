<?php

namespace Tests\Unit;

use App\Services\StandardCostingService;
use Tests\TestCase;

/**
 * Unit tests for the pure-math parts of `StandardCostingService`.
 *
 * Uses `computeVariance()` — a no-IO pure function exposed by the
 * service — so we test the math without mocking Eloquent relations
 * (which is brittle). End-to-end behavior against the real DB lives in
 * `tests/Feature/StandardCosting/Gap2StandardCostingTest`.
 */
class StandardCostingServiceCalculationTest extends TestCase
{
    private StandardCostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StandardCostingService();
    }

    public function test_unfavorable_variance_detected_when_actual_exceeds_standard(): void
    {
        // actual = 110/unit, qty=5 ⇒ 550
        // std per unit = 50+30+10 = 90 ⇒ std total = 450
        // variance = +100 (unfavorable)
        // material_variance = 60*5 - 50*5 = +50
        // labor_variance    = 50*5 - 40*5 = +50
        $result = $this->service->computeVariance(
            actualTotal: 550.0,
            actualMaterialTotal: 300.0,
            actualLaborOverheadTotal: 250.0,
            standardTotal: 450.0,
            standardMaterialTotal: 250.0,
            standardLaborOverheadTotal: 200.0,
            standardPerUnit: 90.0,
            actualPerUnit: 110.0,
        );

        $this->assertTrue($result['has_variance']);
        $this->assertEquals('unfavorable', $result['variance_type']);
        $this->assertEquals(550.0, $result['actual_total']);
        $this->assertEquals(450.0, $result['standard_total']);
        $this->assertEquals(100.0, $result['total_variance']);
        $this->assertEquals(50.0, $result['material_variance']);
        $this->assertEquals(50.0, $result['labor_overhead_variance']);
    }

    public function test_favorable_variance_detected_when_actual_below_standard(): void
    {
        $result = $this->service->computeVariance(
            actualTotal: 400.0,
            actualMaterialTotal: 250.0,
            actualLaborOverheadTotal: 150.0,
            standardTotal: 500.0,
            standardMaterialTotal: 300.0,
            standardLaborOverheadTotal: 200.0,
            standardPerUnit: 100.0,
            actualPerUnit: 80.0,
        );

        $this->assertTrue($result['has_variance']);
        $this->assertEquals('favorable', $result['variance_type']);
        $this->assertEquals(-100.0, $result['total_variance']);
        $this->assertEquals(400.0, $result['actual_total']);
        $this->assertEquals(500.0, $result['standard_total']);
    }

    public function test_zero_variance_classified_as_none(): void
    {
        $result = $this->service->computeVariance(
            actualTotal: 500.0,
            actualMaterialTotal: 300.0,
            actualLaborOverheadTotal: 200.0,
            standardTotal: 500.0,
            standardMaterialTotal: 300.0,
            standardLaborOverheadTotal: 200.0,
            standardPerUnit: 100.0,
            actualPerUnit: 100.0,
        );

        $this->assertEquals('none', $result['variance_type']);
        $this->assertEquals(0.0, $result['total_variance']);
        $this->assertFalse($result['has_variance']);
    }

    public function test_no_variance_when_standard_cost_not_set(): void
    {
        $result = $this->service->computeVariance(
            actualTotal: 500.0,
            actualMaterialTotal: 300.0,
            actualLaborOverheadTotal: 200.0,
            standardTotal: 0.0,
            standardMaterialTotal: 0.0,
            standardLaborOverheadTotal: 0.0,
            standardPerUnit: 0.0,           // ← key trigger
            actualPerUnit: 100.0,
        );

        $this->assertEquals('none', $result['variance_type']);
        $this->assertFalse($result['has_variance']);
        $this->assertEquals(500.0, $result['actual_total']);
        $this->assertEquals(0.0, $result['standard_total']);
        $this->assertEquals('no_standard_cost_set_on_bom', $result['reason']);
    }

    public function test_material_and_labor_overhead_variance_split_reconciles(): void
    {
        // actual per-unit: 70 material + 30 labor = 100/unit
        // std  per-unit:  60 material + 25 labor + 0 OH  = 85/unit
        // qty=5 ⇒ actual=500, std=425, variance=+75
        // material_variance = +50, labor_variance = +25 → sums to +75
        $result = $this->service->computeVariance(
            actualTotal: 500.0,
            actualMaterialTotal: 350.0,
            actualLaborOverheadTotal: 150.0,
            standardTotal: 425.0,
            standardMaterialTotal: 300.0,
            standardLaborOverheadTotal: 125.0,
            standardPerUnit: 85.0,
            actualPerUnit: 100.0,
        );

        $this->assertEquals('unfavorable', $result['variance_type']);
        $this->assertEquals(75.0, $result['total_variance']);
        $this->assertEquals(50.0, $result['material_variance']);
        $this->assertEquals(25.0, $result['labor_overhead_variance']);
        $this->assertEquals(
            $result['total_variance'],
            $result['material_variance'] + $result['labor_overhead_variance'],
            'Sum of splits must reconcile to total variance.'
        );
    }

    public function test_rounding_drift_absorbed_into_labor_overhead_variance(): void
    {
        // Spec: even if components don't perfectly reconcile, the total
        // variance is the source of truth and labor/OH absorbs the diff.
        // Use a sub-cent drift (0.005) so it falls below the |variance| >= 0.01
        // threshold and is classified as 'none'.
        $result = $this->service->computeVariance(
            actualTotal: 500.0025,
            actualMaterialTotal: 300.0,
            actualLaborOverheadTotal: 200.0025,
            standardTotal: 500.0,
            standardMaterialTotal: 300.0,
            standardLaborOverheadTotal: 200.0,
            standardPerUnit: 100.0,
            actualPerUnit: 100.0005,
        );

        $this->assertEqualsWithDelta(0.0025, $result['total_variance'], 0.0001);
        $this->assertEquals('none', $result['variance_type']);
        $this->assertFalse($result['has_variance']);
    }

    public function test_toggle_off_carries_through_into_result(): void
    {
        // Even with the tenant toggle off, the math still runs — but the
        // orchestrator should NOT post anything in this case. The
        // `enabled` flag tells callers this row was computed but ignored.
        $result = $this->service->computeVariance(
            actualTotal: 400.0,
            actualMaterialTotal: 250.0,
            actualLaborOverheadTotal: 150.0,
            standardTotal: 500.0,
            standardMaterialTotal: 300.0,
            standardLaborOverheadTotal: 200.0,
            standardPerUnit: 100.0,
            actualPerUnit: 80.0,
            enabled: false,
        );

        $this->assertFalse($result['enabled']);
        $this->assertEquals('favorable', $result['variance_type']);
        $this->assertEquals(-100.0, $result['total_variance']);
    }
}
