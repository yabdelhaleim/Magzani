<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    protected $primaryKey = 'account_id';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'period_debit',
        'period_credit',
        'ytd_debit',
        'ytd_credit',
        'balance',
        'last_entry_id',
        'last_entry_date',
    ];

    protected $casts = [
        'period_debit' => 'decimal:2',
        'period_credit' => 'decimal:2',
        'ytd_debit' => 'decimal:2',
        'ytd_credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'last_entry_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function lastEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'last_entry_id');
    }
}
