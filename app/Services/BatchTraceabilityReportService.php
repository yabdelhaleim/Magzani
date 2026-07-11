<?php

namespace App\Services;

use App\Models\BatchGenealogy;
use App\Models\FinishedGoodBatch;
use App\Models\MaterialBatch;
use Illuminate\Support\Collection;

/**
 * BatchTraceabilityReportService — Gap 4.
 *
 * Bidirectional trace queries against the batch_genealogy graph. Pure
 * read-only — no DB writes.
 *
 * Two entry points:
 *   - traceBackwards(finishedBatchCode)  → which raw batches fed into X.
 *   - traceForwards(sourceBatchCode)     → which finished batches did X feed.
 *   - statusOf(MaterialBatch)           → human-readable batch lifecycle label.
 */
class BatchTraceabilityReportService
{
    /**
     * Forward lookup: given a SOURCE (raw) batch code, list every FG batch
     * it contributed to, with consumption amounts.
     */
    public function traceForwards(string $sourceBatchCode): Collection
    {
        $source = MaterialBatch::where('batch_code', $sourceBatchCode)->first();
        if (! $source) {
            return collect();
        }

        return BatchGenealogy::query()
            ->with('finishedBatch.product')
            ->where('source_material_batch_id', $source->id)
            ->orderBy('consumed_at')
            ->get()
            ->map(function (BatchGenealogy $link) use ($source) {
                return [
                    'source_batch_code'      => $source->batch_code,
                    'source_remaining_qty'   => (float) $source->remaining_qty,
                    'finished_batch_code'    => $link->finishedBatch?->batch_code,
                    'finished_product_name'  => $link->finishedBatch?->product?->name ?? '',
                    'quantity_consumed'      => (float) $link->quantity_consumed,
                    'source_unit_cost'       => (float) $link->source_unit_cost_snapshot,
                    'consumed_at'            => $link->consumed_at?->toDateTimeString(),
                ];
            });
    }

    /**
     * Reverse lookup: given a FINISHED batch code, list every source raw
     * batch it consumed from. This is the " من أي خامات جاء هذا المنتج" view.
     */
    public function traceBackwards(string $finishedBatchCode): Collection
    {
        $finished = FinishedGoodBatch::where('batch_code', $finishedBatchCode)->first();
        if (! $finished) {
            return collect();
        }

        return BatchGenealogy::query()
            ->with('sourceBatch.product')
            ->where('finished_good_batch_id', $finished->id)
            ->orderBy('consumed_at')
            ->get()
            ->map(function (BatchGenealogy $link) use ($finished) {
                return [
                    'finished_batch_code'    => $finished->batch_code,
                    'finished_remaining_qty' => (float) $finished->remaining_qty,
                    'source_batch_code'      => $link->sourceBatch?->batch_code,
                    'source_product_name'    => $link->sourceBatch?->product?->name ?? '',
                    'quantity_consumed'      => (float) $link->quantity_consumed,
                    'source_unit_cost'       => (float) $link->source_unit_cost_snapshot,
                    'consumed_at'            => $link->consumed_at?->toDateTimeString(),
                ];
            });
    }

    /**
     * Human-readable lifecycle status of a raw batch:
     *   - in_stock           still has remaining qty
     *   - fully_consumed     no remaining, but consumed via genealogy
     *   - sold_through       some/all of its FG descendants sold
     *   - untracked          no genealogy (legacy / pre-Gap-4)
     */
    public function statusOf(MaterialBatch $batch): string
    {
        $remaining = (float) $batch->remaining_qty;
        $hasGenealogy = $batch->genealogyLinks()->exists();

        if (! $hasGenealogy) {
            return 'untracked';
        }

        if ($remaining > 0.0001) {
            return 'partially_consumed';
        }

        // All consumed — is any descendant FG batch still in stock?
        $anyDescendantInStock = false;
        foreach ($batch->genealogyLinks()->with('finishedBatch')->get() as $link) {
            if ($link->finishedBatch && (float) $link->finishedBatch->remaining_qty > 0.0001) {
                $anyDescendantInStock = true;
                break;
            }
        }

        return $anyDescendantInStock ? 'descendants_in_stock' : 'sold_through';
    }
}
