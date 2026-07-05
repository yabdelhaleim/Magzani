<?php

namespace App\Enums;

enum PaymentTerms: string
{
    case DUE_ON_RECEIPT = 'due_on_receipt';
    case NET15 = 'net15';
    case NET30 = 'net30';
    case NET60 = 'net60';
    case NET90 = 'net90';

    public function days(): int
    {
        return match ($this) {
            self::DUE_ON_RECEIPT => 0,
            self::NET15          => 15,
            self::NET30          => 30,
            self::NET60          => 60,
            self::NET90          => 90,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DUE_ON_RECEIPT => 'فوري عند الاستلام',
            self::NET15          => 'صافي 15 يوم',
            self::NET30          => 'صافي 30 يوم',
            self::NET60          => 'صافي 60 يوم',
            self::NET90          => 'صافي 90 يوم',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $term) => [$term->value => $term->label()])
            ->toArray();
    }

    public static function dueDateFrom(string $invoiceDate, ?string $terms = null): string
    {
        $term = self::tryFrom($terms ?? self::NET30->value) ?? self::NET30;

        return Carbon\Carbon::parse($invoiceDate)->addDays($term->days())->toDateString();
    }
}
