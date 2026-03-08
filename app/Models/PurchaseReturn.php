<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'purchase_invoice_id',
        'supplier_id',
        'warehouse_id',
        'return_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'status',
        'return_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'return_date' => 'datetime',
    ];

    // ==================== Relationships ====================

    /**
     * العلاقة مع فاتورة الشراء
     */
    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    /**
     * Alias for purchaseInvoice
     */
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    /**
     * العلاقة مع المورد
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
