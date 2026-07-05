<?php

namespace App\Models;

use App\Enums\JournalEntryStatus;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'entry_date',
        'fiscal_period_id',
        'description',
        'reference',
        'status',
        'source_type',
        'source_id',
        'source_event_key',
        'total_debit',
        'total_credit',
        'currency_code',
        'reversed_entry_id',
        'reversal_of_id',
        'posted_at',
        'posted_by',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'status' => JournalEntryStatus::class,
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function fiscalPeriod()
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function reversedEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_entry_id');
    }

    public function reversalOf()
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_of_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
