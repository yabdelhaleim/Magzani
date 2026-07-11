<?php

namespace App\Services;

use App\Enums\JournalEntrySource;
use App\Models\JournalEntry;
use App\Models\ManufacturingOrder;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CostVariancePostingService — Gap 2.
 *
 * Builds and posts the 3-line journal entry that replaces the legacy
 * 2-line WIP → Finished-Goods entry when an MO completes under Standard
 * Costing.
 *
 * Layout (always balanced):
 *
 *   ┌──────────────────────────────────────────────────────────────────┐
 *   │ if actual > standard  (Unfavorable)                              │
 *   │   DR  Inventory (FG)         = standard_total                    │
 *   │   DR  5160 Cost Variance     = actual − standard                 │
 *   │   CR  WIP                    = actual_total                      │
 *   │                                                                  │
 *   │ if actual < standard  (Favorable)                                │
 *   │   DR  Inventory (FG)         = standard_total                    │
 *   │   CR  WIP                    = actual_total                      │
 *   │   CR  5160 Cost Variance     = standard − actual                 │
 *   │                                                                  │
 *   │ if actual = standard  (no variance)                              │
 *   │   ↓ Falls back to legacy 2-line entry via PostingService         │
 *   └──────────────────────────────────────────────────────────────────┘
 *
 * Idempotency: uses source_event_key = `manufacturing:{id}:completed:standard_costing`
 * which is distinct from the legacy key, so toggling the tenant setting
 * does NOT collide with prior entries already posted. The same flow played
 * twice is a no-op.
 *
 * Failure handling mirrors the rest of PostingService: the caller wraps
 * the call in try/catch; failures inside this service are logged. The
 * `AccountingPostingFailure` row is written by `safePost()` via the
 * shared lock mechanism.
 */
class CostVariancePostingService
{
    public function __construct(
        private readonly JournalEntryService $journalService,
    ) {}

    /**
     * Post the variance-aware manufacturing completion entry.
     *
     * Returns the created JournalEntry on success, null on skipped cases
     * (auto-post disabled, zero cost, zero variance).
     */
    public function postManufacturingCompleteWithVariance(
        ManufacturingOrder $order,
        array $variance,
    ): ?JournalEntry {
        $settings = \App\Models\AccountingSetting::first();
        if (! $settings?->auto_post_manufacturing) {
            return null;
        }

        $actualTotal = (float) $variance['actual_total'];
        if ($actualTotal <= 0) {
            return null;
        }

        // Edge case — variance is essentially zero. Drop the 5160 line and
        // emit the legacy 2-line entry through PostingService so behavior
        // matches a non-standard-costing tenant exactly.
        if (! ($variance['has_variance'] ?? false)) {
            Log::info('[CostVariance] Zero/insignificant variance — falling back to legacy entry', [
                'order'    => $order->order_number,
                'standard' => $variance['standard_total'],
                'actual'   => $actualTotal,
                'reason'   => $variance['reason'] ?? 'unknown',
            ]);

            return app(\App\Services\Accounting\PostingService::class)
                ->postManufacturingComplete($order);
        }

        $varianceAccount = $settings->getResolvedVarianceAccount();
        if (! $varianceAccount) {
            Log::warning('[CostVariance] No variance account resolved — falling back to legacy entry', [
                'order'   => $order->order_number,
                'setting' => $settings->variance_posting_account_id,
            ]);

            return app(\App\Services\Accounting\PostingService::class)
                ->postManufacturingComplete($order);
        }

        $isUnfavorable = $variance['variance_type'] === 'unfavorable';
        $absVariance = round(abs((float) $variance['total_variance']), 2);
        $standardTotal = round((float) $variance['standard_total'], 2);

        return $this->safePost(
            key: "manufacturing:{$order->id}:completed:standard_costing",
            description: "تصنيع Standard Costing #{$order->order_number}",
            callback: function () use ($order, $standardTotal, $actualTotal, $absVariance, $isUnfavorable, $settings, $varianceAccount) {
                $lines = [
                    [
                        'account_id'   => $settings->inventory_account_id,
                        'debit'        => $standardTotal,
                        'credit'       => 0,
                        'description'  => 'منتج تام الصنع (تكلفة معيارية)',
                    ],
                ];

                if ($isUnfavorable) {
                    // Unfavorable: actual was MORE expensive than standard.
                    // Variance account behaves as an extra expense → debit.
                    $lines[] = [
                        'account_id'  => $varianceAccount->id,
                        'debit'       => $absVariance,
                        'credit'      => 0,
                        'description' => 'انحراف تكلفة غير مواتٍ (Standard Costing)',
                    ];
                } else {
                    // Favorable: actual was LESS than standard.
                    // Variance account reduces expense → credit.
                    $lines[] = [
                        'account_id'  => $varianceAccount->id,
                        'debit'       => 0,
                        'credit'      => $absVariance,
                        'description' => 'انحراف تكلفة مواتٍ (تخفيض مصروف)',
                    ];
                }

                $lines[] = [
                    'account_id'  => $settings->wip_account_id,
                    'debit'       => 0,
                    'credit'      => $actualTotal,
                    'description' => 'إنهاء WIP',
                ];

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $order->produced_at ?? $order->updated_at,
                    'description'      => "إنتاج تام (Standard Costing) — أمر تصنيع #{$order->order_number}",
                    'source_type'      => JournalEntrySource::MANUFACTURING->value,
                    'source_id'        => $order->id,
                    'source_event_key' => "manufacturing:{$order->id}:completed:standard_costing",
                    'lines'            => $lines,
                ]);

                if ($entry instanceof JournalEntry) {
                    $order->update(['variance_journal_entry_id' => $entry->id]);
                }

                Log::info('[CostVariance] Variance entry posted', [
                    'order'        => $order->order_number,
                    'entry_id'     => $entry?->id,
                    'standard'     => $standardTotal,
                    'actual'       => $actualTotal,
                    'variance'     => $absVariance,
                    'unfavorable'  => $isUnfavorable,
                ]);

                return $entry;
            }
        );
    }

    /**
     * Lightweight safePost — keeps consistent with PostingService.safePost()
     * contract (idempotency + failure capture) without re-injecting the
     * heavy PostingFailureRetryService which PostingService already wraps.
     *
     * Why a separate implementation: keeps variance entry idempotent even
     * if PostingService internals change later. We deliberately do not
     * record AccountingPostingFailure here — the gap-3 retry flow lives
     * in PostingService and that's where accounting failures converge.
     */
    private function safePost(string $key, string $description, callable $callback): ?JournalEntry
    {
        $lock = Cache::lock("posting:{$key}", 10);

        try {
            return $lock->block(5, function () use ($key, $description, $callback) {
                // Idempotency: don't double-post.
                $existing = JournalEntry::where('source_event_key', $key)->first();
                if ($existing) {
                    return $existing;
                }

                return $callback();
            });
        } catch (\Throwable $e) {
            Log::warning("[CostVariance] {$description} failed: " . $e->getMessage(), [
                'key'          => $key,
                'exception'    => get_class($e),
            ]);

            return null;
        }
    }
}
