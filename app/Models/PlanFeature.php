<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    use HasFactory;

    // Feature keys constants
    const POS = 'pos';
    const MANUFACTURING = 'manufacturing';
    const MULTI_WAREHOUSE = 'multi_warehouse';
    const ACCOUNTING = 'accounting';
    const ACCOUNTING_ADVANCED = 'accounting_advanced';
    const STOCK_COUNT = 'stock_count';
    const PURCHASE = 'purchase';
    const REPORTS_ADVANCED = 'reports_advanced';

    protected $fillable = [
        'plan_id',
        'feature_key',
        'is_enabled',
        'limit_value',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'limit_value' => 'integer',
    ];

    /**
     * Get the plan that owns this feature limit.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
