<?php

namespace App\Enums;

enum AccountTypeEnum: int
{
    case ASSET = 1;
    case LIABILITY = 2;
    case EQUITY = 3;
    case REVENUE = 4;
    case EXPENSE = 5;

    public function code(): string
    {
        return match ($this) {
            self::ASSET => 'asset',
            self::LIABILITY => 'liability',
            self::EQUITY => 'equity',
            self::REVENUE => 'revenue',
            self::EXPENSE => 'expense',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::ASSET => 'أصول',
            self::LIABILITY => 'خصوم',
            self::EQUITY => 'حقوق ملكية',
            self::REVENUE => 'إيرادات',
            self::EXPENSE => 'مصروفات',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::ASSET => 'Assets',
            self::LIABILITY => 'Liabilities',
            self::EQUITY => 'Equity',
            self::REVENUE => 'Revenue',
            self::EXPENSE => 'Expenses',
        };
    }

    public function normalBalance(): NormalBalance
    {
        return match ($this) {
            self::ASSET, self::EXPENSE => NormalBalance::DEBIT,
            self::LIABILITY, self::EQUITY, self::REVENUE => NormalBalance::CREDIT,
        };
    }
}
