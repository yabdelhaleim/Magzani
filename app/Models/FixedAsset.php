<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    protected $fillable = [
        'name',
        'code',
        'purchase_date',
        'purchase_cost',
        'scrap_value',
        'useful_life',
        'depreciation_method',
        'asset_account_id',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',
        'status',
        'disposed_at',
        'disposal_value',
        'disposal_gain_loss',
        'disposal_entry_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date'      => 'date',
        'purchase_cost'      => 'decimal:2',
        'scrap_value'        => 'decimal:2',
        'disposed_at'        => 'date',
        'disposal_value'     => 'decimal:2',
        'disposal_gain_loss' => 'decimal:2',
        'useful_life'        => 'integer',
    ];

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'depreciation_expense_account_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciation::class);
    }

    public function disposalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'disposal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate current book value.
     */
    public function getBookValueAttribute(): float
    {
        $totalDep = (float) $this->depreciations()->sum('amount');
        return (float) ($this->purchase_cost - $totalDep);
    }

    /**
     * Calculate total accumulated depreciation.
     */
    public function getAccumulatedDepreciationAttribute(): float
    {
        return (float) $this->depreciations()->sum('amount');
    }
}
