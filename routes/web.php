<?php


use App\Http\Controllers\PriceUpdateController;
use App\Http\Controllers\PurchaseReturnController;

use App\Http\Controllers\PurchaseInvoiceController;
// use App\Http\Controllers\PurchaseReturnController;
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
// use App\Http\Controllers\SalesController;
use App\Http\Controllers\PurchasesController;
// use App\Http\Controllers\SalesReturnsController;
// use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ReportController;
// use App\Http\Controllers\SettingController;
// use App\Http\Controllers\TransferController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\InventoryMovementController;





/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Warehouses
|--------------------------------------------------------------------------
*/

// ==================== Warehouses Routes ====================
Route::prefix('warehouses')->name('warehouses.')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('index');
    Route::get('/create', [WarehouseController::class, 'create'])->name('create');
    Route::post('/', [WarehouseController::class, 'store'])->name('store');
    
    // Dynamic routes
    Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
    Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
    Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
    Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
    
    // Products management
    Route::get('/{warehouse}/add-product', [WarehouseController::class, 'createProduct'])->name('add-product');
    Route::post('/{warehouse}/products', [WarehouseController::class, 'addProduct'])->name('products.store');
    
    // Reports
    Route::get('/{warehouse}/low-stock', [WarehouseController::class, 'lowStock'])->name('low-stock');
    Route::get('/{warehouse}/movements', [WarehouseController::class, 'movements'])->name('movements');
    
    // Search
    Route::get('/{warehouse}/search', [WarehouseController::class, 'search'])->name('search');
});

// ==================== Transfers Routes ====================
Route::prefix('transfers')->name('transfers.')->group(function () {
    Route::get('/', [TransferController::class, 'index'])->name('index');
    Route::get('/create', [TransferController::class, 'create'])->name('create');
    Route::post('/', [TransferController::class, 'store'])->name('store');
    Route::get('/pending', [TransferController::class, 'pending'])->name('pending');
    
    Route::get('/{transfer}', [TransferController::class, 'show'])->name('show');
    Route::post('/{transfer}/reverse', [TransferController::class, 'reverse'])->name('reverse');
    Route::post('/{transfer}/cancel', [TransferController::class, 'cancel'])->name('cancel');
    
    // Warehouse history
    Route::get('/warehouse/{warehouse}/history', [TransferController::class, 'warehouseHistory'])->name('warehouse.history');
});

// ==================== Stock Counts Routes (الجرد) ====================
Route::prefix('stock-counts')->name('stock-counts.')->group(function () {
    Route::get('/', [StockCountController::class, 'index'])->name('index');
    Route::get('/create', [StockCountController::class, 'create'])->name('create');
    Route::post('/', [StockCountController::class, 'store'])->name('store');
    
    Route::get('/{stockCount}', [StockCountController::class, 'show'])->name('show');
    Route::post('/{stockCount}/start', [StockCountController::class, 'start'])->name('start');
    
    // ✅ إضافة الـ route ده
    Route::get('/{stockCount}/count', [StockCountController::class, 'count'])->name('count');
    
    Route::post('/{stockCount}/complete', [StockCountController::class, 'complete'])->name('complete');
    Route::post('/{stockCount}/cancel', [StockCountController::class, 'cancel'])->name('cancel');
    
    Route::post('/{stockCount}/items/{item}/approve', [StockCountController::class, 'approveItem'])
         ->name('items.approve');
    Route::post('/{stockCount}/approve-all', [StockCountController::class, 'approveAll'])
         ->name('approve-all');

    // Count item
    Route::post('/{stockCount}/items/{item}', [StockCountController::class, 'countItem'])->name('items.count');
    
    // AJAX
    Route::get('/warehouses/{warehouse}/products', [StockCountController::class, 'getWarehouseProducts'])
        ->name('warehouse-products');
    
    // Print
    Route::get('/{stockCount}/print', [StockCountController::class, 'print'])->name('print');
});

// ==================== Inventory Movements Routes ====================
Route::prefix('movements')->name('movements.')->group(function () {
    Route::get('/', [InventoryMovementController::class, 'index'])->name('index');
    Route::get('/product/{product}', [InventoryMovementController::class, 'productMovements'])->name('product');
    Route::get('/export', [InventoryMovementController::class, 'export'])->name('export');
});
    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
Route::prefix('products')->name('products.')->group(function () {
    
    // ==========================================
    // 📋 الصفحات الأساسية
    // ==========================================
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/create', [ProductController::class, 'create'])->name('create');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/barcode/print', [ProductController::class, 'barcode'])->name('barcode');
    
    // ==========================================
    // 🎯 التحديث الذكي للأسعار
    // ==========================================
    
    // صفحة التحديث الذكي الرئيسية
    Route::get('/bulk-price-update', [PriceUpdateController::class, 'bulkPriceUpdatePage'])
        ->name('bulk-price-update');
    
    // ==========================================
    // 🔌 AJAX Endpoints (في WEB - بدون api prefix)
    // ==========================================
    
    // جلب التصنيفات حسب الوحدة (AJAX)
    Route::get('/ajax/categories-by-unit', [PriceUpdateController::class, 'getCategoriesByUnit'])
        ->name('ajax.categories-by-unit');
    
    // جلب المنتجات حسب الوحدة + التصنيف (AJAX)
    Route::get('/ajax/by-unit-category', [PriceUpdateController::class, 'getProductsByUnitAndCategory'])
        ->name('ajax.by-unit-category');
    
    // معاينة التحديثات قبل التطبيق (AJAX - اختياري)
    Route::post('/ajax/preview-smart-update', [PriceUpdateController::class, 'previewSmartUpdate'])
        ->name('ajax.preview-smart-update');
    
    // تطبيق التحديث الذكي (POST)
    Route::post('/bulk-price-update/apply', [PriceUpdateController::class, 'applyBulkPriceUpdate'])
        ->name('bulk-price-update.apply');
    
    // ==========================================
    // 💡 الاقتراحات الذكية والمساعدات
    // ==========================================
    
    // اقتراحات الأسعار (AJAX)
    Route::get('/ajax/suggested-pricing', [ProductController::class, 'getSuggestedPricing'])
        ->name('ajax.suggested-pricing');
    
    // جلب المنتجات حسب الوحدة الأساسية (للنظام القديم - اختياري)
    Route::get('/bulk-price-update/get-products', [PriceUpdateController::class, 'getProductsByBaseUnit'])
        ->name('bulk-price-update.get-products');
    
    // معاينة التحديث الجماعي (للنظام القديم - اختياري)
    Route::post('/bulk-price-update/preview', [PriceUpdateController::class, 'previewBulkPriceUpdate'])
        ->name('bulk-price-update.preview');
    
    // ==========================================
    // 📊 إحصائيات ومعلومات إضافية
    // ==========================================
    
    // إحصائيات الوحدات
    Route::get('/units-statistics', [PriceUpdateController::class, 'unitsStatistics'])
        ->name('units-statistics');
    
    // تحويل سعر من وحدة لأخرى (AJAX)
    Route::get('/ajax/convert-unit-price', [PriceUpdateController::class, 'convertUnitPrice'])
        ->name('ajax.convert-unit-price');
    
    // معلومات وحدة معينة (AJAX)
    Route::get('/ajax/unit-details', [PriceUpdateController::class, 'getUnitDetails'])
        ->name('ajax.unit-details');
    
    // ==========================================
    // 💰 تحديث الأسعار الفردية
    // ==========================================
    
    // تحديث سعر منتج واحد (AJAX)
    Route::post('/{product}/update-price', [ProductController::class, 'updatePrice'])
        ->name('update-price');
    
    // تاريخ الأسعار لمنتج
    Route::get('/{product}/price-history', [ProductController::class, 'priceHistory'])
        ->name('price-history');
    
    // ==========================================
    // 📦 CRUD Routes (Dynamic - آخر شيء)
    // ==========================================
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
});


/*
|--------------------------------------------------------------------------
| Invoices - Sales
|--------------------------------------------------------------------------
*/
// use App\Http\Controllers\SalesController;

Route::prefix('invoices/sales')->name('invoices.sales.')->controller(SalesController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}', 'show')->name('show');
    Route::get('/{id}/edit', 'edit')->name('edit');
    Route::put('/{id}', 'update')->name('update');
    Route::delete('/{id}', 'destroy')->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Invoices - 
|--------------------------------------------------------------------------
*/

Route::prefix('invoices/purchases')->name('invoices.purchases.')->group(function () {
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

// |--------------------------------------------------------------------------
// | Sales Returns
// |--------------------------------------------------------------------------
// */
Route::prefix('invoices/sales-returns')->name('invoices.sales-returns.')->group(function () {
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
Route::prefix('invoices/purchase-returns')->name('invoices.purchase-returns.')->group(function () {
    // قائمة المرتجعات
    Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
    
    // إنشاء مرتجع جديد
    Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
    Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
    
    // عرض تفاصيل مرتجع
    Route::get('/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('show');
    
    // تعديل مرتجع
    Route::get('/{purchaseReturn}/edit', [PurchaseReturnController::class, 'edit'])->name('edit');
    Route::put('/{purchaseReturn}', [PurchaseReturnController::class, 'update'])->name('update');
    
    // حذف مرتجع
    Route::delete('/{purchaseReturn}', [PurchaseReturnController::class, 'destroy'])->name('destroy');
    
    // AJAX Route - الحصول على الأصناف المتاحة للإرجاع من فاتورة
    Route::get('/ajax/available-items/{invoice}', [PurchaseReturnController::class, 'getAvailableItems'])
        ->name('ajax.available-items');
});

/*
|--------------------------------------------------------------------------
| Customers
|--------------------------------------------------------------------------
*/
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
    Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    Route::get('/{customer}/statement', [CustomerController::class, 'statement'])->name('statement');
});

/*
|--------------------------------------------------------------------------
| Suppliers
|--------------------------------------------------------------------------
*/
Route::prefix('suppliers')->name('suppliers.')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
    Route::get('/create', [SupplierController::class, 'create'])->name('create');
    Route::post('/', [SupplierController::class, 'store'])->name('store');
    Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
    Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
    Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
    Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    Route::get('/{supplier}/statement', [SupplierController::class, 'statement'])->name('statement');
});

/*
|--------------------------------------------------------------------------
| Accounting
|--------------------------------------------------------------------------
*/

// Accounting Routes
Route::prefix('accounting')->name('accounting.')->group(function () {
    
    // Treasury
    Route::get('/treasury', [AccountingController::class, 'treasury'])->name('treasury');
    
    // Payments (Index)
    Route::get('/payments', [AccountingController::class, 'index'])->name('payments');
    
    // Deposits
    Route::post('/deposits', [AccountingController::class, 'storeDeposit'])->name('deposits.store');
    
    // Withdrawals
    Route::post('/withdrawals', [AccountingController::class, 'storeWithdrawal'])->name('withdrawals.store');
    
    // Transactions
    Route::put('/transactions/{id}', [AccountingController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{id}', [AccountingController::class, 'destroy'])->name('transactions.destroy');
    
    // Expenses
    Route::get('/expenses', [AccountingController::class, 'expenses'])->name('expenses.index');
    Route::post('/expenses', [AccountingController::class, 'storeExpense'])->name('expenses.store');
    Route::put('/expenses/{id}', [AccountingController::class, 'updateExpense'])->name('expenses.update');
    Route::delete('/expenses/{id}', [AccountingController::class, 'destroyExpense'])->name('expenses.destroy');
    
    // Statistics API
    Route::get('/statistics', [AccountingController::class, 'statistics'])->name('statistics');
});/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Reports Routes - 3 تقارير فقط
|--------------------------------------------------------------------------
*/
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/financial', [ReportingController::class, 'financial'])->name('financial');
    Route::get('/inventory', [ReportingController::class, 'inventory'])->name('inventory');
    Route::get('/profit-loss', [ReportingController::class, 'profitLoss'])->name('profit-loss');
});

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');



Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/inventory', [ReportingController::class, 'inventory'])->name('inventory');
    Route::get('/financial', [ReportingController::class, 'financial'])->name('financial');
    Route::get('/profit-loss', [ReportingController::class, 'profitLoss'])->name('profit-loss');
});




// Route::get('/login', function () {
//     return redirect()->route('');
// })->name('login');




// 'invoices/sales'
// Route::prefix('invoices/sales')->name('invoices.sales.')->group(function () {
//     Route::view('/', 'invoices.sales.index')->name('index');
//     Route::view('/create', 'invoices.sales.create')->name('create');
//     Route::view('/edit', 'invoices.sales.edit')->name('edit');
//     Route::view('/show', 'invoices.sales.show')->name('show');

//     // POST route تجريبي للفورم
//     Route::post('/store', function () {
//         return redirect()->back()->with('success', 'تم حفظ الفاتورة (تجريبي)');
//     })->name('store');
// });







































