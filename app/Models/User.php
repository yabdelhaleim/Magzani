<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
    ];

    // ==================== Relationships ====================

    /**
     * أدوار المستخدم
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * صلاحيات مباشرة (اختياري)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withTimestamps();
    }

    /**
     * فواتير البيع اللي أنشأها
     */
    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'created_by');
    }

    /**
     * فواتير الشراء اللي أنشأها
     */
    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'created_by');
    }

    /**
     * مرتجعات البيع اللي أنشأها
     */
    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class, 'created_by');
    }

    /**
     * مرتجعات الشراء اللي أنشأها
     */
    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class, 'created_by');
    }

    /**
     * التحويلات بين المخازن
     */
    public function warehouseTransfers()
    {
        return $this->hasMany(WarehouseTransfer::class, 'created_by');
    }

    /**
     * المدفوعات اللي سجلها
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    /**
     * المصروفات
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    /**
     * العمليات النقدية
     */
    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class, 'created_by');
    }

    /**
     * سجل النشاط
     */
    public function activities()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ==================== Scopes ====================

    /**
     * المستخدمين النشطين فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}


