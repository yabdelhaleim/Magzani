<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warehouse_id', // ⚠️ ناقص في الكود الحالي
        'invoice_number',
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
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    // ==================== Relationships ====================

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
    
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
    
    // ==================== Accessors ====================
    
    public function getRemainingAttribute(): float
    {
        return round($this->total - $this->paid, 2);
    }
    
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }
    
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }
    
    // ==================== Scopes ====================
    
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }
    
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
    
    public function scopePending($query)
    {
        return $query->whereIn('payment_status', ['unpaid', 'partial']);
    }
}