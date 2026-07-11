<?php

namespace App\Services;

use App\Models\FinishedGoodBatch;
use App\Models\MaterialBatch;
use Illuminate\Support\Collection;

/**
 * BatchImpactSplitService — Gap 4.
 *
 * Pure-function service: takes primitive numbers plus lightweight DTOs and
 * returns the per-account impact split for a late-invoice price adjustment.
 *
 * No DB writes; the orchestrator (LateInvoicePriceAdjustmentService) reads
 * the genealogy graph and feeds the results here.
 *
 * Decimal precision is decimal(15,4) throughout — fractional quantities
 * (e.g. 22.5 wood units in a chair) must reconcile exactly:
 *
 *    inventory_impact + cogs_impact == price_diff * total_quantity
 *
 * If reconciliation fails (rounding drift), the service returns
 * `fallback_required = true` so the orchestrator posts the entire diff to
 * account 5160 (Gap 2) instead of guessing.
 *
 * Inputs are deliberately primitive so the service is unit-testable
 * without instantiating Eloquent models.
 */
class BatchImpactSplitService
{
    /**
     * Compute the impact split for one source batch + one price_diff.
     *
     * Math (per the approved Gap 4 plan):
     *   - raw_remaining × priceDiff        ⇒  inventory_impact_raw
     *   - For each FG descendant of this source batch, the line
     *     raw_qty_consumed (from batch_genealogy.quantity_consumed)
     *     is split by sold_through_ratio of that FG:
     *       sold_raw          = raw × sold_through_ratio         ⇒ cogs
     *       finished_in_stock = raw × (1 − sold_through_ratio)   ⇒ inventory_finished
     *
     * @param array{
     *   raw_remaining_qty: float,
     *   finished_batches: list<array{
     *     raw_qty_consumed: float,
     *     fg_remaining_qty: float,
     *     fg_total_qty:     float,
     *   }>
     * } $genealogy
     *
     * @return array{
     *   inventory_impact_raw: float,
     *   inventory_impact_finished: float,
     *   inventory_impact: float,
     *   cogs_impact: float,
     *   total_quantity_affected: float,
     *   fallback_required: bool,
     *   fallback_reason: ?string,
     *   breakdown: list<array{label: string, qty: float, impact: float}>
     * }
     */
    public function compute(
        float $priceDiff,
        float $rawRemainingQty,
        array $finishedBatches,
    ): array {
        if (abs($priceDiff) < 0.0001) {
            return [
                'inventory_impact_raw'      => 0.0,
                'inventory_impact_finished' => 0.0,
                'inventory_impact'          => 0.0,
                'cogs_impact'               => 0.0,
                'total_quantity_affected'   => 0.0,
                'fallback_required'         => false,
                'fallback_reason'           => null,
                'breakdown'                 => [],
            ];
        }

        // 1) Raw material still in stock ⇒ inventory impact (raw side).
        $invRawQty = round($rawRemainingQty, 4);
        $invRawImpact = round($invRawQty * $priceDiff, 4);

        // 2) Descendants: each split by sold_through_ratio of its FG batch.
        $invFinishedImpact = 0.0;
        $cogsImpact = 0.0;
        $rawConsumedTotal = 0.0;
        $breakdown = [];

        $breakdown[] = [
            'label'   => 'مخزون خام متبقي',
            'qty'     => $invRawQty,
            'impact'  => $invRawImpact,
        ];

        foreach ($finishedBatches as $idx => $fb) {
            $rawConsumed = (float) ($fb['raw_qty_consumed'] ?? 0);
            $fgRemaining = (float) ($fb['fg_remaining_qty'] ?? 0);
            $fgTotal     = (float) ($fb['fg_total_qty'] ?? 0);

            if ($rawConsumed <= 0) {
                continue;
            }

            $soldThroughRatio = $fgTotal > 0
                ? round(($fgTotal - $fgRemaining) / $fgTotal, 6)
                : 0.0;

            $soldRaw = round($rawConsumed * $soldThroughRatio, 4);
            $finishedRaw = round($rawConsumed - $soldRaw, 4);

            $soldImpact = round($soldRaw * $priceDiff, 4);
            $finishedImpact = round($finishedRaw * $priceDiff, 4);

            $cogsImpact = round($cogsImpact + $soldImpact, 4);
            $invFinishedImpact = round($invFinishedImpact + $finishedImpact, 4);

            $rawConsumedTotal = round($rawConsumedTotal + $rawConsumed, 4);

            $breakdown[] = [
                'label'   => "Batch #{$idx} — خام في كراسي مباعة ({$soldRaw})",
                'qty'     => $soldRaw,
                'impact'  => $soldImpact,
            ];
            $breakdown[] = [
                'label'   => "Batch #{$idx} — خام في كراسي مخزون ({$finishedRaw})",
                'qty'     => $finishedRaw,
                'impact'  => $finishedImpact,
            ];
        }

        // 3) Reconciliation: raw_remaining + raw_consumed == total bearing impact
        $totalQty = round($invRawQty + $rawConsumedTotal, 4);
        $expectedTotal = round($totalQty * $priceDiff, 4);
        $sum = round($invRawImpact + $invFinishedImpact + $cogsImpact, 4);

        $drift = round(abs($expectedTotal - $sum), 4);
        if ($drift > 0.02) {
            return [
                'inventory_impact_raw'      => 0.0,
                'inventory_impact_finished' => 0.0,
                'inventory_impact'          => 0.0,
                'cogs_impact'               => 0.0,
                'total_quantity_affected'   => $totalQty,
                'fallback_required'         => true,
                'fallback_reason'           => "reconciliation_drift: expected={$expectedTotal}, got={$sum}",
                'breakdown'                 => $breakdown,
            ];
        }

        $inventoryImpact = round($invRawImpact + $invFinishedImpact, 4);

        return [
            'inventory_impact_raw'      => $invRawImpact,
            'inventory_impact_finished' => $invFinishedImpact,
            'inventory_impact'          => $inventoryImpact,
            'cogs_impact'               => $cogsImpact,
            'total_quantity_affected'   => $totalQty,
            'fallback_required'         => false,
            'fallback_reason'           => null,
            'breakdown'                 => $breakdown,
        ];
    }

    /**
     * Public wrapper that accepts a MaterialBatch + price diff, walks
     * the batch_genealogy graph itself, and synthesizes the genealogy
     * subgraph the pure `compute()` expects.
     *
     * This is the bridge between the Eloquent world and the pure math;
     * the heavy lifting is still done by `compute()` above.
     *
     * For each FinishedGoodBatch that descended from this raw batch, the
     * `quantity_consumed` field of the matching genealogy row is used —
     * NOT the produced quantity of the FG — because the per-source-batch
     * impact is the genealogy-level consumed qty × priceDiff.
     *
     * @param MaterialBatch $batch
     * @param float $priceDiff signed per-unit delta
     */
    public function computeForBatch(
        MaterialBatch $batch,
        float $priceDiff,
    ): array {
        $subgraph = $batch->genealogyLinks()
            ->with('finishedBatch')
            ->get()
            ->map(function ($link) {
                $fg = $link->finishedBatch;
                return [
                    'raw_qty_consumed' => (float) $link->quantity_consumed,
                    'fg_remaining_qty' => $fg ? (float) $fg->remaining_qty : 0.0,
                    'fg_total_qty'     => $fg ? (float) $fg->quantity : 0.0,
                ];
            })->toArray();

        $rawRemainingQty = (float) $batch->remaining_qty;

        return $this->compute($priceDiff, $rawRemainingQty, $subgraph);
    }
}
