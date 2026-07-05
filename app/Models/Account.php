<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'account_type_id',
        'parent_id',
        'level',
        'is_leaf',
        'is_system',
        'is_active',
        'description',
        'linked_model',
        'linked_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_leaf' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function balance()
    {
        return $this->hasOne(AccountBalance::class);
    }

    public function linked()
    {
        return $this->morphTo('linked', 'linked_model', 'linked_id');
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
