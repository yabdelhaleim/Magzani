<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // ==================== Relationships ====================

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }

    // ==================== Methods ====================

    /**
     * منح صلاحية للدور
     */
    public function givePermissionTo(Permission $permission)
    {
        $this->permissions()->attach($permission->id);
        return $this;
    }

    /**
     * سحب صلاحية من الدور
     */
    public function revokePermissionFrom(Permission $permission)
    {
        $this->permissions()->detach($permission->id);
        return $this;
    }

    /**
     * منح صلاحيات متعددة للدور
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions()->sync($permissions);
        return $this;
    }

    /**
     * التحقق مما إذا كان الدور لديه صلاحية معينة
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    // ==================== Scopes ====================

    /**
     * أدوار النظام (لا يمكن حذفها)
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * أدوار مخصصة (يمكن حذفها)
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }
}
