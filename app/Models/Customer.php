<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email','code', 'address', 'balance', 'credit_limit', 'is_active'];
    protected $casts = [
    'is_active' => 'boolean',
];

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function returns()
    {
        return $this->hasMany(SalesReturn::class);
    }
}
