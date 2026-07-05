<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'statement_ending_balance',
        'gl_ending_balance',
        'is_reconciled',
        'reconciled_at',
        'reconciled_by',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_ending_balance' => 'decimal:2',
        'gl_ending_balance' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
