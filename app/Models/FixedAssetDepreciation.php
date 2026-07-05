<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetDepreciation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'fixed_asset_id',
        'depreciation_date',
        'amount',
        'journal_entry_id',
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'amount'            => 'decimal:2',
    ];

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
