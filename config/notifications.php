<?php

use App\Notifications\Accounting\OverdueInvoiceNotification;
use App\Notifications\Invoice\InvoiceCancelledNotification;
use App\Notifications\Return\PurchaseReturnNotification;
use App\Notifications\Return\SalesReturnNotification;
use App\Notifications\Stock\LowStockNotification;
use App\Notifications\SystemNotification;
use App\Notifications\Transfer\TransferInitiatedNotification;

return [
    /*
    |--------------------------------------------------------------------------
    | Important Database Notifications
    |--------------------------------------------------------------------------
    |
    | Only notifications that require follow-up belong in the persistent bell.
    | Routine operation feedback remains a temporary flash message.
    |
    */
    'important_types' => [
        InvoiceCancelledNotification::class,
        SalesReturnNotification::class,
        PurchaseReturnNotification::class,
        LowStockNotification::class,
        OverdueInvoiceNotification::class,
        TransferInitiatedNotification::class,
        SystemNotification::class,
    ],

    'dropdown_limit' => 8,
    'per_page' => 20,
];
