<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'return_number',
        'sales_invoice_id',
        'customer_id',
        'warehouse_id',
        'return_date',
        'subtotal',           // ✅ مطلوب
        'discount_amount',    // ✅ مطلوب
        'tax_amount',         // ✅ مطلوب
        'total',
        'status',             // ✅ مطلوب (enum)
        'return_reason',
        'notes',
        'created_by',
        'updated_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'return_date'    => 'date',
        'subtotal'       => 'decimal:2',
        'discount_amount'=> 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'total'          => 'decimal:2',
        'confirmed_at'   => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // العلاقات
    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    // public function images(): HasMany
    // {
    //     return $this->hasMany(ReturnImage::class, 'return_id');
    // }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}