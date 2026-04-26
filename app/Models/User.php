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
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
    ];

    // ==================== Role Methods ====================

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Get user's role name (Arabic)
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'مدير النظام',
            'employee' => 'موظف',
            default => 'غير معروف',
        };
    }

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
     * صلاحيات المستخدم (مباشرة + عبر الأدوار)
     */
    public function allPermissions()
    {
        $directPermissions = $this->permissions()->pluck('name')->toArray();
        $rolePermissions = $this->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->toArray();

        return array_unique(array_merge($directPermissions, $rolePermissions));
    }

    // ==================== Permission Methods ====================

    /**
     * التحقق مما إذا كان المستخدم لديه صلاحية معينة
     */
    public function hasPermission(string $permission): bool
    {
        // المدير لديه كل الصلاحيات
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->allPermissions());
    }

    /**
     * التحقق مما إذا كان المستخدم لديه أحد الصلاحيات المحددة
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return !empty(array_intersect($permissions, $this->allPermissions()));
    }

    /**
     * التحقق مما إذا كان المستخدم لديه جميع الصلاحيات المحددة
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $userPermissions = $this->allPermissions();

        return empty(array_diff($permissions, $userPermissions));
    }

    /**
     * منح صلاحية مباشرة للمستخدم
     */
    public function givePermissionTo(Permission $permission)
    {
        $this->permissions()->attach($permission->id);
        return $this;
    }

    /**
     * سحب صلاحية من المستخدم
     */
    public function revokePermissionFrom(Permission $permission)
    {
        $this->permissions()->detach($permission->id);
        return $this;
    }

    /**
     * منح صلاحيات متعددة للمستخدم
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions()->sync($permissions);
        return $this;
    }

    /**
     * إضافة دور للمستخدم
     */
    public function assignRole(Role $role)
    {
        $this->roles()->attach($role->id);
        return $this;
    }

    /**
     * إزالة دور من المستخدم
     */
    public function removeRole(Role $role)
    {
        $this->roles()->detach($role->id);
        return $this;
    }

    /**
     * تحديث أدوار المستخدم
     */
    public function syncRoles(array $roleIds)
    {
        $this->roles()->sync($roleIds);
        return $this;
    }

    /**
     * التحقق مما إذا كان المستخدم لديه دور معين
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
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


