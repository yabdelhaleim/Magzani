<?php

namespace App\Events\Manufacturing;

use App\Models\ManufacturingOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ManufacturingOrderCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ManufacturingOrder $order,
        public ?string $reason = null
    ) {}
}
