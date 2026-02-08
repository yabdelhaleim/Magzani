<?php

namespace App\Providers;
use App\Models\ProductBaseUnit;
use App\Observers\ProductBaseUnitObserver;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
    // Invoice Events
    \App\Events\Invoice\SalesInvoiceCreated::class => [
        \App\Listeners\Invoice\SendSalesInvoiceCreatedNotification::class,
        \App\Listeners\LogActivityListener::class,
    ],
    \App\Events\Invoice\SalesInvoiceConfirmed::class => [
        \App\Listeners\Invoice\UpdateSalesInvoiceConfirmedCache::class,
    ],
    \App\Events\Invoice\SalesInvoiceCancelled::class => [
        \App\Listeners\Invoice\HandleSalesInvoiceCancellation::class,
    ],
    \App\Events\Invoice\PurchaseInvoiceCreated::class => [
        \App\Listeners\Invoice\SendPurchaseInvoiceCreatedNotification::class,
    ],
    \App\Events\Invoice\PurchaseInvoiceCancelled::class => [
        \App\Listeners\Invoice\HandlePurchaseInvoiceCancellation::class,
    ],

    // Payment Events
    \App\Events\Payment\PaymentReceived::class => [
        \App\Listeners\Payment\HandlePaymentReceived::class,
    ],
    \App\Events\Payment\PaymentCancelled::class => [
        \App\Listeners\Payment\HandlePaymentCancellation::class,
    ],

    // Return Events
    \App\Events\Return\SalesReturnProcessed::class => [
        \App\Listeners\Return\HandleSalesReturnProcessed::class,
    ],
    \App\Events\Return\PurchaseReturnProcessed::class => [
        \App\Listeners\Return\HandlePurchaseReturnProcessed::class,
    ],

    // Stock Events
    \App\Events\Stock\StockLow::class => [
        \App\Listeners\Stock\SendLowStockAlert::class,
    ],
    \App\Events\Stock\StockUpdated::class => [
        \App\Listeners\Stock\UpdateStockCache::class,
    ],

    // Transfer Events
    \App\Events\Transfer\TransferInitiated::class => [
        \App\Listeners\Transfer\SendTransferInitiatedNotification::class,
    ],
    \App\Events\Transfer\TransferCompleted::class => [
        \App\Listeners\Transfer\HandleTransferCompleted::class,
    ],
    \App\Events\Transfer\TransferCancelled::class => [
        \App\Listeners\Transfer\HandleTransferCancellation::class,
    ],
    \App\Events\Transfer\TransferReversed::class => [
        \App\Listeners\Transfer\HandleTransferReversal::class,
    ],
];


    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
            ProductBaseUnit::observe(ProductBaseUnitObserver::class);
    //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}