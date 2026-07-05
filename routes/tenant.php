<?php

declare(strict_types=1);

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\Accounting\AccountingDashboardController;
use App\Http\Controllers\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Accounting\JournalEntryController;
use App\Http\Controllers\Accounting\ReceiptVoucherController;
use App\Http\Controllers\Accounting\PaymentVoucherController;
use App\Http\Controllers\Accounting\FinancialReportController;
use App\Http\Controllers\Accounting\FiscalPeriodController;
use App\Http\Controllers\Accounting\AccountingSettingsController;
use App\Http\Controllers\Accounting\AccountingSetupController;
use App\Http\Controllers\Accounting\PostingFailureController;
use App\Http\Controllers\Accounting\RecurringJournalEntryController;
use App\Http\Controllers\Accounting\YearEndClosingController;
use App\Http\Controllers\Accounting\FixedAssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\ManufacturingCostController;
use App\Http\Controllers\ManufacturingOrderController;
use App\Http\Controllers\PriceUpdateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\RawMaterialTemplateController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\PosShiftController;
use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
|
*/

Route::middleware([
    'web',
    PreventAccessFromCentralDomains::class,
    InitializeTenancyByDomain::class,
    'feature',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes (Tenant-specific)
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
    Route::post('/login', [LoginController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

    /** Default HOME path redirects to Dashboard root */
    Route::redirect('/home', '/', 302);

    Route::get('plan/upgrade', [PlanController::class, 'upgrade'])->name('plan.upgrade');


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth', 'role');

    /*
    |--------------------------------------------------------------------------
    | POS & Shifts Group (Protected by feature:pos)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'feature:pos'])->group(function () {
        Route::get('/pos', \App\Livewire\PosPanel::class)->name('pos.index');
        Route::get('/pos/returns', \App\Livewire\PosReturnPanel::class)->name('pos.returns');
        Route::get('/pos/settings', [\App\Http\Controllers\PosSettingController::class, 'index'])->name('pos.settings.index')->middleware('admin.only');
        Route::post('/pos/settings', [\App\Http\Controllers\PosSettingController::class, 'update'])->name('pos.settings.update')->middleware('admin.only');

        // POS Shifts (Wordiaat / Cashier Sessions)
        Route::get('/pos/shift/open',  [PosShiftController::class, 'create'])->name('pos.shift.create');
        Route::post('/pos/shift/open', [PosShiftController::class, 'open'])->name('pos.shift.open');
        Route::get('/pos/shift/close', [PosShiftController::class, 'closeView'])->name('pos.shift.close-view');
        Route::post('/pos/shift/close',[PosShiftController::class, 'close'])->name('pos.shift.close');
        Route::get('/pos/history',     [PosShiftController::class, 'history'])->name('pos.history');
        Route::get('/pos/x-report',    [PosShiftController::class, 'xReport'])->name('pos.xreport');
        Route::get('/pos/shift/{id}/z-report', [PosShiftController::class, 'zReport'])->name('pos.shift.zreport');

        // Invoices - Sales (POS Invoices)
        Route::prefix('invoices/sales')->name('invoices.sales.')->controller(SalesController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}/print', 'printReceipt')->name('print');

            Route::middleware('admin.only')->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
            });

            Route::get('/{id}', 'show')->name('show');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Manufacturing & Wood Stocks Group (Protected by feature:manufacturing)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'feature:manufacturing'])->group(function () {
        // Manufacturing Cost Calculator
        Route::prefix('manufacturing')->name('manufacturing.')->middleware('admin.only')->group(function () {
            Route::get('/', [ManufacturingCostController::class, 'index'])->name('index');
            Route::get('/create', [ManufacturingCostController::class, 'create'])->name('create');
            Route::post('/', [ManufacturingCostController::class, 'store'])->name('store');
            Route::post('/calculate', [ManufacturingCostController::class, 'calculateAjax'])->name('calculate');

            // Wood Inventory
            Route::get('wood-stocks', [\App\Http\Controllers\WoodStockController::class, 'index'])->name('wood-stocks.index');
            Route::get('wood-stocks/create', [\App\Http\Controllers\WoodStockController::class, 'create'])->name('wood-stocks.create');
            Route::post('wood-stocks', [\App\Http\Controllers\WoodStockController::class, 'store'])->name('wood-stocks.store');
            Route::get('wood-dispensings', [\App\Http\Controllers\WoodDispensingController::class, 'index'])->name('wood-dispensings.index');
            Route::get('wood-stocks/{woodStock}/dispense', [\App\Http\Controllers\WoodDispensingController::class, 'create'])->name('wood-dispensings.create');
            Route::post('wood-dispensings', [\App\Http\Controllers\WoodDispensingController::class, 'store'])->name('wood-dispensings.store');
            Route::get('wood-dispensings/{dispensing}/create-invoice', [\App\Http\Controllers\WoodDispensingController::class, 'createInvoice'])->name('wood-dispensings.create-invoice');

            Route::get('/{manufacturingCost}', [ManufacturingCostController::class, 'show'])->name('show');
            Route::get('/{manufacturingCost}/edit', [ManufacturingCostController::class, 'edit'])->name('edit');
            Route::put('/{manufacturingCost}', [ManufacturingCostController::class, 'update'])->name('update');
            Route::delete('/{manufacturingCost}', [ManufacturingCostController::class, 'destroy'])->name('destroy');
            Route::post('/{manufacturingCost}/confirm', [ManufacturingCostController::class, 'confirm'])->name('confirm');
        });

        // Manufacturing Orders
        Route::prefix('manufacturing-orders')->name('manufacturing-orders.')->middleware('admin.only')->group(function () {
            Route::get('/', [ManufacturingOrderController::class, 'index'])->name('index');
            Route::get('/create', [ManufacturingOrderController::class, 'create'])->name('create');
            Route::post('/', [ManufacturingOrderController::class, 'store'])->name('store');
            Route::post('/calculate', [ManufacturingOrderController::class, 'calculateCosts'])->name('calculate');

            // Raw Materials
            Route::get('/raw-materials', [RawMaterialTemplateController::class, 'index'])->name('raw-materials.index');
            Route::get('/raw-materials/create', [RawMaterialTemplateController::class, 'create'])->name('raw-materials.create');
            Route::post('/raw-materials', [RawMaterialTemplateController::class, 'store'])->name('raw-materials.store');
            Route::get('/raw-materials/{id}', [RawMaterialTemplateController::class, 'show'])->name('raw-materials.show');
            Route::get('/raw-materials/{id}/edit', [RawMaterialTemplateController::class, 'edit'])->name('raw-materials.edit');
            Route::put('/raw-materials/{id}', [RawMaterialTemplateController::class, 'update'])->name('raw-materials.update');
            Route::delete('/raw-materials/{id}', [RawMaterialTemplateController::class, 'destroy'])->name('raw-materials.destroy');

            Route::get('/{manufacturingOrder}', [ManufacturingOrderController::class, 'show'])->name('show');
            Route::get('/{manufacturingOrder}/edit', [ManufacturingOrderController::class, 'edit'])->name('edit');
            Route::put('/{manufacturingOrder}', [ManufacturingOrderController::class, 'update'])->name('update');
            Route::delete('/{manufacturingOrder}', [ManufacturingOrderController::class, 'destroy'])->name('destroy');
            Route::match(['post', 'patch'], '/{manufacturingOrder}/confirm', [ManufacturingOrderController::class, 'confirm'])->name('confirm');
            Route::match(['post', 'patch'], '/{manufacturingOrder}/complete', [ManufacturingOrderController::class, 'complete'])->name('complete');
            Route::match(['post', 'patch'], '/{manufacturingOrder}/cancel', [ManufacturingOrderController::class, 'cancel'])->name('cancel');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Warehouse Transfers Group (Protected by feature:multi_warehouse)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'feature:multi_warehouse'])->group(function () {
        Route::prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/', [TransferController::class, 'index'])->name('index');
            Route::get('/create', [TransferController::class, 'create'])->name('create');
            Route::post('/', [TransferController::class, 'store'])->name('store');
            Route::get('/pending', [TransferController::class, 'pending'])->name('pending');
            Route::get('/{transfer}', [TransferController::class, 'show'])->name('show');
            Route::post('/{transfer}/reverse', [TransferController::class, 'reverse'])->name('reverse');
            Route::post('/{transfer}/cancel', [TransferController::class, 'cancel'])->name('cancel');
            Route::get('/warehouse/{warehouse}/history', [TransferController::class, 'warehouseHistory'])->name('warehouse.history');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Accounting, Expenses, Treasury & Supplier Payments Group (Protected by feature:accounting)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'feature:accounting'])->group(function () {
        Route::prefix('accounting')->name('accounting.')->middleware('admin.only')->group(function () {
            Route::get('/treasury', [AccountingController::class, 'treasury'])->name('treasury');
            Route::get('/payments', [AccountingController::class, 'index'])->name('payments');
            Route::post('/deposits', [AccountingController::class, 'storeDeposit'])->name('deposits.store');
            Route::post('/withdrawals', [AccountingController::class, 'storeWithdrawal'])->name('withdrawals.store');
            Route::put('/transactions/{id}', [AccountingController::class, 'update'])->name('transactions.update');
            Route::delete('/transactions/{id}', [AccountingController::class, 'destroy'])->name('transactions.destroy');
            Route::get('/expenses', [AccountingController::class, 'expenses'])->name('expenses.index');
            Route::post('/expenses', [AccountingController::class, 'storeExpense'])->name('expenses.store');
            Route::put('/expenses/{id}', [AccountingController::class, 'updateExpense'])->name('expenses.update');
            Route::delete('/expenses/{id}', [AccountingController::class, 'destroyExpense'])->name('expenses.destroy');
            Route::get('/statistics', [AccountingController::class, 'statistics'])->name('statistics');
        });

        Route::prefix('reports')->name('reports.')->middleware('admin.only')->group(function () {
            Route::get('/financial', [ReportingController::class, 'financial'])->name('financial');
            Route::get('/financial/export', [ReportingController::class, 'exportFinancial'])->name('financial.export');
            Route::get('/inventory', [ReportingController::class, 'inventory'])->name('inventory');
            Route::get('/inventory/export', [ReportingController::class, 'exportInventory'])->name('inventory.export');
            Route::get('/profit-loss', [ReportingController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/profit-loss/export', [ReportingController::class, 'exportProfitLoss'])->name('profit-loss.export');

            // Wood Reports
            Route::get('/wood-stock', [ReportingController::class, 'woodStock'])->name('wood-stock');
            Route::get('/wood-movement', [ReportingController::class, 'woodMovement'])->name('wood-movement');
            Route::get('/wood-cost-production', [ReportingController::class, 'woodCostProduction'])->name('wood-cost-production');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Warehouses (Admin Only) - Shared / Standard Guarded
    |--------------------------------------------------------------------------
    */
    Route::prefix('warehouses')->name('warehouses.')->middleware(['auth', 'role', 'feature:warehouses'])->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
        Route::get('/{warehouse}/add-product', [WarehouseController::class, 'createProduct'])->name('add-product');
        Route::post('/{warehouse}/products', [WarehouseController::class, 'addProduct'])->name('products.store');
        Route::get('/{warehouse}/low-stock', [WarehouseController::class, 'lowStock'])->name('low-stock');
        Route::get('/{warehouse}/movements', [WarehouseController::class, 'movements'])->name('movements');
        Route::get('/{warehouse}/search', [WarehouseController::class, 'search'])->name('search');
    });

    /*
    |--------------------------------------------------------------------------
    | Warehouse Orders (Inbound & Outbound)
    |--------------------------------------------------------------------------
    */
    Route::prefix('warehouse-orders')->name('warehouse-orders.')->middleware(['auth', 'role', 'feature:warehouses'])->group(function () {
        Route::get('/stock-preview', [\App\Http\Controllers\WarehouseOrderController::class, 'warehouseStockPreview'])->name('stock-preview');

        // Inbound Orders
        Route::get('/inbound', [\App\Http\Controllers\WarehouseOrderController::class, 'inboundIndex'])->name('inbound.index');
        Route::get('/inbound/create', [\App\Http\Controllers\WarehouseOrderController::class, 'inboundCreate'])->name('inbound.create');
        Route::post('/inbound', [\App\Http\Controllers\WarehouseOrderController::class, 'inboundStore'])->name('inbound.store');
        Route::get('/inbound/{order}', [\App\Http\Controllers\WarehouseOrderController::class, 'inboundShow'])->name('inbound.show');
        Route::get('/inbound/{order}/print', [\App\Http\Controllers\WarehouseOrderController::class, 'inboundPrint'])->name('inbound.print');

        // Outbound Orders
        Route::get('/outbound', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundIndex'])->name('outbound.index');
        Route::get('/outbound/create', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundCreate'])->name('outbound.create');
        Route::post('/outbound', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundStore'])->name('outbound.store');
        Route::get('/outbound/{order}', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundShow'])->name('outbound.show');
        Route::get('/outbound/{order}/print', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundPrint'])->name('outbound.print');
        Route::post('/outbound/{order}/approve', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundApprove'])->name('outbound.approve');
        Route::post('/outbound/{order}/cancel', [\App\Http\Controllers\WarehouseOrderController::class, 'outboundCancel'])->name('outbound.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Stock Counts
    |--------------------------------------------------------------------------
    */
    Route::prefix('stock-counts')->name('stock-counts.')->middleware(['auth', 'role', 'feature:warehouses'])->group(function () {
        Route::get('/', [StockCountController::class, 'index'])->name('index');
        Route::get('/create', [StockCountController::class, 'create'])->name('create');
        Route::post('/', [StockCountController::class, 'store'])->name('store');
        Route::get('/{stockCount}', [StockCountController::class, 'show'])->name('show');
        Route::post('/{stockCount}/start', [StockCountController::class, 'start'])->name('start');
        Route::get('/{stockCount}/count', [StockCountController::class, 'count'])->name('count');
        Route::post('/{stockCount}/complete', [StockCountController::class, 'complete'])->name('complete');
        Route::post('/{stockCount}/cancel', [StockCountController::class, 'cancel'])->name('cancel');
        Route::post('/{stockCount}/items/{item}/approve', [StockCountController::class, 'approveItem'])->name('items.approve');
        Route::post('/{stockCount}/approve-all', [StockCountController::class, 'approveAll'])->name('approve-all');
        Route::post('/{stockCount}/items/{item}', [StockCountController::class, 'countItem'])->name('items.count');
        Route::get('/warehouses/{warehouse}/products', [StockCountController::class, 'getWarehouseProducts'])->name('warehouse-products');
        Route::get('/{stockCount}/print', [StockCountController::class, 'print'])->name('print');
    });

    /*
    |--------------------------------------------------------------------------
    | Inventory Movements
    |--------------------------------------------------------------------------
    */
    Route::prefix('movements')->name('movements.')->middleware(['auth', 'feature:warehouses'])->group(function () {
        Route::get('/', [InventoryMovementController::class, 'index'])->name('index');
        Route::get('/product/{product}', [InventoryMovementController::class, 'productMovements'])->name('product');
        Route::get('/export', [InventoryMovementController::class, 'export'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | Products (No Gate)
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->name('products.')->middleware('auth')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/barcode/print', [ProductController::class, 'barcode'])->name('barcode');
        Route::get('/bulk-price-update', [PriceUpdateController::class, 'bulkPriceUpdatePage'])->name('bulk-price-update');
        Route::get('/ajax/categories-by-unit', [PriceUpdateController::class, 'getCategoriesByUnit'])->name('ajax.categories-by-unit');
        Route::get('/ajax/by-unit-category', [PriceUpdateController::class, 'getProductsByUnitAndCategory'])->name('ajax.by-unit-category');
        Route::post('/ajax/preview-smart-update', [PriceUpdateController::class, 'previewSmartUpdate'])->name('ajax.preview-smart-update');
        Route::post('/bulk-price-update/apply', [PriceUpdateController::class, 'applyBulkPriceUpdate'])->name('bulk-price-update.apply');
        Route::get('/ajax/suggested-pricing', [ProductController::class, 'getSuggestedPricing'])->name('ajax.suggested-pricing');
        Route::get('/bulk-price-update/get-products', [PriceUpdateController::class, 'getProductsByBaseUnit'])->name('bulk-price-update.get-products');
        Route::post('/bulk-price-update/preview', [PriceUpdateController::class, 'previewBulkPriceUpdate'])->name('bulk-price-update.preview');
        Route::get('/units-statistics', [PriceUpdateController::class, 'unitsStatistics'])->name('units-statistics');
        Route::get('/ajax/convert-unit-price', [PriceUpdateController::class, 'convertUnitPrice'])->name('ajax.convert-unit-price');
        Route::get('/ajax/unit-details', [PriceUpdateController::class, 'getUnitDetails'])->name('ajax.unit-details');

        Route::middleware('admin.only')->group(function () {
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

        Route::post('/{product}/update-price', [ProductController::class, 'updatePrice'])->name('update-price');
        Route::get('/{product}/price-history', [ProductController::class, 'priceHistory'])->name('price-history');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
    Route::prefix('categories')->name('categories.')->middleware('auth')->group(function () {
        Route::get('/', [\App\Http\Controllers\CategoryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/toggle-status', [\App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/list', [\App\Http\Controllers\CategoryController::class, 'list'])->name('list');
    });

    /*
    |--------------------------------------------------------------------------
    | Invoices - Purchases
    |--------------------------------------------------------------------------
    */
    Route::prefix('invoices/purchases')->name('invoices.purchases.')->middleware(['auth', 'feature:purchase'])->group(function () {
        Route::get('/', [PurchaseInvoiceController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseInvoiceController::class, 'create'])->name('create');
        Route::post('/', [PurchaseInvoiceController::class, 'store'])->name('store');
        Route::post('/{invoice}/confirm', [PurchaseInvoiceController::class, 'confirm'])->name('confirm');
        Route::get('/export', [PurchaseInvoiceController::class, 'export'])->name('export');
        Route::get('/{invoice}', [PurchaseInvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [PurchaseInvoiceController::class, 'edit'])->name('edit');
        Route::get('/{invoice}/print', [PurchaseInvoiceController::class, 'print'])->name('print');
        Route::get('/{invoice}/export', [PurchaseInvoiceController::class, 'exportSingle'])->name('export.single');
        Route::put('/{invoice}', [PurchaseInvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [PurchaseInvoiceController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Invoices - Sales Returns
    |--------------------------------------------------------------------------
    */
    Route::prefix('invoices/sales-returns')->name('invoices.sales-returns.')->middleware(['auth', 'feature:pos'])->group(function () {
        Route::get('/', [SalesReturnsController::class, 'index'])->name('index');
        Route::get('/create', [SalesReturnsController::class, 'create'])->name('create');
        Route::post('/', [SalesReturnsController::class, 'store'])->name('store');
        Route::get('/{salesReturn}', [SalesReturnsController::class, 'show'])->name('show');
        Route::delete('/{salesReturn}', [SalesReturnsController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Purchase Returns
    |--------------------------------------------------------------------------
    */
    Route::prefix('invoices/purchase-returns')->name('invoices.purchase-returns.')->middleware(['auth', 'feature:purchase'])->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
        Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
        Route::get('/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('show');
        Route::get('/{purchaseReturn}/edit', [PurchaseReturnController::class, 'edit'])->name('edit');
        Route::put('/{purchaseReturn}', [PurchaseReturnController::class, 'update'])->name('update');
        Route::delete('/{purchaseReturn}', [PurchaseReturnController::class, 'destroy'])->name('destroy');
        Route::get('/ajax/available-items/{invoice}', [PurchaseReturnController::class, 'getAvailableItems'])->name('ajax.available-items');
    });

    /*
    |--------------------------------------------------------------------------
    | Customers (No Gate)
    |--------------------------------------------------------------------------
    */
    Route::prefix('customers')->name('customers.')->middleware('auth')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/export', [CustomerController::class, 'export'])->name('export');

        Route::middleware('admin.only')->group(function () {
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        });

        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/statement', [CustomerController::class, 'statement'])->name('statement');
        Route::get('/{customer}/statement/export', [CustomerController::class, 'exportStatement'])->name('statement.export');
    });

    /*
    |--------------------------------------------------------------------------
    | Suppliers (No Gate)
    |--------------------------------------------------------------------------
    */
    Route::prefix('suppliers')->name('suppliers.')->middleware('auth')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/export', [SupplierController::class, 'export'])->name('export');

        Route::middleware('admin.only')->group(function () {
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        });

        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/statement', [SupplierController::class, 'statement'])->name('statement');
        Route::get('/{supplier}/statement/export', [SupplierController::class, 'exportStatement'])->name('statement.export');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('auth', 'admin.only');
    Route::post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update')->middleware('auth', 'admin.only');
    Route::post('/settings/logo/delete', [SettingsController::class, 'deleteLogo'])->name('settings.logo.delete')->middleware('auth', 'admin.only');
    Route::post('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update')->middleware('auth', 'admin.only');

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->middleware('auth', 'admin.only')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
    });

    /*
    |--------------------------------------------------------------------------
    | Permissions Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('permissions')->name('permissions.')->middleware('auth', 'admin.only')->group(function () {
        Route::get('/', [\App\Http\Controllers\PermissionsController::class, 'index'])->name('index');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\PermissionsController::class, 'editUser'])->name('edit-user');
        Route::put('/users/{user}', [\App\Http\Controllers\PermissionsController::class, 'updateUser'])->name('update-user');
        Route::get('/roles', [\App\Http\Controllers\PermissionsController::class, 'roles'])->name('roles');
        Route::post('/roles', [\App\Http\Controllers\PermissionsController::class, 'storeRole'])->name('store-role');
        Route::put('/roles/{role}', [\App\Http\Controllers\PermissionsController::class, 'updateRole'])->name('update-role');
        Route::delete('/roles/{role}', [\App\Http\Controllers\PermissionsController::class, 'destroyRole'])->name('destroy-role');
        Route::put('/roles/{role}/permissions', [\App\Http\Controllers\PermissionsController::class, 'updateRolePermissions'])->name('update-role-permissions');
        Route::get('/print', [\App\Http\Controllers\PermissionsController::class, 'printReport'])->name('print');
    });


    /*
    |--------------------------------------------------------------------------
    | Accounting & Finance Module (Protected by feature:accounting_advanced)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'feature:accounting_advanced', 'admin.only'])
        ->prefix('accounting')
        ->name('accounting.')
        ->group(function () {

            // ── لوحة التحكم ──
            Route::get('/', [AccountingDashboardController::class, 'index'])->name('dashboard');
            Route::get('/integrity-check', [AccountingDashboardController::class, 'integrityCheck'])->name('integrity-check');
            Route::post('/integrity/fix', [AccountingDashboardController::class, 'runIntegrityFix'])->name('integrity.fix');

            // ── معالج الإعداد (Tenant Onboarding Wizard) ──
            Route::prefix('setup')->name('setup.')->group(function () {
                Route::get('/',                   [AccountingSetupController::class, 'index'])->name('index');
                Route::get('/step/{step}',        [AccountingSetupController::class, 'step'])->name('step');
                Route::post('/chart',             [AccountingSetupController::class, 'saveChart'])->name('save-chart');
                Route::post('/fiscal-year',       [AccountingSetupController::class, 'saveFiscalYear'])->name('save-fiscal-year');
                Route::post('/opening-balances',  [AccountingSetupController::class, 'saveOpeningBalances'])->name('save-opening-balances');
                Route::post('/auto-posting',      [AccountingSetupController::class, 'saveAutoPosting'])->name('save-auto-posting');
                Route::get('/complete',           [AccountingSetupController::class, 'complete'])->name('complete');
            });

            // ── دليل الحسابات (Chart of Accounts) ──
            Route::prefix('coa')->name('coa.')->group(function () {
                Route::get('/',              [ChartOfAccountsController::class, 'index'])->name('index');
                Route::get('/create',        [ChartOfAccountsController::class, 'create'])->name('create');
                Route::post('/',             [ChartOfAccountsController::class, 'store'])->name('store');
                Route::get('/export',        [ChartOfAccountsController::class, 'export'])->name('export');
                Route::get('/{account}',     [ChartOfAccountsController::class, 'show'])->name('show');
                Route::get('/{account}/edit',[ChartOfAccountsController::class, 'edit'])->name('edit');
                Route::put('/{account}',     [ChartOfAccountsController::class, 'update'])->name('update');
                Route::delete('/{account}',  [ChartOfAccountsController::class, 'destroy'])->name('destroy');
            });

            // ── قيود اليومية (Journal Entries) ──
            Route::prefix('journal')->name('journal.')->group(function () {
                Route::get('/',                          [JournalEntryController::class, 'index'])->name('index');
                Route::get('/create',                    [JournalEntryController::class, 'create'])->name('create');
                Route::middleware('throttle:30,1')->group(function () {
                    Route::post('/',                     [JournalEntryController::class, 'store'])->name('store');
                    Route::post('/{journalEntry}/post',  [JournalEntryController::class, 'post'])->name('post');
                    Route::post('/{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])->name('reverse');
                });
                Route::get('/{journalEntry}',            [JournalEntryController::class, 'show'])->name('show');
                Route::get('/{journalEntry}/print',      [JournalEntryController::class, 'print'])->name('print');
            });

            // ── سندات القبض (Receipt Vouchers) ──
            Route::prefix('vouchers/receipt')->name('vouchers.receipt.')->group(function () {
                Route::get('/',                       [ReceiptVoucherController::class, 'index'])->name('index');
                Route::get('/create',                 [ReceiptVoucherController::class, 'create'])->name('create');
                Route::post('/',                      [ReceiptVoucherController::class, 'store'])->name('store');
                Route::get('/{journalEntry}',         [ReceiptVoucherController::class, 'show'])->name('show');
                Route::get('/{journalEntry}/print',   [ReceiptVoucherController::class, 'print'])->name('print');
            });

            // ── سندات الصرف (Payment Vouchers) ──
            Route::prefix('vouchers/payment')->name('vouchers.payment.')->group(function () {
                Route::get('/',                       [PaymentVoucherController::class, 'index'])->name('index');
                Route::get('/create',                 [PaymentVoucherController::class, 'create'])->name('create');
                Route::post('/',                      [PaymentVoucherController::class, 'store'])->name('store');
                Route::get('/{journalEntry}',         [PaymentVoucherController::class, 'show'])->name('show');
                Route::get('/{journalEntry}/print',   [PaymentVoucherController::class, 'print'])->name('print');
            });

            // ── التقارير المالية ──
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/trial-balance',       [FinancialReportController::class, 'trialBalance'])->name('trial-balance');
                Route::get('/income-statement',    [FinancialReportController::class, 'incomeStatement'])->name('income-statement');
                Route::get('/comparative-income',  [FinancialReportController::class, 'comparativeIncome'])->name('comparative-income');
                Route::get('/balance-sheet',       [FinancialReportController::class, 'balanceSheet'])->name('balance-sheet');
                Route::get('/general-ledger',      [FinancialReportController::class, 'generalLedger'])->name('general-ledger');
                Route::get('/partner-ledger',      [FinancialReportController::class, 'partnerLedger'])->name('partner-ledger');
                Route::get('/audit-trail',         [FinancialReportController::class, 'auditTrail'])->name('audit-trail');
                Route::get('/aging',               [FinancialReportController::class, 'agingReport'])->name('aging');
                Route::get('/financial-ratios',    [FinancialReportController::class, 'financialRatios'])->name('financial-ratios');
                Route::match(['get', 'post'], '/vat-settlement', [FinancialReportController::class, 'vatSettlement'])->name('vat-settlement');
            });

            // ── القيود المتكررة ──
            Route::prefix('recurring')->name('recurring.')->group(function () {
                Route::get('/',                          [RecurringJournalEntryController::class, 'index'])->name('index');
                Route::get('/create',                    [RecurringJournalEntryController::class, 'create'])->name('create');
                Route::post('/',                         [RecurringJournalEntryController::class, 'store'])->name('store');
                Route::get('/{recurring}/edit',          [RecurringJournalEntryController::class, 'edit'])->name('edit');
                Route::put('/{recurring}',               [RecurringJournalEntryController::class, 'update'])->name('update');
                Route::delete('/{recurring}',            [RecurringJournalEntryController::class, 'destroy'])->name('destroy');
                Route::post('/{recurring}/run',          [RecurringJournalEntryController::class, 'runNow'])->name('run');
            });

            // ── الفترات المالية (Fiscal Periods) ──
            Route::prefix('fiscal')->name('fiscal.')->group(function () {
                Route::get('/',                                    [FiscalPeriodController::class, 'index'])->name('index');
                Route::post('/',                                   [FiscalPeriodController::class, 'store'])->name('store');
                Route::post('/years/{fiscalYear}/close',           [FiscalPeriodController::class, 'closeYear'])->name('year.close');
                Route::get('/year-end',                            [YearEndClosingController::class, 'wizard'])->name('year-end');
                Route::post('/year-end/execute',                   [YearEndClosingController::class, 'execute'])->name('year-end.execute');
                Route::post('/years/{fiscalYear}/set-current',     [FiscalPeriodController::class, 'setCurrent'])->name('year.set-current');
                Route::post('/periods/{period}/close',             [FiscalPeriodController::class, 'closePeriod'])->name('period.close');
            });

            // ── الترحيلات الفاشلة (Posting Failures) ──
            Route::prefix('posting-failures')->name('posting-failures.')->group(function () {
                Route::get('/',                  [PostingFailureController::class, 'index'])->name('index');
                Route::post('/{failure}/retry',  [PostingFailureController::class, 'retry'])->name('retry');
                Route::post('/{failure}/resolve',[PostingFailureController::class, 'resolve'])->name('resolve');
            });

            // ── الأصول الثابتة (Fixed Assets) ──
            Route::prefix('fixed-assets')->name('fixed-assets.')->group(function () {
                Route::get('/',              [FixedAssetController::class, 'index'])->name('index');
                Route::get('/create',        [FixedAssetController::class, 'create'])->name('create');
                Route::post('/',             [FixedAssetController::class, 'store'])->name('store');
                Route::get('/depreciate',    [FixedAssetController::class, 'depreciateForm'])->name('depreciate.form');
                Route::post('/depreciate',   [FixedAssetController::class, 'runDepreciation'])->name('depreciate.run');
                Route::get('/{fixedAsset}',  [FixedAssetController::class, 'show'])->name('show');
                Route::post('/{fixedAsset}/dispose', [FixedAssetController::class, 'dispose'])->name('dispose');
            });

            // ── الإعدادات المحاسبية ──
            Route::get('/settings',  [AccountingSettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings',  [AccountingSettingsController::class, 'update'])->name('settings.update');
        });

});
