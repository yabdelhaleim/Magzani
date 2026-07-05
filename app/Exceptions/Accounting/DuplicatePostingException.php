<?php

namespace App\Exceptions\Accounting;

use RuntimeException;

class DuplicatePostingException extends RuntimeException
{
    public function __construct(string $eventKey, int $existingEntryId)
    {
        parent::__construct(
            "ترحيل مكرر: المفتاح [{$eventKey}] مُسجَّل بالفعل في القيد #{$existingEntryId}."
        );
    }
}
