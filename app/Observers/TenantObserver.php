<?php

namespace App\Observers;

use App\Models\Tenant;

class TenantObserver
{
    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        // plan_id and custom_features are stored inside the data JSON column.
        // Any change to it can change the effective feature set, so invalidate
        // the feature cache rather than checking only plan_id.
        if ($tenant->isDirty('data')) {
            $tenant->invalidateFeatureCache();
        }
    }
}
