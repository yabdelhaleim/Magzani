<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringJournalEntryLine extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'recurring_journal_entry_id',
        'line_number',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function template()
    {
        return $this->belongsTo(RecurringJournalEntry::class, 'recurring_journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
