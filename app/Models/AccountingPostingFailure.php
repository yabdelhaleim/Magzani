<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingPostingFailure extends Model
{
    protected $fillable = [
        'source_type',
        'source_id',
        'source_event_key', // اسم مُوحَّد مع journal_entries
        'event_key',        // للتوافقية مع الكود القديم
        'description',
        'error_message',
        'error_trace',
        'error_class',
        'attempts',
        'failed_at',
        'resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'failed_at'   => 'datetime',
        'resolved'    => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('posting_failures_count');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('posting_failures_count');
        });
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the Arabic label of the source transaction type
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return match ($this->source_type) {
            'sales_invoice'    => 'فاتورة مبيعات',
            'purchase_invoice' => 'فاتورة مشتريات',
            'payment'          => 'سند قبض عميل',
            'supplier_payment' => 'سند صرف مورد',
            'sales_return'     => 'مرتجع مبيعات',
            'purchase_return'  => 'مرتجع مشتريات',
            'cash_tx'          => 'حركة نقدية',
            'manufacturing'    => 'أمر تصنيع',
            default            => $this->source_type ?? 'غير معروف',
        };
    }

    /**
     * Get the financial amount affected by the failed posting
     */
    public function getAffectedAmountAttribute(): ?float
    {
        try {
            switch ($this->source_type) {
                case 'sales_invoice':
                    return \App\Models\SalesInvoice::where('id', $this->source_id)->value('total_amount');
                case 'purchase_invoice':
                    return \App\Models\PurchaseInvoice::where('id', $this->source_id)->value('total_amount');
                case 'payment':
                    return \App\Models\Payment::where('id', $this->source_id)->value('amount');
                case 'supplier_payment':
                    return \App\Models\SupplierPayment::where('id', $this->source_id)->value('amount');
                case 'sales_return':
                    return \App\Models\SalesReturn::where('id', $this->source_id)->value('total_amount');
                case 'purchase_return':
                    return \App\Models\PurchaseReturn::where('id', $this->source_id)->value('total_amount');
                case 'cash_tx':
                    return \App\Models\CashTransaction::where('id', $this->source_id)->value('amount');
                case 'manufacturing':
                    return \App\Models\ManufacturingOrder::where('id', $this->source_id)->value('total_cost');
                default:
                    return null;
            }
        } catch (\Throwable) {
            return null;
        }
    }
}
