<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'balance',
        // معلومات الاتصال
        'phone',
        'phone2',
        'email',
        'contact_person',
        // العنوان
        'address',
        'city',
        'country',
        // المالية
        'opening_balance',
        'current_balance',
        // الحالة
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    // ==================== Scopes ====================
    
    /**
     * Scope للموردين النشطين فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope للبحث
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // ==================== Relationships ====================

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
    
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== Accessors & Mutators ====================

    /**
     * حساب الرصيد الحالي تلقائياً
     */
    public function calculateBalance()
    {
        $totalPurchases = $this->purchaseInvoices()->sum('total');
        $totalReturns = $this->purchaseReturns()->sum('total');
        $totalPayments = $this->payments()->sum('amount');

        // الرصيد = رصيد افتتاحي + المشتريات - المرتجعات - المدفوعات
        return $this->opening_balance + $totalPurchases - $totalReturns - $totalPayments;
    }

    /**
     * تحديث الرصيد الحالي
     */
    public function updateCurrentBalance()
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
        
        return $this->current_balance;
    }

    // ==================== Helper Methods ====================

    /**
     * هل المورد لديه رصيد مستحق؟
     */
    public function hasOutstandingBalance(): bool
    {
        return $this->current_balance > 0;
    }

    /**
     * الحصول على كشف الحساب
     */
    public function getStatement()
    {
        $statement = collect();

        // الرصيد الافتتاحي
        if ($this->opening_balance != 0) {
            $statement->push([
                'date' => $this->created_at,
                'type' => 'opening_balance',
                'type_ar' => 'رصيد افتتاحي',
                'reference' => '-',
                'debit' => $this->opening_balance > 0 ? $this->opening_balance : 0,
                'credit' => $this->opening_balance < 0 ? abs($this->opening_balance) : 0,
                'balance' => $this->opening_balance,
            ]);
        }

        // فواتير الشراء (مدين)
        foreach ($this->purchaseInvoices as $invoice) {
            $statement->push([
                'date' => $invoice->invoice_date,
                'type' => 'purchase_invoice',
                'type_ar' => 'فاتورة شراء',
                'reference' => $invoice->invoice_number,
                'debit' => $invoice->total,
                'credit' => 0,
                'balance' => 0, // سيتم حسابه لاحقاً
            ]);
        }

        // مرتجعات الشراء (دائن)
        foreach ($this->purchaseReturns as $return) {
            $statement->push([
                'date' => $return->return_date,
                'type' => 'purchase_return',
                'type_ar' => 'مرتجع شراء',
                'reference' => $return->return_number,
                'debit' => 0,
                'credit' => $return->total,
                'balance' => 0,
            ]);
        }

        // المدفوعات (دائن)
        foreach ($this->payments as $payment) {
            $statement->push([
                'date' => $payment->payment_date,
                'type' => 'payment',
                'type_ar' => 'سداد',
                'reference' => $payment->reference_number ?? '-',
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0,
            ]);
        }

        // ترتيب حسب التاريخ وحساب الرصيد المتراكم
        $statement = $statement->sortBy('date')->values();
        $runningBalance = 0;
        
        $statement = $statement->map(function($item) use (&$runningBalance) {
            $runningBalance += $item['debit'] - $item['credit'];
            $item['balance'] = $runningBalance;
            return $item;
        });

        return $statement;
    }
}