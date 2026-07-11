<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MaterialBatchPurchaseLink — Gap 4.
 *
 * The missing join: formal FK between a raw batch (which holds the cost)
 * and a purchase invoice item (which holds the supplier-declared price).
 *
 * Without this, retroactive price adjustments have nothing to attach to,
 * and the old `purchase_reference` text column is unjoinable.
 */
class MaterialBatchPurchaseLink extends Model
{
    use HasFactory;

    protected $table = 'material_batch_purchase_links';

    protected $fillable = [
        'material_batch_id',
        'purchase_invoice_item_id',
        'quantity_originally_priced',
        'linked_at',
    ];

    protected $casts = [
        'quantity_originally_priced' => 'decimal:4',
        'linked_at' => 'datetime',
    ];

    public function materialBatch(): BelongsTo
    {
        return $this->belongsTo(MaterialBatch::class, 'material_batch_id');
    }

    public function purchaseInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoiceItem::class, 'purchase_invoice_item_id');
    }
}
