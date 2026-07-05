<?php

namespace App\Services\Accounting;

use App\Models\FixedAsset;
use App\Models\FixedAssetDepreciation;
use App\Models\JournalEntry;
use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FixedAssetService
{
    public function __construct(
        private JournalEntryService $journalService
    ) {}

    /**
     * Register a new fixed asset.
     */
    public function register(array $data): FixedAsset
    {
        return DB::transaction(function () use ($data) {
            $asset = FixedAsset::create([
                'name'                                => $data['name'],
                'code'                                => $data['code'],
                'purchase_date'                       => $data['purchase_date'],
                'purchase_cost'                       => (float) $data['purchase_cost'],
                'scrap_value'                         => (float) ($data['scrap_value'] ?? 0.0),
                'useful_life'                         => (int) $data['useful_life'],
                'depreciation_method'                 => $data['depreciation_method'] ?? 'straight_line',
                'asset_account_id'                    => $data['asset_account_id'],
                'accumulated_depreciation_account_id' => $data['accumulated_depreciation_account_id'],
                'depreciation_expense_account_id'     => $data['depreciation_expense_account_id'],
                'status'                              => 'active',
                'created_by'                          => Auth::id(),
            ]);

            return $asset;
        });
    }

    /**
     * Calculate monthly depreciation for a straight-line asset.
     */
    public function calculateMonthlyDepreciation(FixedAsset $asset): float
    {
        if ($asset->status !== 'active') {
            return 0.0;
        }

        $depreciableAmount = $asset->purchase_cost - $asset->scrap_value;
        if ($depreciableAmount <= 0 || $asset->useful_life <= 0) {
            return 0.0;
        }

        // Monthly depreciation
        $monthlyAmount = $depreciableAmount / ($asset->useful_life * 12);
        
        return round($monthlyAmount, 2);
    }

    /**
     * Post a monthly depreciation run for all active assets.
     * Returns the list of created depreciation records.
     */
    public function postDepreciationRun(string $date): array
    {
        $runDate = Carbon::parse($date)->endOfMonth();
        $runDateStr = $runDate->toDateString();

        return DB::transaction(function () use ($runDate, $runDateStr) {
            $assets = FixedAsset::where('status', 'active')
                ->where('purchase_date', '<=', $runDateStr)
                ->get();

            $results = [];

            foreach ($assets as $asset) {
                // Idempotency: check if already run this month
                $existing = FixedAssetDepreciation::where('fixed_asset_id', $asset->id)
                    ->whereBetween('depreciation_date', [
                        $runDate->copy()->startOfMonth()->toDateString(),
                        $runDateStr
                    ])
                    ->first();

                if ($existing) {
                    continue;
                }

                // Check remaining depreciable value
                $accumulated = (float) $asset->accumulated_depreciation;
                $bookValue = $asset->purchase_cost - $accumulated;
                $remainingDepreciable = $bookValue - $asset->scrap_value;

                if ($remainingDepreciable <= 0.01) {
                    $asset->update(['status' => 'fully_depreciated']);
                    continue;
                }

                // Monthly calculation
                $amount = $this->calculateMonthlyDepreciation($asset);

                // Adjust for final month
                if ($amount >= $remainingDepreciable) {
                    $amount = $remainingDepreciable;
                }

                if ($amount <= 0) {
                    $asset->update(['status' => 'fully_depreciated']);
                    continue;
                }

                // Post Journal Entry
                $eventKey = "fixed_asset:{$asset->id}:depreciation:{$runDateStr}";
                
                $lines = [
                    [
                        'account_id'  => $asset->depreciation_expense_account_id,
                        'debit'       => $amount,
                        'credit'      => 0,
                        'description' => "إهلاك شهري للأصل {$asset->name} (#{$asset->code}) - شهر " . $runDate->format('m/Y'),
                    ],
                    [
                        'account_id'  => $asset->accumulated_depreciation_account_id,
                        'debit'       => 0,
                        'credit'      => $amount,
                        'description' => "مجمع إهلاك للأصل {$asset->name} (#{$asset->code})",
                    ]
                ];

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $runDateStr,
                    'description'      => "إهلاك شهري للأصل {$asset->name} - " . $runDate->format('m/Y'),
                    'source_type'      => JournalEntrySource::MANUAL->value,
                    'source_id'        => $asset->id,
                    'source_event_key' => $eventKey,
                    'lines'            => $lines,
                ]);

                // Create depreciation record
                $dep = FixedAssetDepreciation::create([
                    'fixed_asset_id'    => $asset->id,
                    'depreciation_date' => $runDateStr,
                    'amount'            => $amount,
                    'journal_entry_id'  => $entry->id,
                ]);

                // Update asset if it reached scrap value
                if (($bookValue - $amount) <= $asset->scrap_value + 0.01) {
                    $asset->update(['status' => 'fully_depreciated']);
                }

                $results[] = $dep;
            }

            return $results;
        });
    }

    /**
     * Dispose/Sell a fixed asset.
     */
    public function dispose(FixedAsset $asset, float $disposalValue, string $disposalDate, int $cashAccountId): JournalEntry
    {
        if (in_array($asset->status, ['disposed'])) {
            throw new \RuntimeException('لا يمكن بيع أو استبعاد أصل مستبعد مسبقاً.');
        }

        return DB::transaction(function () use ($asset, $disposalValue, $disposalDate, $cashAccountId) {
            $accumulated = (float) $asset->accumulated_depreciation;
            $bookValue = $asset->purchase_cost - $accumulated;
            $gainLoss = $disposalValue - $bookValue;

            $eventKey = "fixed_asset:{$asset->id}:disposal:{$disposalDate}";

            $lines = [];

            // 1. Debit Cash/Bank for the disposal value (if any received)
            if ($disposalValue > 0) {
                $lines[] = [
                    'account_id'  => $cashAccountId,
                    'debit'       => $disposalValue,
                    'credit'      => 0,
                    'description' => "قيمة بيع/استبعاد الأصل {$asset->name} (#{$asset->code})",
                ];
            }

            // 2. Debit Accumulated Depreciation to close it
            if ($accumulated > 0) {
                $lines[] = [
                    'account_id'  => $asset->accumulated_depreciation_account_id,
                    'debit'       => $accumulated,
                    'credit'      => 0,
                    'description' => "إغلاق مجمع إهلاك الأصل {$asset->name} (#{$asset->code})",
                ];
            }

            // 3. Credit the original Asset cost account to close it
            $lines[] = [
                'account_id'  => $asset->asset_account_id,
                'debit'       => 0,
                'credit'      => $asset->purchase_cost,
                'description' => "إغلاق حساب الأصل {$asset->name} (#{$asset->code})",
            ];

            // 4. Handle Gain or Loss
            if ($gainLoss > 0) {
                // Gain -> Credit Other Revenue (4200)
                $otherRevenueAcc = \App\Models\Account::where('code', '4200')->value('id');
                $lines[] = [
                    'account_id'  => $otherRevenueAcc ?? $asset->depreciation_expense_account_id, // fallback
                    'debit'       => 0,
                    'credit'      => $gainLoss,
                    'description' => "أرباح رأسمالية من بيع/استبعاد الأصل {$asset->name}",
                ];
            } elseif ($gainLoss < 0) {
                // Loss -> Debit Miscellaneous Expenses (5290)
                $miscExpenseAcc = \App\Models\Account::where('code', '5290')->value('id');
                $lines[] = [
                    'account_id'  => $miscExpenseAcc ?? $asset->depreciation_expense_account_id, // fallback
                    'debit'       => abs($gainLoss),
                    'credit'      => 0,
                    'description' => "خسائر رأسمالية من بيع/استبعاد الأصل {$asset->name}",
                ];
            }

            // Create Journal Entry
            $entry = $this->journalService->createAndPost([
                'entry_date'       => $disposalDate,
                'description'      => "بيع/استبعاد الأصل الثابت {$asset->name} - كود {$asset->code}",
                'source_type'      => JournalEntrySource::MANUAL->value,
                'source_id'        => $asset->id,
                'source_event_key' => $eventKey,
                'lines'            => $lines,
            ]);

            // Update Asset properties
            $asset->update([
                'status'             => 'disposed',
                'disposed_at'        => $disposalDate,
                'disposal_value'     => $disposalValue,
                'disposal_gain_loss' => $gainLoss,
                'disposal_entry_id'  => $entry->id,
                'updated_by'         => Auth::id(),
            ]);

            return $entry;
        });
    }
}
