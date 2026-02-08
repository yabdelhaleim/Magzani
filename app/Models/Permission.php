<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // ==================== Relationships ====================

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user')->withTimestamps();
    }
}
