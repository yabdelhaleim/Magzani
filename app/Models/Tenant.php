<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Custom database columns that should be directly accessible on the model.
     * Any other columns will be stored inside the 'data' JSON column.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'plan_expires_at',
            'trial_ends_at',
            'data',
        ];
    }

    /**
     * التحقق من توفر ميزة معينة في باقة المستأجر
     */
    public function hasFeature(string $feature): bool
    {
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);

        if ($planId === 'custom') {
            $customFeatures = $this->custom_features ?? ($this->data['custom_features'] ?? []);
            return is_array($customFeatures) && in_array($feature, $customFeatures);
        }

        // جلب ميزات الباقة القياسية من قاعدة البيانات المركزية
        $centralConnection = config('tenancy.database.central_connection', 'central');
        
        try {
            $plan = \Illuminate\Support\Facades\DB::connection($centralConnection)
                ->table('plans')
                ->where('slug', $planId)
                ->first();
                
            if ($plan) {
                $features = json_decode($plan->features, true) ?? [];
                return is_array($features) && in_array($feature, $features);
            }
        } catch (\Exception $e) {
            // تجاهل الخطأ
        }

        return false;
    }

    /**
     * علاقة الباقة مع المستأجر
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'slug');
    }

    /**
     * جلب الباقة ديناميكياً للتعامل مع الباقات القياسية والمخصصة
     */
    public function getPlanAttribute()
    {
        $planId = $this->plan_id ?? ($this->data['plan_id'] ?? null);
        if (!$planId) {
            return null;
        }

        if ($planId === 'custom') {
            $plan = Plan::where('slug', 'custom')->first();
            if ($plan) {
                $customFeatures = $this->custom_features ?? ($this->data['custom_features'] ?? []);
                $featuresCollection = collect(is_array($customFeatures) ? $customFeatures : [])
                    ->map(function ($key) use ($plan) {
                        $f = new PlanFeature();
                        $f->plan_id = $plan->id;
                        $f->feature_key = $key;
                        $f->is_enabled = true;
                        return $f;
                    });
                $plan->setRelation('features', $featuresCollection);
                return $plan;
            }
        }

        return $this->getRelationValue('plan') ?? Plan::where('slug', $planId)->first();
    }
}

