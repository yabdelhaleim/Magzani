<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'billing_period',
        'features',
        'value_props',
        'display_label',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'features'    => 'array',
        'value_props' => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'price'       => 'decimal:2',
        'sort_order'  => 'integer',
    ];

    /**
     * Get the features / limits associated with this plan.
     */
    public function featuresList()
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * Get the features attribute (returns a collection of PlanFeature models)
     */
    public function getFeaturesAttribute()
    {
        if ($this->relationLoaded('features')) {
            return $this->getRelation('features');
        }
        return $this->featuresList()->where('is_enabled', true)->get();
    }


    /**
     * Check if this plan has a specific feature enabled
     */
    public function hasFeature(string $feature): bool
    {
        $feat = $this->featuresList()->where('feature_key', $feature)->first();
        if ($feat) {
            return (bool) $feat->is_enabled;
        }

        // Fallback to json columns if no db entry exists
        return is_array($this->features) && in_array($feature, $this->features);
    }

    /**
     * Get limit value for a specific feature
     */
    public function getLimit(string $feature): ?int
    {
        $feat = $this->featuresList()->where('feature_key', $feature)->first();
        return $feat ? $feat->limit_value : null;
    }
}
