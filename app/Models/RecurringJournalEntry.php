<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringJournalEntry extends Model
{
    protected $fillable = [
        'template_name',
        'description',
        'frequency',
        'next_run_date',
        'last_run_date',
        'end_date',
        'is_active',
        'auto_post',
        'created_by',
    ];

    protected $casts = [
        'next_run_date'  => 'date',
        'last_run_date'  => 'date',
        'end_date'       => 'date',
        'is_active'      => 'boolean',
        'auto_post'      => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(RecurringJournalEntryLine::class)->orderBy('line_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency) {
            'daily'     => 'يومي',
            'weekly'    => 'أسبوعي',
            'monthly'   => 'شهري',
            'quarterly' => 'ربع سنوي',
            'yearly'    => 'سنوي',
            default     => $this->frequency,
        };
    }
}
