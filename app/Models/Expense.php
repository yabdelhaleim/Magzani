<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'description',
        'reference_number',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get category display name
     */
    public function getTypeAttribute()
    {
        $categories = [
            'rent' => 'إيجار',
            'salaries' => 'رواتب',
            'utilities' => 'مرافق',
            'maintenance' => 'صيانة',
            'supplies' => 'مستلزمات',
            'marketing' => 'تسويق ودعاية',
            'transportation' => 'مواصلات',
            'communication' => 'اتصالات',
            'insurance' => 'تأمينات',
            'taxes' => 'ضرائب ورسوم',
            'other' => 'أخرى',
        ];
        
        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get date formatted
     */
    public function getDateAttribute()
    {
        return $this->expense_date;
    }

    /**
     * Get notes from description
     */
    public function getNotesAttribute()
    {
        return $this->description;
    }

    /**
     * Get reference to cash transaction
     */
    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'reference');
    }
}