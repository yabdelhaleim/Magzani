<?php

namespace App\Models;

use App\Enums\NormalBalance;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'code',
        'name_ar',
        'name_en',
        'normal_balance',
        'sort_order',
    ];

    protected $casts = [
        'normal_balance' => NormalBalance::class,
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
