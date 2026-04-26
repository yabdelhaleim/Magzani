<?php


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PriceUpdateController;
use App\Http\Controllers\PurchaseReturnController;

use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManufacturingCostController;
use App\Http\Controllers\ManufacturingOrderController;


/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Dashboard (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth', 'role');

/*
|--------------------------------------------------------------------------
| Warehouses (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('warehouses')->name('warehouses.')->middleware('auth', 'role')->group(function () {
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
| Transfers (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::prefix('transfers')->name('transfers.')->middleware('auth')->group(function () {
    Route::get('/', [TransferController::class, 'index'])->name('index');
    Route::get('/create', [TransferController::class, 'create'])->name('create');
    Route::post('/', [TransferController::class, 'store'])->name('store');
    Route::get('/pending', [TransferController::class, 'pending'])->name('pending');
    Route::get('/{transfer}', [TransferController::class, 'show'])->name('show');
    Route::post('/{transfer}/reverse', [TransferController::class, 'reverse'])->name('reverse');
    Route::post('/{transfer}/cancel', [TransferController::class, 'cancel'])->name('cancel');
    Route::get('/warehouse/{warehouse}/history', [TransferController::class, 'warehouseHistory'])->name('warehouse.history');
});

/*
|--------------------------------------------------------------------------
| Warehouse Orders (Inbound & Outbound) - Admin Only
|--------------------------------------------------------------------------
*/
Route::prefix('warehouse-orders')->name('warehouse-orders.')->middleware('auth', 'role')->group(function () {
    // Inbound Orders
    Route::get('/inbound', [App\Http\Controllers\WarehouseOrderController::class, 'inboundIndex'])->name('inbound.index');
    Route::get('/inbound/create', [App\Http\Controllers\WarehouseOrderController::class, 'inboundCreate'])->name('inbound.create');
    Route::post('/inbound', [App\Http\Controllers\WarehouseOrderController::class, 'inboundStore'])->name('inbound.store');
    Route::get('/inbound/{order}', [App\Http\Controllers\WarehouseOrderController::class, 'inboundShow'])->name('inbound.show');
    Route::get('/inbound/{order}/print', [App\Http\Controllers\WarehouseOrderController::class, 'inboundPrint'])->name('inbound.print');

    // Outbound Orders
    Route::get('/outbound', [App\Http\Controllers\WarehouseOrderController::class, 'outboundIndex'])->name('outbound.index');
    Route::get('/outbound/create', [App\Http\Controllers\WarehouseOrderController::class, 'outboundCreate'])->name('outbound.create');
    Route::post('/outbound', [App\Http\Controllers\WarehouseOrderController::class, 'outboundStore'])->name('outbound.store');
    Route::get('/outbound/{order}', [App\Http\Controllers\WarehouseOrderController::class, 'outboundShow'])->name('outbound.show');
    Route::get('/outbound/{order}/print', [App\Http\Controllers\WarehouseOrderController::class, 'outboundPrint'])->name('outbound.print');
    Route::post('/outbound/{order}/approve', [App\Http\Controllers\WarehouseOrderController::class, 'outboundApprove'])->name('outbound.approve');
    Route::post('/outbound/{order}/cancel', [App\Http\Controllers\WarehouseOrderController::class, 'outboundCancel'])->name('outbound.cancel');
});

/*
|--------------------------------------------------------------------------
| Stock Counts (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('stock-counts')->name('stock-counts.')->middleware('auth', 'role')->group(function () {
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
| Inventory Movements (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::prefix('movements')->name('movements.')->middleware('auth')->group(function () {
    Route::get('/', [InventoryMovementController::class, 'index'])->name('index');
    Route::get('/product/{product}', [InventoryMovementController::class, 'productMovements'])->name('product');
    Route::get('/export', [InventoryMovementController::class, 'export'])->name('export');
});

/*
|--------------------------------------------------------------------------
| Products (Protected - Both Admin and Employee)
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

    // ✅ Admin-only routes MUST come BEFORE /{product} to avoid matching 'create' as an ID
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
| Manufacturing Cost Calculator (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('manufacturing')->name('manufacturing.')->middleware('auth', 'admin.only')->group(function () {
    Route::get('/', [ManufacturingCostController::class, 'index'])->name('index');
    Route::get('/create', [ManufacturingCostController::class, 'create'])->name('create');
    Route::post('/', [ManufacturingCostController::class, 'store'])->name('store');
    Route::post('/calculate', [ManufacturingCostController::class, 'calculateAjax'])->name('calculate');
    Route::get('/{manufacturingCost}', [ManufacturingCostController::class, 'show'])->name('show');
    Route::get('/{manufacturingCost}/edit', [ManufacturingCostController::class, 'edit'])->name('edit');
    Route::put('/{manufacturingCost}', [ManufacturingCostController::class, 'update'])->name('update');
    Route::delete('/{manufacturingCost}', [ManufacturingCostController::class, 'destroy'])->name('destroy');
    Route::post('/{manufacturingCost}/confirm', [ManufacturingCostController::class, 'confirm'])->name('confirm');
});

/*
|--------------------------------------------------------------------------
| Manufacturing Orders (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('manufacturing-orders')->name('manufacturing-orders.')->middleware('auth', 'admin.only')->group(function () {
    Route::get('/', [ManufacturingOrderController::class, 'index'])->name('index');
    Route::get('/create', [ManufacturingOrderController::class, 'create'])->name('create');
    Route::post('/', [ManufacturingOrderController::class, 'store'])->name('store');
    Route::post('/calculate', [ManufacturingOrderController::class, 'calculateCosts'])->name('calculate');
    Route::get('/{manufacturingOrder}', [ManufacturingOrderController::class, 'show'])->name('show');
    Route::get('/{manufacturingOrder}/edit', [ManufacturingOrderController::class, 'edit'])->name('edit');
    Route::put('/{manufacturingOrder}', [ManufacturingOrderController::class, 'update'])->name('update');
    Route::delete('/{manufacturingOrder}', [ManufacturingOrderController::class, 'destroy'])->name('destroy');
    Route::post('/{manufacturingOrder}/confirm', [ManufacturingOrderController::class, 'confirm'])->name('confirm');
    Route::post('/{manufacturingOrder}/complete', [ManufacturingOrderController::class, 'complete'])->name('complete');
    Route::post('/{manufacturingOrder}/cancel', [ManufacturingOrderController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| Invoices - Sales (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::prefix('invoices/sales')->name('invoices.sales.')->controller(SalesController::class)->middleware('auth')->group(function () {
    Route::get('/', 'index')->name('index');

    // Admin-only creation/editing routes should come BEFORE show/{id} to avoid matching 'create' as ID
    Route::middleware('admin.only')->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::get('/{id}', 'show')->name('show');
});

/*
|--------------------------------------------------------------------------
| Invoices - Purchases (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::prefix('invoices/purchases')->name('invoices.purchases.')->middleware('auth')->group(function () {
    Route::get('/', [PurchaseInvoiceController::class, 'index'])->name('index');
    Route::get('/create', [PurchaseInvoiceController::class, 'create'])->name('create');
    Route::post('/', [PurchaseInvoiceController::class, 'store'])->name('store');
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
| Invoices - Sales Returns (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::prefix('invoices/sales-returns')->name('invoices.sales-returns.')->middleware('auth')->group(function () {
    Route::get('/', [SalesReturnsController::class, 'index'])->name('index');
    Route::get('/create', [SalesReturnsController::class, 'create'])->name('create');
    Route::post('/', [SalesReturnsController::class, 'store'])->name('store');
    Route::get('/{salesReturn}', [SalesReturnsController::class, 'show'])->name('show');
    Route::delete('/{salesReturn}', [SalesReturnsController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Purchase Returns (Protected - Both Admin and Employee)
|--------------------------------------------------------------------------
*/
Route::prefix('invoices/purchase-returns')->name('invoices.purchase-returns.')->middleware('auth')->group(function () {
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
| Customers (Protected - View for all, Modify for Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('customers')->name('customers.')->middleware('auth')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/export', [CustomerController::class, 'export'])->name('export');

    // Admin-only routes MUST come BEFORE show/{customer} to avoid matching 'create' as ID
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
| Suppliers (Protected - View for all, Modify for Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('suppliers')->name('suppliers.')->middleware('auth')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
    Route::get('/export', [SupplierController::class, 'export'])->name('export');

    // Admin-only routes MUST come BEFORE show/{supplier} to avoid matching 'create' as ID
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
| Accounting (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('accounting')->name('accounting.')->middleware('auth', 'admin.only')->group(function () {
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

/*
|--------------------------------------------------------------------------
| Reports (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('reports')->name('reports.')->middleware('auth', 'admin.only')->group(function () {
    Route::get('/financial', [ReportingController::class, 'financial'])->name('financial');
    Route::get('/financial/export', [ReportingController::class, 'exportFinancial'])->name('financial.export');
    Route::get('/inventory', [ReportingController::class, 'inventory'])->name('inventory');
    Route::get('/inventory/export', [ReportingController::class, 'exportInventory'])->name('inventory.export');
    Route::get('/profit-loss', [ReportingController::class, 'profitLoss'])->name('profit-loss');
    Route::get('/profit-loss/export', [ReportingController::class, 'exportProfitLoss'])->name('profit-loss.export');
});

/*
|--------------------------------------------------------------------------
| Settings (Admin Only)
|--------------------------------------------------------------------------
*/
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('auth', 'admin.only');
Route::post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update')->middleware('auth', 'admin.only');
Route::post('/settings/logo/delete', [SettingsController::class, 'deleteLogo'])->name('settings.logo.delete')->middleware('auth', 'admin.only');
Route::post('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update')->middleware('auth', 'admin.only');

/*
|--------------------------------------------------------------------------
| User Management (Admin Only)
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
| Permissions Management (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix('permissions')->name('permissions.')->middleware('auth', 'admin.only')->group(function () {
    Route::get('/', [App\Http\Controllers\PermissionsController::class, 'index'])->name('index');
    Route::get('/users/{user}/edit', [App\Http\Controllers\PermissionsController::class, 'editUser'])->name('edit-user');
    Route::put('/users/{user}', [App\Http\Controllers\PermissionsController::class, 'updateUser'])->name('update-user');
    Route::get('/roles', [App\Http\Controllers\PermissionsController::class, 'roles'])->name('roles');
    Route::post('/roles', [App\Http\Controllers\PermissionsController::class, 'storeRole'])->name('store-role');
    Route::put('/roles/{role}', [App\Http\Controllers\PermissionsController::class, 'updateRole'])->name('update-role');
    Route::delete('/roles/{role}', [App\Http\Controllers\PermissionsController::class, 'destroyRole'])->name('destroy-role');
    Route::put('/roles/{role}/permissions', [App\Http\Controllers\PermissionsController::class, 'updateRolePermissions'])->name('update-role-permissions');
    Route::get('/print', [App\Http\Controllers\PermissionsController::class, 'printReport'])->name('print');
});
