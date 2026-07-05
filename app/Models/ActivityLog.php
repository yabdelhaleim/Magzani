<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'activity_log';

    protected $fillable = [
        'user_id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'batch_uuid',
    ];

    // ==================== Relationships ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    /**
     * تسجيل نشاط حساس جديد بسهولة
     */
    public static function log(string $action, $subject, string $description, array $properties = []): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'log_name' => strtolower(class_basename($subject)),
            'description' => $description,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'properties' => json_encode($properties),
        ]);
    }
}
