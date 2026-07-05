<?php

namespace App\Exceptions\Accounting;

use RuntimeException;

class ClosedPeriodException extends RuntimeException
{
    public function __construct(string $periodName)
    {
        parent::__construct(
            "الفترة المالية [{$periodName}] مغلقة ولا يمكن الترحيل إليها."
        );
    }
}
