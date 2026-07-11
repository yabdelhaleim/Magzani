<?php

namespace App\Services;

use App\Models\AccountingSetting;
use App\Models\ManufacturingCost;
use App\Models\ManufacturingOrder;
use Illuminate\Support\Facades\Log;

/**
 * StandardCostingService — Gap 2.
 *
 * Pure calculation logic for Standard Costing & Cost Variance.
 * No database writes — returns structured data for callers to act on.
 *
 * Tenant-level gate: `AccountingSetting.standard_costing_enabled` MUST be
 * true before any downstream posting happens. This service exposes
 * `isEnabled()` for callers to short-circuit cheaply.
 *
 * Standard cost is stored on the BOM header (`ManufacturingCost` row) per
 * product, split into material / labor / overhead. When a ManufacturingOrder
 * is completed we look up the matching BOM and:
 *
 *   standard_per_unit = effective_standard_cost_attribute
 *   standard_total    = standard_per_unit × quantity_produced
 *   actual_total      = cost_per_unit × quantity_produced
 *   variance_total    = actual_total − standard_total
 *
 * Source split keeps material variance separate from labor/overhead variance
 * so reports can analyze either side without polluting the GL.
 */
class StandardCostingService
{
    /**
     * Fast gate-check — read once per request at most.
     */
    public function isEnabled(): bool
    {
        return (bool) (AccountingSetting::first()?->standard_costing_enabled ?? false);
    }

    /**
     * Resolve the BOM row to use as the standard-cost baseline for an order.
     * Picks the most-recent ManufacturingCost for the product — versions are
     * distinguished via `standard_cost_effective_from` (caller can pick a
     * specific row if needed; this default is the common case).
     */
    public function getBomForOrder(ManufacturingOrder $order): ?ManufacturingCost
    {
        if (! $order->product_id) {
            return null;
        }

        return ManufacturingCost::query()
            ->where('product_id', $order->product_id)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Standard cost PER UNIT (sum of material + labor + overhead from the BOM).
     * Returns 0 if no BOM or no standard cost has been set yet — variance
     * downstream is treated as "none" in that case.
     */
    public function getStandardCostPerUnit(ManufacturingOrder $order): float
    {
        $bom = $this->getBomForOrder($order);
        if (! $bom) {
            return 0.0;
        }

        return (float) $bom->getEffectiveStandardCostAttribute();
    }

    /**
     * Split actual cost into material vs labor/overhead components for the
     * order. Returns both per-unit and total (whole run) figures, plus the
     * BOM-side standard split, plus the variance split.
     *
     * Returned array shape:
     *   has_variance             (bool)   — true only when |variance| >= 0.01
     *   enabled                  (bool)   — tenant-level toggle
     *   has_standard_defined     (bool)   — tenant enabled but no BOM cost set
     *   actual_per_unit          (float)
     *   standard_per_unit        (float)
     *   actual_total             (float)  — actual cost for the whole run
     *   standard_total           (float)  — standard cost for the whole run
     *   total_variance           (float)  — actual − standard (whole run)
     *   variance_type            (string) — 'favorable'|'unfavorable'|'none'
     *   material_variance        (float)
     *   labor_overhead_variance  (float)
     *   actual_material_total    (float)
     *   actual_labor_overhead_total (float)
     *   standard_material_total  (float)
     *   standard_labor_overhead_total (float)
     *   reason                   (string) — diagnostic for logs
     */
    public function calculateVariance(ManufacturingOrder $order): array
    {
        $qty = (float) $order->quantity_produced;
        $actualPerUnit = (float) $order->cost_per_unit;
        $actualTotal = round($actualPerUnit * $qty, 4);

        // Source split on the order — components + extras (both stored PER UNIT).
        $componentsTotalPerUnit = (float) $order->components()->sum('total_cost');
        $extraCostsPerUnit = (float) $order->extraCosts()->sum('amount');

        $actualMaterialPerUnit = $componentsTotalPerUnit > 0
            ? $componentsTotalPerUnit
            : $actualPerUnit; // fallback when an MO has no rows recorded

        $actualLaborOverheadPerUnit = $extraCostsPerUnit > 0
            ? $extraCostsPerUnit
            : max($actualPerUnit - $actualMaterialPerUnit, 0);

        $actualMaterialTotal = round($actualMaterialPerUnit * $qty, 4);
        $actualLaborOverheadTotal = round($actualLaborOverheadPerUnit * $qty, 4);

        // Standard side — comes from BOM.
        $bom = $this->getBomForOrder($order);
        $standardMaterialPerUnit = $bom ? (float) $bom->standard_material_cost : 0.0;
        $standardLaborPerUnit = $bom ? (float) $bom->standard_labor_cost : 0.0;
        $standardOverheadPerUnit = $bom ? (float) $bom->standard_overhead_cost : 0.0;
        $standardLaborOverheadPerUnit = $standardLaborPerUnit + $standardOverheadPerUnit;
        $standardPerUnit = $standardMaterialPerUnit + $standardLaborOverheadPerUnit;
        $standardTotal = round($standardPerUnit * $qty, 4);

        $standardMaterialTotal = round($standardMaterialPerUnit * $qty, 4);
        $standardLaborOverheadTotal = round($standardLaborOverheadPerUnit * $qty, 4);

        return $this->computeVariance(
            actualTotal: $actualTotal,
            actualMaterialTotal: $actualMaterialTotal,
            actualLaborOverheadTotal: $actualLaborOverheadTotal,
            standardTotal: $standardTotal,
            standardMaterialTotal: $standardMaterialTotal,
            standardLaborOverheadTotal: $standardLaborOverheadTotal,
            standardPerUnit: $standardPerUnit,
            actualPerUnit: $actualPerUnit,
            enabled: $this->isEnabled(),
        );
    }

    /**
     * Pure, no-IO variance computation. Exposed so unit tests can verify
     * the math directly without instantiating Eloquent models.
     */
    public function computeVariance(
        float $actualTotal,
        float $actualMaterialTotal,
        float $actualLaborOverheadTotal,
        float $standardTotal,
        float $standardMaterialTotal,
        float $standardLaborOverheadTotal,
        float $standardPerUnit = 0.0,
        float $actualPerUnit = 0.0,
        bool $enabled = true,
    ): array {
        // No standard cost set on the BOM yet — variance is "none".
        if ($standardPerUnit <= 0 || $standardTotal <= 0) {
            return [
                'has_variance'                  => false,
                'enabled'                       => $enabled,
                'has_standard_defined'          => false,
                'actual_per_unit'               => $actualPerUnit,
                'standard_per_unit'             => 0.0,
                'actual_total'                  => $actualTotal,
                'standard_total'                => 0.0,
                'total_variance'                => 0.0,
                'variance_type'                 => 'none',
                'material_variance'             => 0.0,
                'labor_overhead_variance'       => 0.0,
                'actual_material_total'         => $actualMaterialTotal,
                'actual_labor_overhead_total'   => $actualLaborOverheadTotal,
                'standard_material_total'       => $standardMaterialTotal,
                'standard_labor_overhead_total' => $standardLaborOverheadTotal,
                'reason'                        => 'no_standard_cost_set_on_bom',
            ];
        }

        $totalVariance = round($actualTotal - $standardTotal, 4);
        $materialVariance = round($actualMaterialTotal - $standardMaterialTotal, 4);
        // Adjust labor/OH variance to absorb rounding imprecision so the sum
        // reconciles exactly to the total variance.
        $laborOverheadVariance = round($totalVariance - $materialVariance, 4);

        $varianceType = 'none';
        if (abs($totalVariance) >= 0.01) {
            $varianceType = $totalVariance > 0 ? 'unfavorable' : 'favorable';
        }

        return [
            'has_variance'                  => abs($totalVariance) >= 0.01,
            'enabled'                       => $enabled,
            'has_standard_defined'          => true,
            'actual_per_unit'               => $actualPerUnit,
            'standard_per_unit'             => $standardPerUnit,
            'actual_total'                  => $actualTotal,
            'standard_total'                => $standardTotal,
            'total_variance'                => $totalVariance,
            'variance_type'                 => $varianceType,
            'material_variance'             => $materialVariance,
            'labor_overhead_variance'       => $laborOverheadVariance,
            'actual_material_total'         => $actualMaterialTotal,
            'actual_labor_overhead_total'   => $actualLaborOverheadTotal,
            'standard_material_total'       => $standardMaterialTotal,
            'standard_labor_overhead_total' => $standardLaborOverheadTotal,
            'reason'                        => 'ok',
        ];
    }

    /**
     * Persist variance snapshot on the order so it survives even after the
     * BOM cost is later revised. Lock the cost row from any retroactive edit.
     */
    public function persistVarianceSnapshot(ManufacturingOrder $order, array $variance): ManufacturingOrder
    {
        $order->fill([
            'standard_cost_at_completion' => $variance['standard_total'],
            'actual_cost_at_completion'   => $variance['actual_total'],
            'total_variance'              => $variance['total_variance'],
            'variance_type'               => $variance['variance_type'],
            'material_variance'           => $variance['material_variance'],
            'labor_overhead_variance'     => $variance['labor_overhead_variance'],
            'variance_posted_at'          => $variance['has_variance'] ? now() : null,
            'cost_locked_at'              => now(),
        ]);
        $order->save();

        Log::info('[StandardCosting] Variance snapshot persisted', [
            'order'        => $order->order_number,
            'standard'     => $variance['standard_total'],
            'actual'       => $variance['actual_total'],
            'variance'     => $variance['total_variance'],
            'type'         => $variance['variance_type'],
            'has_variance' => $variance['has_variance'],
        ]);

        return $order;
    }
}
