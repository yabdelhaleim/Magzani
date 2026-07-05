<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'journal_entry_id',
        'line_number',
        'account_id',
        'debit',
        'credit',
        'description',
        'cost_center_id',
        'party_type',
        'party_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function party()
    {
        return $this->morphTo('party', 'party_type', 'party_id');
    }
}
