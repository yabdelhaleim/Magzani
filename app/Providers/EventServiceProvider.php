<?php

namespace App\Providers;

use App\Events\Invoice\PurchaseInvoiceCancelled;
use App\Events\Invoice\PurchaseInvoiceConfirmed;
use App\Events\Invoice\PurchaseInvoiceCreated;
use App\Events\Invoice\SalesInvoiceCancelled;
use App\Events\Invoice\SalesInvoiceConfirmed;
use App\Events\Invoice\SalesInvoiceCreated;
use App\Events\Manufacturing\ManufacturingOrderCancelled;
use App\Events\Payment\PaymentCancelled;
use App\Events\Payment\PaymentReceived;
use App\Events\Payment\SupplierPaymentCreated;
use App\Events\Return\PurchaseReturnProcessed;
use App\Events\Return\SalesReturnProcessed;
use App\Events\Stock\StockLow;
use App\Events\Stock\StockUpdated;
use App\Events\Transfer\TransferCancelled;
use App\Events\Transfer\TransferCompleted;
use App\Events\Transfer\TransferInitiated;
use App\Events\Transfer\TransferReversed;
use App\Listeners\Accounting\PostPaymentToGL;
use App\Listeners\Accounting\PostPurchaseInvoiceToGL;
use App\Listeners\Accounting\PostPurchaseReturnToGL;
use App\Listeners\Accounting\PostSalesInvoiceToGL;
use App\Listeners\Accounting\PostSalesReturnToGL;
use App\Listeners\Accounting\PostSupplierPaymentToGL;
use App\Listeners\Accounting\ReverseManufacturingOrderFromGL;
use App\Listeners\Accounting\ReversePurchaseInvoiceFromGL;
use App\Listeners\Accounting\ReverseSalesInvoiceFromGL;
use App\Listeners\Invoice\HandlePurchaseInvoiceCancellation;
use App\Listeners\Invoice\HandleSalesInvoiceCancellation;
use App\Listeners\Invoice\SendPurchaseInvoiceCreatedNotification;
use App\Listeners\Invoice\SendSalesInvoiceCreatedNotification;
use App\Listeners\Invoice\UpdateSalesInvoiceConfirmedCache;
use App\Listeners\LogActivityListener;
use App\Listeners\Payment\HandlePaymentCancellation;
use App\Listeners\Payment\HandlePaymentReceived;
use App\Listeners\Return\HandlePurchaseReturnProcessed;
use App\Listeners\Return\HandleSalesReturnProcessed;
use App\Listeners\Stock\SendLowStockAlert;
use App\Listeners\Stock\UpdateStockCache;
use App\Listeners\Transfer\HandleTransferCancellation;
use App\Listeners\Transfer\HandleTransferCompleted;
use App\Listeners\Transfer\HandleTransferReversal;
use App\Listeners\Transfer\SendTransferInitiatedNotification;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\ProductBaseUnit;
use App\Observers\PlanObserver;
use App\Observers\ProductBaseUnitObserver;
use Illuminate\Database\Eloquent\Model;
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
        SalesInvoiceCreated::class => [
            SendSalesInvoiceCreatedNotification::class,
            LogActivityListener::class,
        ],
        SalesInvoiceConfirmed::class => [
            UpdateSalesInvoiceConfirmedCache::class,
            PostSalesInvoiceToGL::class,
        ],
        SalesInvoiceCancelled::class => [
            HandleSalesInvoiceCancellation::class,
            ReverseSalesInvoiceFromGL::class,
        ],
        PurchaseInvoiceCreated::class => [
            SendPurchaseInvoiceCreatedNotification::class,
        ],
        PurchaseInvoiceConfirmed::class => [
            PostPurchaseInvoiceToGL::class,
        ],
        PurchaseInvoiceCancelled::class => [
            HandlePurchaseInvoiceCancellation::class,
            ReversePurchaseInvoiceFromGL::class,
        ],
        ManufacturingOrderCancelled::class => [
            ReverseManufacturingOrderFromGL::class,
        ],

        // Payment Events
        PaymentReceived::class => [
            HandlePaymentReceived::class,
            PostPaymentToGL::class,
        ],
        PaymentCancelled::class => [
            HandlePaymentCancellation::class,
        ],
        SupplierPaymentCreated::class => [
            PostSupplierPaymentToGL::class,
        ],

        // Return Events
        SalesReturnProcessed::class => [
            HandleSalesReturnProcessed::class,
            PostSalesReturnToGL::class,
        ],
        PurchaseReturnProcessed::class => [
            HandlePurchaseReturnProcessed::class,
            PostPurchaseReturnToGL::class,
        ],

        // Stock Events
        StockLow::class => [
            SendLowStockAlert::class,
        ],
        StockUpdated::class => [
            UpdateStockCache::class,
        ],

        // Transfer Events
        TransferInitiated::class => [
            SendTransferInitiatedNotification::class,
        ],
        TransferCompleted::class => [
            HandleTransferCompleted::class,
        ],
        TransferCancelled::class => [
            HandleTransferCancellation::class,
        ],
        TransferReversed::class => [
            HandleTransferReversal::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        ProductBaseUnit::observe(ProductBaseUnitObserver::class);
        $planObserver = app(PlanObserver::class);
        Plan::observe($planObserver);

        // plan_features rows are not the Plan model itself, so observe
        // its saved/deleted events directly to invalidate caches.
        Event::listen('eloquent.saved: '.PlanFeature::class, function (PlanFeature $feature) use ($planObserver): void {
            $planObserver->onFeatureSaved($feature);
        });
        Event::listen('eloquent.deleted: '.PlanFeature::class, function (PlanFeature $feature) use ($planObserver): void {
            $planObserver->onFeatureDeleted($feature);
        });
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
