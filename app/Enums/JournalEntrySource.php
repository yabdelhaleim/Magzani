<?php

namespace App\Enums;

enum JournalEntrySource: string
{
    case MANUAL = 'manual';
    case SALES_INVOICE = 'sales_invoice';
    case PURCHASE_INVOICE = 'purchase_invoice';
    case PAYMENT = 'payment';
    case SUPPLIER_PAYMENT = 'supplier_payment';
    case EXPENSE = 'expense';
    case CASH_TRANSACTION = 'cash_transaction';
    case SALES_RETURN = 'sales_return';
    case PURCHASE_RETURN = 'purchase_return';
    case MANUFACTURING = 'manufacturing';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'قيد يدوي',
            self::SALES_INVOICE => 'فاتورة مبيعات',
            self::PURCHASE_INVOICE => 'فاتورة مشتريات',
            self::PAYMENT => 'دفعة عميل (سند قبض)',
            self::SUPPLIER_PAYMENT => 'سداد مورد (سند صرف)',
            self::EXPENSE => 'مصروف',
            self::CASH_TRANSACTION => 'حركة خزينة',
            self::SALES_RETURN => 'مرتجع مبيعات',
            self::PURCHASE_RETURN => 'مرتجع مشتريات',
            self::MANUFACTURING => 'أمر تصنيع',
        };
    }
}
