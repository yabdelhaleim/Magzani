<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WoodDispensing extends Model
{
    use HasFactory;

    protected $fillable = [
        'wood_stock_id',
        'user_id',
        'client_id',
        'manufacturing_order_id',
        'sales_invoice_id',
        'volume_cm3_taken',
        'notes',
        'dispensed_at',
    ];

    protected $casts = [
        'volume_cm3_taken' => 'decimal:4',
        'dispensed_at' => 'date',
    ];

    /* ===========================
     * 🔗 RELATIONSHIPS
     * =========================== */

    public function woodStock(): BelongsTo
    {
        return $this->belongsTo(WoodStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
