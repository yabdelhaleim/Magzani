<?php

namespace App\Services;

use App\Models\BatchGenealogy;
use App\Models\FinishedGoodBatch;
use App\Models\ManufacturingOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * BatchGenealogyService — Gap 4.
 *
 * Builds the genealogy graph after a ManufacturingOrder completes:
 *
 *   1. Reads the `MaterialDispensing` rows already created by
 *      `MaterialStockService::dispense()` during MO confirm (the existing
 *      flow — we don't re-create dispensings).
 *
 *   2. Snapshots `source_unit_cost` onto each dispensing (idempotent;
 *      skipped if already set, so re-runs are safe).
 *
 *   3. Creates ONE `finished_good_batches` row representing the
 *      finished-good output of this MO.
 *
 *   4. Inserts one `batch_genealogy` row per (source dispensing,
 *      finished batch) pair — this is the bidirectional linkage.
 *
 *   5. Generates a batch_code if the source batch didn't have one
 *      (legacy rows that predate this gap).
 *
 * Idempotent: re-running on the same MO is a no-op because:
 *  - FinishedGoodBatch.manufacturing_order_id is already populated ⇒ skip.
 *  - batch_genealogy has unique (source, finished) ⇒ upsert behavior.
 *
 * Failure handling: all in a DB::transaction; on exception everything is
 * rolled back. Callers wrap in try/catch and treat failure as a warning
 * (never block the MO completion).
 */
class BatchGenealogyService
{
    /** Pattern: FGB-YYYY-NNNNN */
    public function generateFinishedBatchCode(): string
    {
        $year = Carbon::now()->format('Y');

        // Cheap sequential counter per year from the latest FG batch row.
        $latest = FinishedGoodBatch::query()
            ->where('batch_code', 'like', "FGB-{$year}-%")
            ->orderByDesc('id')
            ->value('batch_code');

        $next = 1;
        if ($latest && preg_match("/FGB-{$year}-(\d+)/", $latest, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return sprintf('FGB-%s-%05d', $year, $next);
    }

    /** Pattern: B-YYYY-NNNNN. Used for legacy source batches missing batch_code. */
    public function generateSourceBatchCode(): string
    {
        $year = Carbon::now()->format('Y');
        $latest = \App\Models\MaterialBatch::query()
            ->where('batch_code', 'like', "B-{$year}-%")
            ->orderByDesc('id')
            ->value('batch_code');

        $next = 1;
        if ($latest && preg_match("/B-{$year}-(\d+)/", $latest, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return sprintf('B-%s-%05d', $year, $next);
    }

    /**
     * Build (or rebuild) the genealogy for an MO.
     * Public so a controller / queue can re-trigger if needed.
     *
     * Returns the new FinishedGoodBatch row, or the existing one if
     * re-invoked for the same MO.
     */
    public function recordGenealogyOnCompletion(ManufacturingOrder $order): ?FinishedGoodBatch
    {
        // 0) Idempotency — if the MO already has a finished batch, return it.
        $existing = FinishedGoodBatch::query()
            ->where('manufacturing_order_id', $order->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $dispenses = $order->materialDispensings()->with('batch')->get();
        if ($dispenses->isEmpty()) {
            Log::info('[BatchGenealogy] No dispensings for MO — skipping', [
                'order' => $order->order_number,
            ]);
            return null;
        }

        try {
            return DB::transaction(function () use ($order, $dispenses) {
                // 1) Snapshot unit cost onto each dispensing (idempotent)
                foreach ($dispenses as $d) {
                    if ($d->source_unit_cost === null && $d->batch) {
                        $d->source_unit_cost = (float) $d->batch->unit_cost;
                        $d->save();
                    }
                }

                // 2) Ensure all source batches have a batch_code (legacy rows).
                foreach ($dispenses as $d) {
                    if ($d->batch && empty($d->batch->batch_code)) {
                        $d->batch->batch_code = $this->generateSourceBatchCode();
                        // Persist original_unit_cost snapshot if not yet locked.
                        if ($d->batch->original_unit_cost === null) {
                            $d->batch->original_unit_cost = (float) $d->batch->unit_cost;
                            $d->batch->original_unit_cost_locked_at = now();
                        }
                        $d->batch->save();
                    }
                }

                // 3) Create the FinishedGoodBatch
                //    `standard_unit_cost` mirrors Gap 2's per-completion snapshot
                //    and stays null for tenants that don't use Standard Costing.
                $fg = FinishedGoodBatch::create([
                    'batch_code'        => $this->generateFinishedBatchCode(),
                    'product_id'        => $order->product_id,
                    'warehouse_id'      => $order->warehouse_id,
                    'manufacturing_order_id' => $order->id,
                    'quantity'          => (float) $order->quantity_produced,
                    'remaining_qty'     => (float) $order->quantity_produced,
                    'unit_cost'         => (float) $order->cost_per_unit,
                    'standard_unit_cost'=> $order->standard_cost_at_completion !== null
                        ? (float) $order->standard_cost_at_completion / max((float) $order->quantity_produced, 1.0)
                        : null,
                    'produced_at'       => $order->produced_at?->toDateString() ?? now()->toDateString(),
                ]);

                // 4) Create one genealogy row per (source dispensing, finished batch)
                foreach ($dispenses as $d) {
                    BatchGenealogy::updateOrCreate(
                        [
                            'source_material_batch_id' => $d->material_batch_id,
                            'finished_good_batch_id'   => $fg->id,
                        ],
                        [
                            'quantity_consumed'         => (float) $d->quantity_taken,
                            'source_unit_cost_snapshot' => (float) ($d->source_unit_cost ?? $d->batch?->unit_cost ?? 0),
                            'consumed_at'               => now(),
                        ]
                    );
                }

                Log::info('[BatchGenealogy] Genealogy built', [
                    'order'        => $order->order_number,
                    'fg_batch'     => $fg->batch_code,
                    'sources'      => $dispenses->count(),
                ]);

                return $fg;
            });
        } catch (\Throwable $e) {
            Log::warning('[BatchGenealogy] Failed for MO ' . $order->order_number . ': ' . $e->getMessage());
            return null;
        }
    }
}
