<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get payment type display name
     */
    public function getTypeAttribute()
    {
        return $this->payable_type;
    }

    /**
     * Get payment name
     */
    public function getNameAttribute()
    {
        return $this->notes ?? 'غير محدد';
    }

    /**
     * Get payment method display
     */
    public function getMethodAttribute()
    {
        $methods = [
            'cash' => 'نقدي',
            'bank' => 'تحويل بنكي',
            'check' => 'شيك',
            'card' => 'بطاقة',
        ];
        
        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get reference to cash transaction
     */
    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'reference');
    }
}