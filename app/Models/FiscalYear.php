<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'is_current',   // ✅ إصلاح #18 — مطلوب في AccountingDashboardController و FiscalPeriodController
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_closed'  => 'boolean',
        'is_current' => 'boolean',  // ✅
        'closed_at'  => 'datetime',
    ];

    public function periods()
    {
        return $this->hasMany(FiscalPeriod::class);
    }
}
