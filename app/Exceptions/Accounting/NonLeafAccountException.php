<?php

namespace App\Exceptions\Accounting;

use RuntimeException;

class NonLeafAccountException extends RuntimeException
{
    public function __construct(string $accountCode, string $accountName)
    {
        parent::__construct(
            "لا يمكن الترحيل لحساب غير ورقي: [{$accountCode}] {$accountName}. يجب استخدام حساب ورقي (leaf) فقط."
        );
    }
}
