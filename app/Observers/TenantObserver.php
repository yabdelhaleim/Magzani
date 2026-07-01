<?php

namespace App\Observers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantObserver
{
    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        // Since plan_id is stored inside the 'data' JSON column, standard Eloquent isDirty('plan_id') 
        // won't check it directly. We check if the 'data' attribute itself is dirty, and then
        // compare the plan_id from the original data and current data.
        if ($tenant->isDirty('data')) {
            $originalData = $tenant->getOriginal('data');
            
            // Handle if Laravel returns original as string or array
            $originalArray = is_array($originalData) ? $originalData : (json_decode($originalData ?? '{}', true) ?? []);
            $originalPlanId = $originalArray['plan_id'] ?? null;
            
            $currentPlanId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? null);
            
            if ($originalPlanId !== $currentPlanId) {
                Cache::forget("tenant_{$tenant->id}_plan");
            }
        }
    }
}
