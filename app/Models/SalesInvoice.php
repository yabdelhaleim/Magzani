<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'warehouse_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'shipping_cost',
        'other_charges',
        'total',
        'paid',
        'status',
        'payment_status',
        'notes',
        'terms_conditions',
        'created_by',
        'updated_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'confirmed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

    protected $appends = ['remaining'];

    // ==================== Relationships ====================

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function returns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ==================== Accessors ====================

    /**
     * المبلغ المتبقي (محسوب تلقائياً)
     */
    public function getRemainingAttribute()
    {
        return round($this->total - $this->paid, 2);
    }

    /**
     * نسبة الدفع (%)
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total <= 0) {
            return 0;
        }
        return round(($this->paid / $this->total) * 100, 2);
    }

    /**
     * هل الفاتورة مدفوعة بالكامل؟
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->remaining <= 0;
    }

    /**
     * هل الفاتورة مدفوعة جزئياً؟
     */
    public function getIsPartiallyPaidAttribute()
    {
        return $this->paid > 0 && $this->remaining > 0;
    }

    // ==================== Scopes ====================

    /**
     * الفواتير المدفوعة بالكامل
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * الفواتير المعلقة (غير مدفوعة)
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * الفواتير المدفوعة جزئياً
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partial');
    }

    /**
     * الفواتير المؤكدة
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}