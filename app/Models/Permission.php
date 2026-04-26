<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module',
        'action',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // ==================== Relationships ====================

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user')->withTimestamps();
    }

    // ==================== Scopes ====================

    /**
     * صلاحيات النظام (لا يمكن حذفها)
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * صلاحيات مخصصة (يمكن حذفها)
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * صلاحيات موديول معين
     */
    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * صلاحيات إجراء معين
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
