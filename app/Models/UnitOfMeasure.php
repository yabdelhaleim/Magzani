<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'name',
        'code',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'from_uom_id');
    }

    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'to_uom_id');
    }
}
