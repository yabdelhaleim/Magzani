<?php

namespace App\Observers;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;

class PlanObserver
{
    /**
     * Invalidate every tenant that subscribes to the affected plan slugs.
     *
     * @param  array<int, string|null>  $slugs
     */
    private function invalidateTenants(array $slugs): void
    {
        $slugs = array_values(array_filter(array_unique($slugs)));

        if ($slugs === []) {
            return;
        }

        Tenant::all()
            ->filter(function (Tenant $tenant) use ($slugs): bool {
                $planId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? null);

                return in_array($planId, $slugs, true);
            })
            ->each(fn (Tenant $tenant) => $tenant->invalidateFeatureCache());
    }

    /**
     * Capture the original slug so we can invalidate tenants assigned to
     * the old identifier after a rename.
     */
    public function updating(Plan $plan): void
    {
        if ($plan->isDirty('slug')) {
            $plan->_oldSlug = $plan->getOriginal('slug');
        }
    }

    public function updated(Plan $plan): void
    {
        $slugs = [$plan->slug];

        if (isset($plan->_oldSlug)) {
            $slugs[] = $plan->_oldSlug;
            unset($plan->_oldSlug);
        }

        $this->invalidateTenants($slugs);
    }

    public function deleted(Plan $plan): void
    {
        $this->invalidateTenants([$plan->slug]);
    }

    /**
     * When a plan_features row is created/updated/deleted, invalidate the
     * matching plan so all subscribed tenants get fresh data.
     */
    public function onFeatureSaved(PlanFeature $feature): void
    {
        $plan = $feature->plan ?? Plan::find($feature->plan_id);

        if ($plan) {
            $this->invalidateTenants([$plan->slug]);
        }
    }

    public function onFeatureDeleted(PlanFeature $feature): void
    {
        $this->onFeatureSaved($feature);
    }
}
