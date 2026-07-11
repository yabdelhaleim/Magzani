<?php

namespace App\Services;

use App\Models\BatchGenealogy;
use App\Models\BatchPriceAdjustment;
use App\Models\FinishedGoodBatch;
use App\Models\JournalEntry;
use App\Models\MaterialBatch;
use App\Models\PurchaseInvoiceItem;
use App\Services\Accounting\PostingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * LateInvoicePriceAdjustmentService — Gap 4.
 *
 * Workflow:
 *   1. Receive a PurchaseInvoiceItem + a new (corrected) unit cost.
 *   2. Resolve the affected MaterialBatch via material_batch_purchase_links.
 *   3. Walk the genealogy to build the list of downstream FG batches.
 *   4. Hand off the math to BatchImpactSplitService.
 *   5. Post a journal entry via PostingService (per Q5: ONE entry per
 *      PurchaseInvoiceItem, never combining two batches).
 *   6. Record an audit row in batch_price_adjustments.
 *   7. Update material_batch.unit_cost to the new price.
 *   8. If fallback fires, post everything to account 5160 (Gap 2).
 *
 * Idempotent on re-invocation: a unique `source_event_key` per JE,
 * stored in batch_price_adjustments.journal_entry_id.
 */
class LateInvoicePriceAdjustmentService
{
    public function __construct(
        private BatchImpactSplitService $splitService,
        private PostingService $postingService,
    ) {}

    /**
     * Main entry point.
     *
     * Returns the persisted BatchPriceAdjustment audit row.
     */
    public function adjustLateInvoicePrice(
        PurchaseInvoiceItem $item,
        float $newUnitCost,
    ): BatchPriceAdjustment {
        $userId = Auth::id();

        // 1) Resolve affected material batch via the purchase-links bridge.
        $batch = MaterialBatch::query()
            ->whereHas('purchaseLinks', function ($q) use ($item) {
                $q->where('purchase_invoice_item_id', $item->id);
            })
            ->first();

        if (! $batch) {
            // No link set up between this invoice item and any batch.
            // Surface as a validation error, not a fallback — the caller
            // (Controller) should prompt the user to pick the batch first.
            throw new \RuntimeException(
                "No MaterialBatch linked to PurchaseInvoiceItem #{$item->id}. " .
                'Please set up the batch link before recording a price adjustment.'
            );
        }

        // 2) Snapshot the original cost (NEVER overwrite batch.original_unit_cost).
        $originalUnitCost = (float) ($batch->original_unit_cost ?? $batch->unit_cost ?? 0);
        $priceDiff = round($newUnitCost - $originalUnitCost, 4);

        if (abs($priceDiff) < 0.0001) {
            // Nothing to do — supplier re-billed at the same price.
            return BatchPriceAdjustment::create([
                'purchase_invoice_item_id'  => $item->id,
                'material_batch_id'         => $batch->id,
                'original_unit_cost'         => $originalUnitCost,
                'new_unit_cost'              => $newUnitCost,
                'price_diff'                 => $priceDiff,
                'total_quantity_affected'    => 0,
                'inventory_impact'           => 0,
                'cogs_impact'                => 0,
                'fallback_used'              => false,
                'journal_entry_id'           => null,
                'applied_by'                 => $userId,
            ]);
        }

        // 3) Walk the genealogy to find downstream FG batches.
        //    Even if the link exists, BATCH MIGHT have NO genealogy rows
        //    (i.e. created BEFORE this gap, no MO has consumed it yet via
        //    the new service). In that case the split is just raw_remaining
        //    qty * priceDiff ⇒ all goes to inventory.
        $descendants = $this->collectDescendantFGBatches($batch);

        // 4) Compute the impact split. The service walks the genealogy
        //    graph itself, so we only pass the source batch + the diff.
        $split = $this->splitService->computeForBatch(
            batch: $batch,
            priceDiff: $priceDiff,
        );

        // 5) Post the journal entry (always one JE per item per Q5).
        $entry = null;
        $inventoryImpact = $split['inventory_impact'] ?? 0.0;
        $cogsImpact = $split['cogs_impact'] ?? 0.0;

        try {
            $entry = DB::transaction(function () use (
                $item, $batch, $originalUnitCost, $newUnitCost,
                $priceDiff, $inventoryImpact, $cogsImpact, $split, $userId
            ) {
                if ($split['fallback_required']) {
                    return $this->postingFallback($item, $batch, $originalUnitCost, $newUnitCost, $priceDiff, $split, $userId);
                }

                return $this->postingDetailed($item, $batch, $originalUnitCost, $newUnitCost, $priceDiff, $inventoryImpact, $cogsImpact, $userId);
            });
        } catch (\Throwable $e) {
            Log::error('[LateInvoicePriceAdjustment] Posting failed: ' . $e->getMessage(), [
                'item'  => $item->id,
                'batch' => $batch->id,
            ]);
            throw $e;
        }

        // 6) Persist the audit row.
        $adjustment = BatchPriceAdjustment::create([
            'purchase_invoice_item_id'   => $item->id,
            'material_batch_id'          => $batch->id,
            'original_unit_cost'         => $originalUnitCost,
            'new_unit_cost'              => $newUnitCost,
            'price_diff'                 => $priceDiff,
            'total_quantity_affected'    => $split['total_quantity_affected'] ?? 0,
            'inventory_impact'           => $split['fallback_required'] ? 0 : $inventoryImpact,
            'cogs_impact'                => $split['fallback_required'] ? 0 : $cogsImpact,
            'fallback_used'              => $split['fallback_required'],
            'fallback_reason'            => $split['fallback_reason'] ?? null,
            'journal_entry_id'           => $entry?->id,
            'applied_by'                 => $userId,
        ]);

        // 7) Update batch.unit_cost to the new price (original_unit_cost stays).
        $batch->unit_cost = $newUnitCost;
        $batch->save();

        return $adjustment;
    }

    /**
     * Walk the genealogy graph from this source batch downwards to the
     * FINISHED level. Multi-level walk stays within scope-of-batch
     * (manufactured product becomes raw of next MO is OUT OF SCOPE per Q4).
     */
    public function collectDescendantFGBatches(MaterialBatch $batch): \Illuminate\Support\Collection
    {
        $fgIds = BatchGenealogy::query()
            ->where('source_material_batch_id', $batch->id)
            ->pluck('finished_good_batch_id')
            ->unique()
            ->all();

        if (empty($fgIds)) {
            return collect();
        }

        return FinishedGoodBatch::query()
            ->whereIn('id', $fgIds)
            ->get();
    }

    /**
     * Build the detailed (3-way) journal entry: DR inventory × 2, DR cogs, CR AP.
     */
    private function postingDetailed(
        PurchaseInvoiceItem $item,
        MaterialBatch $batch,
        float $originalUnitCost,
        float $newUnitCost,
        float $priceDiff,
        float $inventoryImpact,
        float $cogsImpact,
        ?int $userId,
    ): ?JournalEntry {
        if (abs($inventoryImpact) < 0.01 && abs($cogsImpact) < 0.01) {
            return null;
        }

        $settings = \App\Models\AccountingSetting::first();
        if (! $settings) {
            return null;
        }

        $apAccount = $settings->ap_account_id;          // 2110
        $inventoryAccount = $settings->inventory_account_id;  // 1310
        $cogsAccount = $settings->cogs_account_id;      // 5100

        $sourceEventKey = "batch_price_adjustment:item:{$item->id}:adjustment:{$item->id}";
        $invoiceNumber = $item->invoice?->invoice_number ?? $item->id;

        $lines = [];

        // DR inventory (signed by priceDiff sign)
        if (abs($inventoryImpact) >= 0.01) {
            $lines[] = [
                'account_id'   => $inventoryAccount,
                'debit'        => $inventoryImpact > 0 ? $inventoryImpact : 0,
                'credit'       => $inventoryImpact < 0 ? abs($inventoryImpact) : 0,
                'description'  => "تسوية سعر فاتورة #{$invoiceNumber} — دفعة {$batch->batch_code}",
            ];
        }

        // DR cogs (signed by priceDiff sign)
        if (abs($cogsImpact) >= 0.01) {
            $lines[] = [
                'account_id'   => $cogsAccount,
                'debit'        => $cogsImpact > 0 ? $cogsImpact : 0,
                'credit'       => $cogsImpact < 0 ? abs($cogsImpact) : 0,
                'description'  => "تسوية COGS — فاتورة #{$invoiceNumber}",
            ];
        }

        // CR AP (always inverse total)
        $totalCr = round($inventoryImpact + $cogsImpact, 4);
        $lines[] = [
            'account_id'   => $apAccount,
            'debit'        => $totalCr < 0 ? abs($totalCr) : 0,
            'credit'       => $totalCr > 0 ? $totalCr : 0,
            'description'  => 'مورد — تعديل سعر متأخر',
        ];

        // Hand to PostingService via JournalEntryService directly — same as
        // legacy posting calls elsewhere.
        return app(\App\Services\Accounting\JournalEntryService::class)
            ->createAndPost([
                'entry_date'       => now()->toDateString(),
                'description'      => "تسوية سعر متأخر — بند فاتورة #{$item->id} (دفعة {$batch->batch_code})",
                'source_type'      => \App\Enums\JournalEntrySource::MANUAL->value,
                'source_id'        => $item->id,
                'source_event_key' => $sourceEventKey,
                'lines'            => $lines,
            ]);
    }

    /**
     * Fallback posting — entire diff to 5160 (Gap 2) and balanced CR AP.
     */
    private function postingFallback(
        PurchaseInvoiceItem $item,
        MaterialBatch $batch,
        float $originalUnitCost,
        float $newUnitCost,
        float $priceDiff,
        array $split,
        ?int $userId,
    ): ?JournalEntry {
        $settings = \App\Models\AccountingSetting::first();
        if (! $settings) {
            return null;
        }

        // Pick a variance account: 5160 by default, or whatever the tenant
        // configured. (Gap 2 added getResolvedVarianceAccount.)
        $varianceAccount = method_exists($settings, 'getResolvedVarianceAccount')
            ? $settings->getResolvedVarianceAccount()
            : null;

        if (! $varianceAccount) {
            Log::warning('[LateInvoicePriceAdjustment] No 5160 / variance account resolved — skipping fallback', [
                'tenant_id' => tenant()?->id,
            ]);
            return null;
        }

        $apAccount = $settings->ap_account_id;
        $totalImpact = round($priceDiff * (float) ($split['total_quantity_affected'] ?? 0), 4);

        if (abs($totalImpact) < 0.01) {
            return null;
        }

        $sourceEventKey = "batch_price_adjustment:item:{$item->id}:fallback:{$item->id}";

        $lines = [
            [
                'account_id'   => $varianceAccount->id,
                'debit'        => $totalImpact > 0 ? $totalImpact : 0,
                'credit'       => $totalImpact < 0 ? abs($totalImpact) : 0,
                'description'  => "Fallback: تسوية دفعة {$batch->batch_code} — {$split['fallback_reason']}",
            ],
            [
                'account_id'   => $apAccount,
                'debit'        => $totalImpact < 0 ? abs($totalImpact) : 0,
                'credit'       => $totalImpact > 0 ? $totalImpact : 0,
                'description'  => 'مورد — تعديل سعر متأخر',
            ],
        ];

        return app(\App\Services\Accounting\JournalEntryService::class)
            ->createAndPost([
                'entry_date'       => now()->toDateString(),
                'description'      => "Fallback: تسوية سعر متأخر — بند فاتورة #{$item->id}",
                'source_type'      => \App\Enums\JournalEntrySource::MANUAL->value,
                'source_id'        => $item->id,
                'source_event_key' => $sourceEventKey,
                'lines'            => $lines,
            ]);
    }
}
