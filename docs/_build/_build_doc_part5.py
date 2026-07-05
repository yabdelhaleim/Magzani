"""
Magzani ERP Doc - Part 4B
Detailed Modules: Warehouses/Transfers, POS/Shifts, Inventory/Stock Count,
                  Manufacturing, Accounting, Reporting, Settings, Activity Logs
"""
import sys
sys.path.insert(0, r"C:\MAGZANIV6\Magzani\docs")
from _build_doc_part4 import (
    doc, add_heading, add_para, add_para_rtl, add_table, add_code_block,
    add_hr, add_page_break, OUT_PATH, module_block,
)
from docx.shared import Pt, Cm, RGBColor

# ----- MODULE 8: WAREHOUSES & TRANSFERS -----
module_block(
    doc,
    'Module 8: Warehouses & Transfers',
    'المخازن والتحويلات',
    'Multi-warehouse support with product-warehouse pivot, draft→pending→in_transit→received transfer workflow, warehouse inbound/outbound orders.',
    [
        'app/Http/Controllers/WarehouseController.php (8 KB)',
        'app/Http/Controllers/TransferController.php (6.8 KB)',
        'app/Http/Controllers/WarehouseOrderController.php (14 KB)',
        'app/Services/WarehouseService.php (20 KB)',
        'app/Services/TransferService.php (25 KB)',
        'app/Services/WarehouseStockService.php (10 KB)',
        'app/Models/Warehouse.php / ProductWarehouse.php / WarehouseTransfer.php / WarehouseTransferItem.php / WarehouseInboundOrder.php / WarehouseInboundOrderItem.php / WarehouseOutboundOrder.php / WarehouseOutboundOrderItem.php',
        'resources/views/warehouses/{index,create,edit,show,add-product,movements,search,store,destroy, partials/}.blade.php',
        'resources/views/transfers/{index,create,pending,show,warehouse-history,transfers/}.blade.php',
    ],
    [
        ['Warehouse', 'id, name, location, manager, is_active, soft_deletes'],
        ['ProductWarehouse', 'product_id, warehouse_id, quantity, reserved_quantity, available_quantity, min_stock, average_cost, last_count_quantity, adjustment_total'],
        ['WarehouseTransfer', 'transfer_number, from_warehouse_id, to_warehouse_id, status (draft/pending/in_transit/received/cancelled/reversed), transfer_date, expected_arrival, notes'],
        ['WarehouseInboundOrder', 'order_number, warehouse_id, supplier_id, status, expected_date, received_date'],
        ['WarehouseOutboundOrder', 'order_number, warehouse_id, customer_id, status, expected_date, shipped_date'],
    ],
    [
        'Routes (feature:warehouses + auth + role, lines 236-249): full CRUD /warehouses/*, POST /warehouses/{id}/add-product, GET /warehouses/low-stock, /warehouses/{id}/movements, /warehouses/search.',
        'Routes (feature:multi_warehouse + auth, lines 183-194): /transfers/* — index, create, store, pending, show, reverse, cancel, warehouse/{id}/history.',
        'Routes (feature:warehouses + auth, lines 256-274): /warehouse-orders/stock-preview, /warehouse-orders/inbound/* (index/create/store/show/print), /warehouse-orders/outbound/* (index/create/store/show/print/approve/cancel).',
        'Transfer status state machine: draft → pending → in_transit → received (or cancelled / reversed at any step).',
        'Note: routes/orders inside warehouse-inbound and warehouse-outbound have print/approve/cancel endpoints (see Part 5).',
    ],
    url_prefix='/warehouses/*, /transfers/*, /warehouse-orders/*',
)

# ----- MODULE 9: POS & SHIFTS -----
module_block(
    doc,
    'Module 9: Point of Sale & Shifts',
    'نقطة البيع والورديات',
    'Livewire-based POS with shift lifecycle (open → in-progress → close), cash reconciliation, X/Z reports, returns panel.',
    [
        'app/Livewire/PosPanel.php (29 KB — main POS screen component)',
        'app/Livewire/PosReturnPanel.php (5 KB — POS returns)',
        'app/Http/Controllers/PosShiftController.php (11 KB)',
        'app/Http/Controllers/PosSettingController.php',
        'app/Models/PosShift.php / PosSetting.php',
        'resources/views/livewire/pos-panel.blade.php (50 KB — POS screen)',
        'resources/views/livewire/pos-return-panel.blade.php (11 KB)',
        'resources/views/pos/{history,settings,shift-close,shift-open,x-report,z-report}.blade.php',
    ],
    [
        ['PosShift', 'user_id, opened_at, closed_at, opening_balance, closing_balance_actual/expected/difference, total_sales, total_returns, sales_count, returns_count, net_sales, expected_cash, actual_cash, cash_difference, status (STATUS_OPEN|STATUS_CLOSED|STATUS_AUTO_CLOSED), notes, journal_entry_id'],
        ['PosSetting', 'tenant-specific POS settings (receipt header, footer, tax display, etc.)'],
    ],
    [
        'PosShift constants (lines 35-37): STATUS_OPEN, STATUS_CLOSED, STATUS_AUTO_CLOSED.',
        'Methods: getActiveShift(), autoCloseStaleShays(), calculateExpectedBalance(), recalculateTotals(), computeAndSaveDifference(), duration accessor.',
        'Routes (feature:pos + auth, lines 92-120): GET /pos → PosPanel, GET /pos/returns → PosReturnPanel, GET/POST /pos/settings (admin.only), GET/POST /pos/shift/open, GET/POST /pos/shift/close, GET /pos/history, GET /pos/x-report, GET /pos/shift/{id}/z-report. Sales invoice CRUD also feature:pos + admin-only writes.',
        'Opening flow: verify no active shift → auto-close stale shifts → create new shift with opening_balance → redirect to /pos.',
        'During sale: SalesInvoice records shift_id + source=pos + payment_method. PosShift::recalculateTotals() aggregates confirmed sales.',
        'Closing flow: calc expected_cash = opening_balance + cash_sales → user inputs counted cash → save cash_difference = actual - expected → set STATUS_CLOSED.',
        'X-Report = mid-shift snapshot; Z-Report = end-of-day reset-style report per shift.',
    ],
    url_prefix='/pos/*, /invoices/sales/* (POS-flavoured)',
)

# ----- MODULE 10: INVENTORY & STOCK COUNT -----
module_block(
    doc,
    'Module 10: Inventory, Stock Count & Movements',
    'المخزون والجرد وحركات المخزون',
    'Inventory movements ledger, full-cycle stock counts (draft→started→completed), stock adjustments, wood stock & dispensing.',
    [
        'app/Http/Controllers/InventoryMovementController.php (3.5 KB)',
        'app/Http/Controllers/StockCountController.php (7.8 KB)',
        'app/Http/Controllers/WoodStockController.php',
        'app/Http/Controllers/WoodDispensingController.php',
        'app/Services/InventoryMovementService.php (17 KB)',
        'app/Services/StockCountService.php (20 KB)',
        'app/Services/StockService.php (11 KB)',
        'app/Services/WoodCalculationService.php',
        'app/Services/WoodStockService.php',
        'app/Models/InventoryMovement.php / StockCount.php / StockCountItem.php / StockAdjustment.php / WoodStock.php / WoodDispensing.php',
        'resources/views/stock-counts/, Movements/index.blade.php, manufacturing/wood-stocks/, manufacturing/wood-dispensings/',
    ],
    [
        ['InventoryMovement', 'product_id, warehouse_id, type (initial_stock/purchase/sale/transfer/adjustment/return/production), quantity, unit_cost, total_cost, reference_type, reference_id, notes, created_by (nullable), movement_date'],
        ['StockCount', 'count_number, warehouse_id, count_date, status, started_at, completed_at, cancelled_at, notes, created_by, approved_by'],
        ['WoodStock', 'wood_type, quantity, unit, warehouse_id, product_id'],
        ['WoodDispensing', 'wood_stock_id, quantity, dispensed_to, dispensing_date, invoice_id, notes, created_by'],
    ],
    [
        'InventoryMovement types: initial_stock, purchase, sale, transfer, adjustment, return, production.',
        'StockCount statuses (transfers): draft, pending, in_transit, received, cancelled, reversed. Stock counts: start → count → complete workflow with per-item approve / approve-all.',
        'Routes (feature:stock_count + feature:warehouses + role, lines 281-295): /stock-counts/* (index, create, store, show, start, count, complete, cancel, items.approve, items.count, approve-all, warehouse-products, print).',
        'Routes (lines 302-306): /movements/* (index, product/{id}, export).',
    ],
    url_prefix='/stock-counts/*, /movements/*, /manufacturing/wood-stocks/*, /manufacturing/wood-dispensings/*',
)

# ----- MODULE 11: MANUFACTURING -----
module_block(
    doc,
    'Module 11: Manufacturing Orders & BOM',
    'التصنيع وأوامر الإنتاج',
    'BOM-based production orders (draft → confirmed → completed / cancelled). Auto-updates finished product cost + selling price on completion; records wood consumption.',
    [
        'app/Http/Controllers/ManufacturingOrderController.php (25 KB)',
        'app/Http/Controllers/ManufacturingCostController.php',
        'app/Http/Controllers/RawMaterialTemplateController.php (4.5 KB)',
        'app/Services/ManufacturingOrderService.php (25 KB)',
        'app/Services/ManufacturingCostService.php (10 KB)',
        'app/Services/RawMaterialTemplateInventoryService.php (4 KB)',
        'app/Models/ManufacturingOrder.php / ManufacturingOrderComponent.php / ManufacturingCost.php / BomComponent.php / RawMaterialTemplate.php',
        'resources/views/manufacturing/{create,edit,index,show}.blade.php',
        'resources/views/manufacturing-orders/{create,edit,index,show,raw-materials/}.blade.php',
    ],
    [
        ['ManufacturingOrder', 'order_number (auto MO-YYYY-NNNN), product_id (nullable FK), product_name, quantity_produced, cost_per_unit, total_cost, selling_price_per_unit, status (draft/confirmed/completed/cancelled), produced_at, notes, warehouse_id, created_by, updated_by, completed_by'],
        ['ManufacturingOrderComponent', 'order_id, component_name, quantity, unit, unit_cost, total_cost, wood_stock_id, component_type (varchar), specs'],
        ['ManufacturingCost', 'product_id, labor_cost, material_cost, overhead_cost, total_cost, cost_per_unit, profit_margin, selling_price, notes'],
    ],
    [
        'Statuses: draft → confirmed → completed / cancelled.',
        'Routes (feature:manufacturing + auth + admin.only, lines 128-176):',
        '  /manufacturing/* — calculator: index, create, store, calculate, show, edit, update, destroy, confirm + wood-stocks/wood-dispensings sub-routes.',
        '  /manufacturing-orders/* — orders: index, create, store, calculate, show, edit, update, destroy, confirm/complete/cancel (POST or PATCH), raw-materials sub-resource.',
        'Completion flow (atomic DB transaction in ManufacturingOrderService):',
        '  1. Find target product (FK).',
        '  2. Update product purchase_price = cost_per_unit, selling_price = selling_price_per_unit.',
        '  3. Inventory movement: type=production, qty = +quantity_produced.',
        '  4. Status = completed, produced_at = now().',
        'Order numbers auto-generated as MO-YYYY-NNNN.',
        'Manufacturing cost calculator (BOM-based pricing) lets you build per-product cost + suggested selling price.',
        'Wood consumption: manufacturing_order_components.wood_stock_id links to wood_stocks.',
    ],
    url_prefix='/manufacturing/*, /manufacturing-orders/*',
)

# ----- MODULE 12: ACCOUNTING -----
module_block(
    doc,
    'Module 12: Accounting (Chart of Accounts, Journals, Reports)',
    'المحاسبة (شجرة الحسابات، القيود، التقارير)',
    'Full double-entry accounting: COA, Journal Entries (manual + system-posted), Receipt & Payment Vouchers, Fiscal Years/Periods, Fixed Assets, Posting Failures, Partner Ledger.',
    [
        'app/Http/Controllers/Accounting/AccountingDashboardController.php',
        'app/Http/Controllers/Accounting/AccountingSettingsController.php',
        'app/Http/Controllers/Accounting/AccountingSetupController.php (onboarding wizard)',
        'app/Http/Controllers/Accounting/ChartOfAccountsController.php',
        'app/Http/Controllers/Accounting/FinancialReportController.php',
        'app/Http/Controllers/Accounting/FiscalPeriodController.php',
        'app/Http/Controllers/Accounting/FixedAssetController.php',
        'app/Http/Controllers/Accounting/JournalEntryController.php',
        'app/Http/Controllers/Accounting/PaymentVoucherController.php',
        'app/Http/Controllers/Accounting/PostingFailureController.php',
        'app/Http/Controllers/Accounting/ReceiptVoucherController.php',
        'app/Http/Controllers/Accounting/RecurringJournalEntryController.php',
        'app/Http/Controllers/Accounting/YearEndClosingController.php',
        'app/Services/Accounting/* (13 services — PostingService is 43 KB, FinancialReportService 26 KB)',
        'app/Providers/AccountingServiceProvider.php (registered in app()->booted)',
        'app/Enums/AccountTypeEnum.php, JournalEntrySource.php, JournalEntryStatus.php, NormalBalance.php, PaymentTerms.php',
        'app/Models/{Account, AccountType, AccountBalance, JournalEntry, JournalEntryLine, RecurringJournalEntry*, FiscalYear, FiscalPeriod, AccountingSetting, AccountingAuditLog, AccountingPostingFailure, BankAccount, BankReconciliation, CostCenter, FixedAsset, FixedAssetDepreciation}.php',
        'database/seeders/DefaultChartOfAccountsSeeder.php (12 KB full COA)',
        'resources/views/accounting/{coa,dashboard,expenses,fiscal,fixed-assets,journal,posting-failures,recurring,reports,settings,setup,treasury,vouchers}.blade.php',
    ],
    [
        ['Account', 'code (unique), name, type (asset/liability/equity/revenue/expense), parent_id, normal_balance, is_active'],
        ['JournalEntry', 'entry_number, entry_date, description, source (manual/system), status (draft/posted/reversed), fiscal_year_id, fiscal_period_id, reference_type, reference_id, total_debit, total_credit, posted_by/at, reversed_by/at, reversal_of'],
        ['JournalEntryLine', 'journal_entry_id, account_id, debit, credit, description, cost_center_id, partner_type, partner_id, line_order'],
        ['FiscalYear / FiscalPeriod', 'is_current, is_closed, closed_at, period_number'],
        ['FixedAsset', 'name, code, purchase_date, purchase_cost, salvage_value, useful_life, depreciation_method, accumulated_depreciation, book_value, status'],
    ],
    [
        'Routes (feature:accounting_advanced + admin.only, lines 496-617):',
        '  /accounting — dashboard + integrity-check + fix.',
        '  /accounting/setup/* — multi-step onboarding wizard.',
        '  /accounting/coa/* — CRUD + export.',
        '  /accounting/journal/* — CRUD + post + reverse (throttled 30,1).',
        '  /accounting/vouchers/{receipt,payment}/*.',
        '  /accounting/reports/* — trial-balance, income-statement, comparative-income, balance-sheet, general-ledger, partner-ledger, audit-trail, aging, financial-ratios, vat-settlement.',
        '  /accounting/recurring/* — recurring journal entries with run-now.',
        '  /accounting/fiscal/* — fiscal periods/years + year-end wizard.',
        '  /accounting/posting-failures/* — retry / resolve.',
        '  /accounting/fixed-assets/* — CRUD + depreciate + dispose.',
        '  /accounting/settings — index/update.',
        'PostingService (43 KB) auto-posts journal entries on sales confirmation, purchase confirmation, payment, refund, manufacturing completion, etc. — and logs failures to accounting_posting_failures for manual retry.',
        'AuthServiceProvider defines 19 accounting.* gates dynamically: accounting.dashboard, accounting.coa.*, accounting.journal.*, accounting.vouchers.*, accounting.reports.*, accounting.fiscal.*, accounting.settings.*, accounting.posting-failures.*.',
        'System-posted entries are linked to source transactions via reference_type + reference_id and cannot be edited directly (must be reversed first).',
        'Year-end closing wizard handles period/year locking and rollover.',
    ],
    url_prefix='/accounting/* (feature:accounting_advanced + admin)',
)

# ----- MODULE 13: REPORTING & DASHBOARD -----
module_block(
    doc,
    'Module 13: Reporting & Dashboard',
    'التقارير ولوحة التحكم',
    'Operational + financial + wood-specific reports, Excel exports, profit/loss, inventory, aging, statements.',
    [
        'app/Http/Controllers/ReportingController.php (7.8 KB)',
        'app/Http/Controllers/FinancialReportController.php',
        'app/Http/Controllers/AccountingDashboardController.php',
        'app/Http/Controllers/DashboardController.php',
        'app/Services/ReportingService.php (23 KB)',
        'app/Services/FinancialReportService.php',
        'app/Exports/{CustomersExport, SuppliersExport, InventoryReportExport, ProfitLossReportExport, FinancialReportExport, CustomerStatementExport, SupplierStatementExport}.php',
        'resources/views/reports/{financial,inventory,profit-loss,wood-stock,wood-movement,wood-cost-production}.blade.php',
        'resources/views/Dashboard/dashboard.blade.php',
    ],
    None,
    [
        'Reports operational (DOCUMENTATION.md §11):',
        '  • Sales by date / customer / product',
        '  • Purchases by date / supplier',
        '  • Inventory levels / value',
        '  • Profit & Loss (Gross + Net)',
        '  • Top products / top customers',
        '  • Low stock',
        '  • Debtors / Creditors (Aging)',
        '  • Expenses by category',
        '  • Daily sales detail',
        'Reports financial (in /accounting/reports/*):',
        '  • Trial Balance, Income Statement, Comparative Income, Balance Sheet',
        '  • General Ledger, Partner Ledger, Audit Trail',
        '  • Aging (AR/AP), Financial Ratios, VAT Settlement.',
        'Reports wood-specific: wood-stock, wood-movement, wood-cost-production.',
        'Routes (feature:accounting + admin, lines 216-228): /reports/{financial,inventory,profit-loss}/{export} and non-export versions; /reports/wood-stock, /reports/wood-movement, /reports/wood-cost-production.',
    ],
    url_prefix='/reports/* + /accounting/reports/*',
)

# ----- MODULE 14: SETTINGS -----
module_block(
    doc,
    'Module 14: Settings',
    'الإعدادات',
    'Tenant-level company profile, system settings, logo, accounting settings (admin-only).',
    [
        'app/Http/Controllers/SettingsController.php (4 KB)',
        'app/Http/Controllers/AccountingSettingsController.php',
        'app/Http/Controllers/AccountingSetupController.php (onboarding wizard)',
        'app/Services/SettingsService.php / ChartOfAccountsService.php / ExpenseService.php / AccountingAuditService.php',
        'app/Models/Company.php / SystemSetting.php / AccountingSetting.php / PosSetting.php',
        'resources/views/settings/*.blade.php (admin only)',
    ],
    [
        ['Company', 'name, logo, address, phone, email, tax_id, currency'],
        ['SystemSetting', 'key-value general settings'],
        ['AccountingSetting', 'key-value accounting settings'],
    ],
    [
        'Routes (auth + admin.only, lines 452-455): GET /settings, POST /settings/company, POST /settings/system, POST /settings/logo/delete.',
        'AppServiceProvider view composer reads tenant()->plan->features (or plan()->custom_features) on every view → exposes $planFeatures.',
    ],
    url_prefix='/settings/* (admin only)',
)

# ----- MODULE 15: ACTIVITY LOGS & NOTIFICATIONS -----
module_block(
    doc,
    'Module 15: Activity Logs & Notifications',
    'سجل النشاط والإشعارات',
    'Per-user activity tracking across invoices, transfers, payments; standard Laravel notifications table.',
    [
        'app/Models/ActivityLog.php',
        'app/Notifications/* (custom + Laravel default)',
        'database/migrations/tenant/2026_01_20_232916_create_notifications_table.php',
        'database/migrations/tenant/*_create_activity_logs*.php',
    ],
    None,
    [
        'User hasMany: salesInvoices, purchaseInvoices, salesReturns, purchaseReturns, warehouseTransfers, payments, expenses, cashTransactions, activityLogs.',
        'There are TWO activity_log tables: activity_logs (custom) AND Laravel default activity_log pattern from migrations. The "system_audit_logs" pattern is logged via Custom events.',
        'TODO from ERROR_FIXES_TODO.md: ensure single activity_log table per tenant.',
        'Notifications: standard Laravel mail / database / broadcast channels available via app()->booted services.',
    ],
    url_prefix='(no UI routes — used by Audit)',
)

doc.save(OUT_PATH)
print(f"Part 4B appended → {OUT_PATH}")
