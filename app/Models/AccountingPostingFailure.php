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

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
