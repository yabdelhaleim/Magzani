<?php

namespace App\Exceptions\Accounting;

use RuntimeException;

class UnbalancedEntryException extends RuntimeException
{
    public function __construct(
        public readonly float $totalDebit,
        public readonly float $totalCredit,
    ) {
        $diff = abs($totalDebit - $totalCredit);
        parent::__construct(
            "القيد غير مُوازَن: إجمالي المدين = {$totalDebit}، إجمالي الدائن = {$totalCredit}. الفرق = {$diff}"
        );
    }
}
