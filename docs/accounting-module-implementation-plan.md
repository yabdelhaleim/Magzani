# خطة تنفيذ موديول المالية والحسابات — Magzani SaaS

> **الإصدار:** 2.1 (مُصحَّح بعد Senior QA + Code Verification)  
> **التاريخ:** 2026-07-03  
> **النطاق:** نظام محاسبة مزدوج القيد (Double-Entry GL) متعدد المستأجرين  
> **الحالة:** خطة تنفيذ — كل القيود مُثبَتة التوازن + كل الافتراضات مُتحقَّق منها مقابل الكود  
> **مرجع الإصلاحات:** `docs/accounting-fixes-roadmap.md`

> **ما تغيّر في v2.0 (تصحيحات محاسبية):**
> - القسم 7: +10 حسابات (4800 خصم، 4300/4400 شحن/رسوم، 1350 WIP، 3250 ملخص الدخل، 5295 تقريب، ...)
> - القسم 8: كل قواعد الترحيل أُعيد اشتقاقها ومُثبَتة التوازن — إصلاح الخصم/الشحن/COGS/الدفع الجزئي/التصنيع/مرتجع المشتريات/الإقفال
> - القسم 6: `account_balances` (materialized) + `accounting_posting_failures` + أعمدة إعدادات جديدة + ربط `journal_entry_id`
> - القسم 14: منع ازدواجية ترحيل البيانات القديمة (طريقة A/B حصرياً)

> **ما تغيّر في v2.1 (تحقق من الكود الفعلي):**
> - **المصروفات (قرار الخيار A):** تبقى في `cash_transactions` (نوع withdrawal) — أُلغيت migration 013 لجدول `expenses` غير المستخدم
> - **`PurchaseInvoiceConfirmed`:** غير مُسجَّل في `EventServiceProvider` — أُضيف للقسم 11 مع التحقق من الإطلاق
> - **`accounting_advanced`:** غير مُعرَّف في `PlanFeature`/`PlanSeeder` — أُضيف بالكامل في القسم 13 (كان سيحجب الميزة عن الجميع)
> - **أحداث التصنيع + `SupplierPaymentCreated`:** تُنشأ وتُطلَق من طبقة الـ **Service** (القسم 11.1b)
> - **`RecordInAccountingLedger`:** موجود لكن dead code — يُستبدل بـ `PostPaymentToLedger` (لا يُعاد استخدامه)
> - **🔴 اكتشاف حرج:** `SalesInvoiceConfirmed`/`PurchaseInvoiceConfirmed` **لا يُطلَقان أبداً** في `InvoiceService` — Phase 3 يضيف استدعاءات `event()` (القسم 11.1) وإلا لا يعمل الترحيل التلقائي

---

## جدول المحتويات

1. [الملخص التنفيذي](#1-الملخص-التنفيذي)
2. [المبادئ المحاسبية الحاكمة](#2-المبادئ-المحاسبية-الحاكمة)
3. [تدقيق الوضع الحالي](#3-تدقيق-الوضع-الحالي)
4. [الهدف والنطاق](#4-الهدف-والنطاق)
5. [المعمارية التقنية](#5-المعمارية-التقنية)
6. [تصميم قاعدة البيانات](#6-تصميم-قاعدة-البيانات)
7. [دليل الحسابات الافتراضي](#7-دليل-الحسابات-الافتراضي)
8. [قواعد الترحيل المحاسبي](#8-قواعد-الترحيل-المحاسبي)
9. [الصفحات والواجهات](#9-الصفحات-والواجهات)
10. [طبقة الخدمات (Services)](#10-طبقة-الخدمات-services)
11. [التكامل مع الموديولات الحالية](#11-التكامل-مع-الموديولات-الحالية)
12. [التقارير المالية](#12-التقارير-المالية)
13. [SaaS: الباقات والصلاحيات](#13-saas-الباقات-والصلاحيات)
14. [ترحيل البيانات القديمة](#14-ترحيل-البيانات-القديمة)
15. [ضمانات سلامة محاسبية](#15-ضمانات-سلامة-محاسبية)
16. [خطة التنفيذ على مراحل](#16-خطة-التنفيذ-على-مراحل)
17. [اختبارات التحقق المحاسبي](#17-اختبارات-التحقق-المحاسبي)
18. [مخاطر ومعالجاتها](#18-مخاطر-ومعالجاتها)
19. [ملحق: قائمة الملفات](#19-ملحق-قائمة-الملفات)

---

## 1. الملخص التنفيذي

### الهدف
بناء **نظام محاسبة مزدوج القيد كامل** داخل Magzani ERP SaaS، يعمل لكل مستأجر (tenant) في قاعدة بياناته المنفصلة، مع ترحيل تلقائي من المبيعات والمشتريات والخزينة والمصروفات، وتقارير مالية معتمدة محاسبياً.

### القرارات المعمارية الأساسية

| القرار | الاختيار | السبب |
|--------|----------|-------|
| نوع المحاسبة | قيد مزدوج كامل (GL) | دقة تقارير، قابلية تدقيق، معيار ERP |
| مكان البيانات | Tenant DB فقط | عزل بيانات SaaS |
| الترحيل | تلقائي عند الاعتماد + يدوي للقيود الخاصة | تقليل أخطاء بشرية |
| أساس الاعتراف | استحقاق (Accrual) | فواتير = إيراد/مصروف عند التأكيد |
| المخزون | جرد مستمر (Perpetual) | متوافق مع `inventory_movements` الموجود |
| تعديل القيود المعتمدة | عكس قيد (Reversal) فقط — لا حذف | سلامة تدقيق |
| الضريبة | حسابات منفصلة VAT Input/Output | امتثال ضريبي |

### ما لن نفعله (خارج النطاق — المرحلة الأولى)
- محاسبة تكاليف متقدمة ABC
- دمج مع أنظمة بنكية API
- محاسبة متعددة عملات (Phase 5)
- فواتير إلكترونية حكومية (e-invoicing)

---

## 2. المبادئ المحاسبية الحاكمة

### 2.1 قواعد لا استثناء فيها

```
RULE-01: كل قيد يومي → مجموع المدين = مجموع الدائن (بالهللتين)
RULE-02: القيد المعتمد (posted) → غير قابل للتعديل أو الحذف
RULE-03: تصحيح قيد معتمد → قيد عكسي جديد مرتبط بالأصل
RULE-04: لا ترحيل مزدوج لنفس المصدر (idempotency)
RULE-05: الفترة المغلقة → رفض أي قيد بتاريخ داخلها
RULE-06: الحسابات الورقية (leaf) فقط تستقبل قيود — لا قيود على حساب أب
RULE-07: رصيد AR/AP الفرعي = رصيد حساب GL المرتبط (reconciliation)
RULE-08: COGS يُحسب عند البيع — المشتريات تذهب للمخزون لا للمصروفات
RULE-09: كل عملية مالية لها audit trail (من، متى، ماذا)
RULE-10: السالب في الحسابات يتبع طبيعة الحساب (normal balance)
```

### 2.2 طبيعة الأرصدة (Normal Balance)

| نوع الحساب | طبيعة الرصيد | يزيد بـ | ينقص بـ |
|------------|-------------|---------|---------|
| أصول (Asset) | مدين | مدين | دائن |
| خصوم (Liability) | دائن | دائن | مدين |
| حقوق ملكية (Equity) | دائن | دائن | مدين |
| إيرادات (Revenue) | دائن | دائن | مدين |
| مصروفات (Expense) | مدين | مدين | دائن |

### 2.3 معادلة المحاسبة

```
الأصول = الخصوم + حقوق الملكية
صافي الربح = الإيرادات − المصروفات
حقوق الملكية = رأس المال + الأرباح المحتجزة + صافي الربح − المسحوبات
```

### 2.4 الفرق بين المحاسبة التشغيلية والمحاسبة العامة

| العملية | Sub-ledger (تشغيلي) | GL (عام) |
|---------|---------------------|----------|
| فاتورة مبيعات | `sales_invoices` | قيد: AR/Revenue/VAT |
| دفعة عميل | `payments` | قيد: Cash/AR |
| فاتورة مشتريات | `purchase_invoices` | قيد: Inventory/AP/VAT |
| سحب خزينة | `cash_transactions` | قيد: Expense/Cash |
| حركة مخزون | `inventory_movements` | قيد: COGS/Inventory |

**القاعدة:** Sub-ledger مصدر الحقيقة التشغيلية، GL مصدر الحقيقة المالية. يجب أن يتطابقا دائماً.

---

## 3. تدقيق الوضع الحالي

### 3.1 ما هو موجود ويعمل

| المكون | المسار | الحالة |
|--------|--------|--------|
| الخزينة | `AccountingController::treasury` | ✅ يعمل |
| المصروفات | `AccountingController::expenses` | ✅ جزئي |
| المدفوعات | `AccountingController::payments` | ✅ يعمل |
| تقرير P&L | `ReportingService::profitLossReport` | ⚠️ تقريبي |
| كشف حساب عميل | `CustomerController::statement` | ✅ بدون GL |
| Feature gating | `feature:accounting` | ✅ مفعّل |
| صلاحيات | `accounting.*` في Seeder | ⚠️ غير مربوطة |

### 3.2 مشاكل محاسبية في الكود الحالي (يجب إصلاحها)

| # | المشكلة | التأثير المحاسبي | الحل |
|---|---------|-----------------|------|
| 1 | P&L يحسب المشتريات كمصروف | تضخيم مصروفات، COGS خاطئ | فصل: مشتريات→مخزون، بيع→COGS |
| 2 | `getBankBalance()` = 0 دائماً | ميزانية عمومية خاطئة | `bank_accounts` + قيود |
| 3 | `RecordInAccountingLedger` موجود لكن **غير مسجّل** في `EventServiceProvider` (dead code) | لا ترحيل للمدفوعات | يُستبدل بـ `PostPaymentToLedger` الجديد (لا يُعاد استخدامه) |
| 4 | **المصروفات تُخزَّن في `cash_transactions` (نوع withdrawal)** — جدول `expenses` موجود لكن **غير مستخدم** في أي controller | التصميم الأصلي افترض جدول expenses | **قرار: الخيار A** — الاعتماد على `cash_transactions` وربط `journal_entry_id` بها فقط (بدون migration للـ expenses) |
| 5 | لا فترات مالية مغلقة | قيود بتواريخ قديمة | `fiscal_periods` + lock |
| 6 | `cash_transactions` بدون ربط GL | خزينة منفصلة عن الحسابات | `journal_entry_id` FK |
| 7 | مرتجعات المبيعات غير في P&L | إيرادات مبالغ فيها | قيود مرتجع |
| 8 | `SupplierPaymentController` بدون routes | AP غير مكتمل | تفعيل + ترحيل |
| 9 | **`PurchaseInvoiceConfirmed` غير مُسجَّل** في `EventServiceProvider` (Created/Cancelled فقط) | لا ترحيل تلقائي للمشتريات | إضافة المفتاح |
| 10 | **`accounting_advanced` غير مُعرَّف** في `PlanFeature` ولا `PlanSeeder` | `feature:accounting_advanced` سيحجب الميزة عن كل الباقات | إضافة الثابت + توزيعه على Pro/Enterprise/pxxx/custom |
| 11 | 🔴 **حرج: `SalesInvoiceConfirmed`/`PurchaseInvoiceConfirmed` لا يُطلَقان أبداً** — الفاتورة تُنشأ بـ `status='confirmed'` مباشرة في `InvoiceService` (سطر 459/1044) دون `event(new ...)` | **كل معمارية الترحيل التلقائي معطّلة** — لا listener سيُنفَّذ | Phase 3 يجب أن يضيف استدعاءات `event()` عند نقاط التأكيد في `InvoiceService` |

### 3.3 الأحداث الموجودة للتكامل

```
app/Events/Invoice/SalesInvoiceConfirmed.php      → ترحيل إيراد + AR/نقدية
app/Events/Invoice/PurchaseInvoiceConfirmed.php   → ترحيل مخزون + AP
app/Events/Invoice/SalesInvoiceCancelled.php      → قيد عكسي
app/Events/Invoice/PurchaseInvoiceCancelled.php   → قيد عكسي
app/Events/Payment/PaymentReceived.php            → ترحيل قبض
app/Events/Stock/StockUpdated.php                  → (لا ترحيل — المخزون عبر الفواتير)
```

---

## 4. الهدف والنطاق

### 4.1 الصفحات النهائية (22 صفحة)

#### إعدادات
- [ ] إعدادات المحاسبة
- [ ] دليل الحسابات (شجرة)
- [ ] السنوات والفترات المالية
- [ ] حسابات البنوك
- [ ] مراكز التكلفة (Phase 5)

#### عمليات يومية
- [ ] لوحة التحكم المالية
- [ ] القيود اليومية (قائمة + إنشاء + عرض)
- [ ] سند قبض
- [ ] سند صرف
- [ ] تحويل بين حسابات (خزينة ↔ بنك)
- [ ] الخزينة (تطوير الموجود)
- [ ] المصروفات (تطوير الموجود)
- [ ] مدفوعات الموردين (تفعيل الموجود)

#### ذمم
- [ ] كشف حساب عميل (تطوير)
- [ ] كشف حساب مورد (تطوير)
- [ ] أعمار الديون (Aging)

#### تقارير
- [ ] ميزان المراجعة
- [ ] قائمة الدخل
- [ ] الميزانية العمومية
- [ ] دفتر الأستاذ العام
- [ ] كشف حساب
- [ ] التدفقات النقدية
- [ ] مطابقة بنكية

---

## 5. المعمارية التقنية

### 5.1 هيكل المجلدات

```
app/
├── Http/
│   ├── Controllers/Accounting/
│   │   ├── AccountingDashboardController.php
│   │   ├── ChartOfAccountsController.php
│   │   ├── JournalEntryController.php
│   │   ├── ReceiptVoucherController.php
│   │   ├── PaymentVoucherController.php
│   │   ├── BankAccountController.php
│   │   ├── BankReconciliationController.php
│   │   ├── FiscalPeriodController.php
│   │   ├── AccountingSettingsController.php
│   │   └── CostCenterController.php          # Phase 5
│   └── Requests/Accounting/
│       ├── StoreAccountRequest.php
│       ├── UpdateAccountRequest.php
│       ├── StoreJournalEntryRequest.php
│       ├── PostJournalEntryRequest.php
│       ├── StoreReceiptVoucherRequest.php
│       ├── StorePaymentVoucherRequest.php
│       └── ReconcileBankRequest.php
│
├── Services/Accounting/
│   ├── ChartOfAccountsService.php
│   ├── JournalEntryService.php          # القلب — إنشاء/اعتماد/عكس
│   ├── PostingService.php               # ترحيل تلقائي من المصادر
│   ├── TreasuryService.php              # تطوير الخزينة
│   ├── AccountsReceivableService.php
│   ├── AccountsPayableService.php
│   ├── FinancialReportService.php
│   ├── BankReconciliationService.php
│   ├── FiscalPeriodService.php
│   ├── AccountingSettingsService.php
│   └── AccountBalanceService.php        # حساب الأرصدة بكفاءة
│
├── Models/
│   ├── Account.php
│   ├── AccountType.php
│   ├── JournalEntry.php
│   ├── JournalEntryLine.php
│   ├── FiscalYear.php
│   ├── FiscalPeriod.php
│   ├── BankAccount.php
│   ├── BankReconciliation.php
│   ├── BankReconciliationItem.php
│   ├── AccountingSetting.php
│   ├── CostCenter.php                     # Phase 5
│   └── AccountingAuditLog.php
│
├── Events/Accounting/
│   ├── JournalEntryPosted.php
│   ├── JournalEntryReversed.php
│   └── FiscalPeriodClosed.php
│
├── Listeners/Accounting/
│   ├── PostSalesInvoiceToLedger.php
│   ├── PostPurchaseInvoiceToLedger.php
│   ├── PostSalesReturnToLedger.php
│   ├── PostPurchaseReturnToLedger.php
│   ├── PostPaymentToLedger.php
│   ├── PostSupplierPaymentToLedger.php
│   ├── PostExpenseToLedger.php
│   ├── PostCashTransactionToLedger.php
│   ├── ReverseInvoiceJournalEntry.php
│   └── LogAccountingAudit.php
│
├── Policies/
│   ├── AccountPolicy.php
│   ├── JournalEntryPolicy.php
│   └── FiscalPeriodPolicy.php
│
├── Enums/
│   ├── AccountTypeEnum.php
│   ├── JournalEntryStatus.php
│   ├── JournalEntrySource.php
│   └── NormalBalance.php
│
└── Console/Commands/
    ├── AccountingSeedDefaultAccounts.php
    ├── AccountingMigrateLegacyData.php
    └── AccountingValidateIntegrity.php

database/
├── migrations/tenant/
│   ├── 2026_07_02_000001_create_account_types_table.php
│   ├── 2026_07_02_000002_create_accounts_table.php
│   ├── 2026_07_02_000003_create_fiscal_years_table.php
│   ├── 2026_07_02_000004_create_fiscal_periods_table.php
│   ├── 2026_07_02_000005_create_journal_entries_table.php
│   ├── 2026_07_02_000006_create_journal_entry_lines_table.php
│   ├── 2026_07_02_000007_create_bank_accounts_table.php
│   ├── 2026_07_02_000008_create_accounting_settings_table.php
│   ├── 2026_07_02_000009_create_accounting_audit_logs_table.php
│   ├── 2026_07_02_000010_create_bank_reconciliations_table.php
│   ├── 2026_07_02_000011_add_journal_entry_id_to_cash_transactions.php  # المصروفات هنا (نوع withdrawal)
│   ├── 2026_07_02_000012_add_journal_entry_id_to_payments.php
│   ├── # (أُلغيت) 013_add_journal_entry_id_to_expenses — جدول expenses غير مستخدم (قرار: الخيار A)
│   ├── 2026_07_02_000014_create_cost_centers_table.php
│   ├── 2026_07_02_000015_create_account_balances_table.php          # QA
│   ├── 2026_07_02_000016_create_accounting_posting_failures_table.php # QA
│   └── 2026_07_02_000017_add_journal_entry_id_to_invoices_returns.php # QA
│
└── seeders/
    ├── DefaultChartOfAccountsSeeder.php
    └── AccountingSettingsSeeder.php

resources/views/accounting/
├── dashboard.blade.php
├── settings/
│   └── index.blade.php
├── chart-of-accounts/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── fiscal-periods/
│   ├── index.blade.php
│   └── close.blade.php
├── journal-entries/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── print.blade.php
├── vouchers/
│   ├── receipt-create.blade.php
│   ├── payment-create.blade.php
│   └── transfer-create.blade.php
├── bank-accounts/
│   ├── index.blade.php
│   └── create.blade.php
├── bank-reconciliation/
│   ├── index.blade.php
│   └── reconcile.blade.php
├── reports/
│   ├── trial-balance.blade.php
│   ├── income-statement.blade.php
│   ├── balance-sheet.blade.php
│   ├── general-ledger.blade.php
│   ├── account-statement.blade.php
│   ├── cash-flow.blade.php
│   └── aging.blade.php
├── treasury.blade.php          # تطوير الموجود
├── expenses.blade.php          # تطوير الموجود
└── payments.blade.php          # تطوير الموجود
```

### 5.2 Routes (داخل `routes/tenant.php`)

```php
Route::middleware(['auth', 'feature:accounting'])->group(function () {

    // ── المحاسبة الأساسية (كل الباقات) ──
    Route::prefix('accounting')->name('accounting.')->middleware('admin.only')->group(function () {
        Route::get('/dashboard', [AccountingDashboardController::class, 'index'])->name('dashboard');
        Route::get('/treasury', [AccountingController::class, 'treasury'])->name('treasury');
        Route::get('/expenses', [AccountingController::class, 'expenses'])->name('expenses.index');
        Route::get('/payments', [AccountingController::class, 'index'])->name('payments');
        // ... existing treasury/expense routes
    });

    // ── المحاسبة المتقدمة (feature:accounting_advanced) ──
    Route::prefix('accounting')->name('accounting.')
        ->middleware(['admin.only', 'feature:accounting_advanced'])
        ->group(function () {

        // دليل الحسابات
        Route::resource('accounts', ChartOfAccountsController::class)
            ->except(['show'])->parameters(['accounts' => 'account']);

        // القيود اليومية
        Route::resource('journal-entries', JournalEntryController::class);
        Route::post('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
        Route::post('journal-entries/{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');

        // سندات
        Route::get('receipts/create', [ReceiptVoucherController::class, 'create'])->name('receipts.create');
        Route::post('receipts', [ReceiptVoucherController::class, 'store'])->name('receipts.store');
        Route::get('payment-vouchers/create', [PaymentVoucherController::class, 'create'])->name('payment-vouchers.create');
        Route::post('payment-vouchers', [PaymentVoucherController::class, 'store'])->name('payment-vouchers.store');

        // البنوك
        Route::resource('bank-accounts', BankAccountController::class);
        Route::get('bank-reconciliation', [BankReconciliationController::class, 'index'])->name('bank-reconciliation.index');
        Route::post('bank-reconciliation', [BankReconciliationController::class, 'store'])->name('bank-reconciliation.store');

        // الفترات المالية
        Route::resource('fiscal-periods', FiscalPeriodController::class)->only(['index', 'show']);
        Route::post('fiscal-periods/{period}/close', [FiscalPeriodController::class, 'close'])->name('fiscal-periods.close');

        // الإعدادات
        Route::get('settings', [AccountingSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [AccountingSettingsController::class, 'update'])->name('settings.update');
    });

    // ── التقارير المالية ──
    Route::prefix('reports')->name('reports.')->middleware('admin.only')->group(function () {
        // existing reports...
        Route::middleware('feature:accounting_advanced')->group(function () {
            Route::get('/trial-balance', [ReportingController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/balance-sheet', [ReportingController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/general-ledger', [ReportingController::class, 'generalLedger'])->name('general-ledger');
            Route::get('/account-statement', [ReportingController::class, 'accountStatement'])->name('account-statement');
            Route::get('/cash-flow', [ReportingController::class, 'cashFlow'])->name('cash-flow');
            Route::get('/aging', [ReportingController::class, 'aging'])->name('aging');
        });
    });
});
```

---

## 6. تصميم قاعدة البيانات

### 6.1 `account_types`

```sql
CREATE TABLE account_types (
    id          TINYINT UNSIGNED PRIMARY KEY,  -- ثابت: 1-5
    code        VARCHAR(20) NOT NULL UNIQUE,   -- asset, liability, equity, revenue, expense
    name_ar     VARCHAR(100) NOT NULL,
    name_en     VARCHAR(100) NOT NULL,
    normal_balance ENUM('debit', 'credit') NOT NULL,
    sort_order  TINYINT UNSIGNED NOT NULL
);
-- بيانات ثابتة: 5 أنواع فقط — لا يُنشأ من UI
```

### 6.2 `accounts` — دليل الحسابات

```sql
CREATE TABLE accounts (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code            VARCHAR(20) NOT NULL,           -- مثل: 1110, 4100
    name_ar         VARCHAR(200) NOT NULL,
    name_en         VARCHAR(200) NULL,
    account_type_id TINYINT UNSIGNED NOT NULL,      -- FK → account_types
    parent_id       BIGINT UNSIGNED NULL,           -- شجرة
    level           TINYINT UNSIGNED NOT NULL DEFAULT 1,
    is_leaf         BOOLEAN NOT NULL DEFAULT TRUE,  -- فقط الورق يستقبل قيود
    is_system       BOOLEAN NOT NULL DEFAULT FALSE, -- لا يُحذف
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    description     TEXT NULL,
    -- ربط sub-ledger
    linked_model    VARCHAR(100) NULL,              -- Customer, Supplier, BankAccount
    linked_id       BIGINT UNSIGNED NULL,
    created_by      BIGINT UNSIGNED NULL,
    updated_by      BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP NULL,

    UNIQUE KEY uk_accounts_code (code),
    INDEX idx_accounts_parent (parent_id),
    INDEX idx_accounts_type (account_type_id),
    INDEX idx_accounts_linked (linked_model, linked_id),
    FOREIGN KEY (parent_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
);
```

### 6.3 `fiscal_years` + `fiscal_periods`

```sql
CREATE TABLE fiscal_years (
    id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(50) NOT NULL,               -- "2026"
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    is_closed   BOOLEAN NOT NULL DEFAULT FALSE,
    closed_at   TIMESTAMP NULL,
    closed_by   BIGINT UNSIGNED NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE fiscal_periods (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    fiscal_year_id  BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(50) NOT NULL,           -- "يناير 2026"
    period_number   TINYINT UNSIGNED NOT NULL,      -- 1-12
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,
    is_closed       BOOLEAN NOT NULL DEFAULT FALSE,
    closed_at       TIMESTAMP NULL,
    closed_by       BIGINT UNSIGNED NULL,

    FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id),
    UNIQUE KEY uk_period (fiscal_year_id, period_number)
);
```

### 6.4 `journal_entries` — رأس القيد

```sql
CREATE TABLE journal_entries (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    entry_number        VARCHAR(30) NOT NULL,       -- JE-2026-000001
    entry_date          DATE NOT NULL,
    fiscal_period_id    BIGINT UNSIGNED NOT NULL,
    description         TEXT NOT NULL,
    reference           VARCHAR(100) NULL,        -- رقم فاتورة، سند، إلخ
    status              ENUM('draft', 'posted', 'reversed') NOT NULL DEFAULT 'draft',
    source_type         VARCHAR(50) NOT NULL,       -- manual, sales_invoice, payment, ...
    source_id           BIGINT UNSIGNED NULL,       -- ID المصدر
  source_event_key    VARCHAR(100) NULL,        -- idempotency: "sales_invoice:123:confirmed"
    total_debit         DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_credit        DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency_code       CHAR(3) NOT NULL DEFAULT 'EGP',
    reversed_entry_id   BIGINT UNSIGNED NULL,     -- القيد الذي عُكس
    reversal_of_id      BIGINT UNSIGNED NULL,     -- القيد الأصلي المعكوس
    posted_at           TIMESTAMP NULL,
    posted_by           BIGINT UNSIGNED NULL,
    created_by          BIGINT UNSIGNED NULL,
    created_at          TIMESTAMP,
    updated_at          TIMESTAMP,

    UNIQUE KEY uk_entry_number (entry_number),
    UNIQUE KEY uk_source_event (source_event_key),  -- منع الترحيل المزدوج
    INDEX idx_entry_date (entry_date),
    INDEX idx_status (status),
    INDEX idx_source (source_type, source_id),
    FOREIGN KEY (fiscal_period_id) REFERENCES fiscal_periods(id),
    FOREIGN KEY (reversal_of_id) REFERENCES journal_entries(id)
);
```

### 6.5 `journal_entry_lines` — سطور القيد

```sql
CREATE TABLE journal_entry_lines (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    journal_entry_id    BIGINT UNSIGNED NOT NULL,
    line_number         SMALLINT UNSIGNED NOT NULL,
    account_id          BIGINT UNSIGNED NOT NULL,
    debit               DECIMAL(15,2) NOT NULL DEFAULT 0,
    credit              DECIMAL(15,2) NOT NULL DEFAULT 0,
    description         VARCHAR(500) NULL,
    cost_center_id      BIGINT UNSIGNED NULL,       -- Phase 5
    -- للتتبع
    party_type          VARCHAR(100) NULL,          -- Customer, Supplier
    party_id            BIGINT UNSIGNED NULL,

    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    INDEX idx_lines_account (account_id),
    INDEX idx_lines_party (party_type, party_id),
    -- CONSTRAINT: debit > 0 XOR credit > 0 (ليس الاثنان معاً)
    CHECK (NOT (debit > 0 AND credit > 0)),
    CHECK (debit >= 0 AND credit >= 0)
);
```

### 6.6 `bank_accounts`

```sql
CREATE TABLE bank_accounts (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    account_id      BIGINT UNSIGNED NOT NULL,       -- حساب GL المرتبط (1120-x)
    bank_name       VARCHAR(200) NOT NULL,
    account_number  VARCHAR(50) NOT NULL,
    iban            VARCHAR(50) NULL,
    currency_code   CHAR(3) NOT NULL DEFAULT 'EGP',
    opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,

    FOREIGN KEY (account_id) REFERENCES accounts(id)
);
```

### 6.7 `accounting_settings` (سجل واحد لكل tenant)

```sql
CREATE TABLE accounting_settings (
    id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_name            VARCHAR(200) NULL,
    fiscal_year_start_month TINYINT UNSIGNED NOT NULL DEFAULT 1,  -- 1=يناير
    default_currency        CHAR(3) NOT NULL DEFAULT 'EGP',
    tax_enabled             BOOLEAN NOT NULL DEFAULT TRUE,
    default_tax_rate        DECIMAL(5,2) NOT NULL DEFAULT 14.00,
    tax_account_output_id   BIGINT UNSIGNED NULL,  -- حساب VAT مستحق
    tax_account_input_id    BIGINT UNSIGNED NULL,  -- حساب VAT مدفوع
    cash_account_id         BIGINT UNSIGNED NULL,  -- 1110
    ar_account_id           BIGINT UNSIGNED NULL,  -- 1210
    ap_account_id           BIGINT UNSIGNED NULL,  -- 2110
    inventory_account_id    BIGINT UNSIGNED NULL,  -- 1310
    cogs_account_id         BIGINT UNSIGNED NULL,  -- 5100
    sales_revenue_account_id BIGINT UNSIGNED NULL, -- 4100
    retained_earnings_id    BIGINT UNSIGNED NULL,  -- 3200
    -- حسابات إضافية (تصحيحات QA)
    wip_account_id              BIGINT UNSIGNED NULL,  -- 1350 إنتاج تحت التشغيل
    income_summary_account_id   BIGINT UNSIGNED NULL,  -- 3250 ملخص الدخل
    sales_discount_account_id   BIGINT UNSIGNED NULL,  -- 4800 خصم مبيعات
    shipping_revenue_account_id BIGINT UNSIGNED NULL,  -- 4300 إيرادات شحن
    other_charges_account_id    BIGINT UNSIGNED NULL,  -- 4400 رسوم أخرى
    rounding_account_id         BIGINT UNSIGNED NULL,  -- 5295 فروقات تقريب
    advance_customer_account_id BIGINT UNSIGNED NULL,  -- 2120 دفعات مقدمة من عملاء
    advance_supplier_account_id BIGINT UNSIGNED NULL,  -- 1400 دفعات مقدمة لموردين
    capitalize_freight      BOOLEAN NOT NULL DEFAULT TRUE,  -- تحميل الشحن على المخزون (مشتريات)
    auto_post_invoices      BOOLEAN NOT NULL DEFAULT TRUE,
    auto_post_payments      BOOLEAN NOT NULL DEFAULT TRUE,
    auto_post_expenses      BOOLEAN NOT NULL DEFAULT TRUE,
    auto_post_manufacturing BOOLEAN NOT NULL DEFAULT FALSE,
    numbering_prefix_je     VARCHAR(10) NOT NULL DEFAULT 'JE',
    created_at              TIMESTAMP,
    updated_at              TIMESTAMP
);
```

### 6.7b `account_balances` — الأرصدة المُجمّعة (Materialized)

> **الغرض:** حساب رصيد أي حساب في O(1) بدل مسح `journal_entry_lines` كل مرة (أداء الـ dashboard والتقارير). يُحدَّث عند كل `post()`/`reverse()`.

```sql
CREATE TABLE account_balances (
    account_id      BIGINT UNSIGNED PRIMARY KEY,
    period_debit    DECIMAL(15,2) NOT NULL DEFAULT 0,
    period_credit   DECIMAL(15,2) NOT NULL DEFAULT 0,
    ytd_debit       DECIMAL(15,2) NOT NULL DEFAULT 0,
    ytd_credit      DECIMAL(15,2) NOT NULL DEFAULT 0,
    balance         DECIMAL(15,2) NOT NULL DEFAULT 0,
    last_entry_id   BIGINT UNSIGNED NULL,
    last_entry_date DATE NULL,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (account_id) REFERENCES accounts(id)
);
```

يُعاد بناؤه بالكامل عبر `php artisan accounting:rebuild-balances`.

### 6.8b `accounting_posting_failures` — سجل فشل الترحيل

```sql
CREATE TABLE accounting_posting_failures (
    id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    source_type   VARCHAR(50) NOT NULL,
    source_id     BIGINT UNSIGNED NOT NULL,
    event_key     VARCHAR(100) NOT NULL,
    error_message TEXT NOT NULL,
    error_class   VARCHAR(200) NOT NULL,
    attempts      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    resolved_at   TIMESTAMP NULL,
    resolved_by   BIGINT UNSIGNED NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_failures_unresolved (resolved_at, created_at),
    INDEX idx_failures_source (source_type, source_id)
);
```

### 6.8 `accounting_audit_logs`

```sql
CREATE TABLE accounting_audit_logs (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NULL,
    action          VARCHAR(50) NOT NULL,   -- created, posted, reversed, closed_period
    auditable_type  VARCHAR(100) NOT NULL,
    auditable_id    BIGINT UNSIGNED NOT NULL,
    old_values      JSON NULL,
    new_values      JSON NULL,
    ip_address      VARCHAR(45) NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_audit (auditable_type, auditable_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_date (created_at)
);
```

### 6.9 تعديلات على الجداول الموجودة

```sql
-- cash_transactions
ALTER TABLE cash_transactions
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL AFTER reference,
    ADD FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id);

-- payments
ALTER TABLE payments
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL,
    ADD FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id);

-- ملاحظة: المصروفات تُخزَّن في cash_transactions (نوع withdrawal) — لا حاجة لتعديل جدول expenses
-- (قرار: الخيار A). journal_entry_id على cash_transactions يغطي المصروفات والإيداعات والسحوبات.

-- ربط القيود بالمصادر (تتبع عكسي)
ALTER TABLE sales_invoices
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL,
    ADD COLUMN cogs_entry_id    BIGINT UNSIGNED NULL;
ALTER TABLE purchase_invoices
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
ALTER TABLE sales_returns
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
ALTER TABLE purchase_returns
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
```

---

## 7. دليل الحسابات الافتراضي

يُزرع تلقائياً عند إنشاء كل tenant جديد عبر `DefaultChartOfAccountsSeeder`.

```
الكود    │ الاسم                          │ النوع      │ ورقي │ نظام
─────────┼────────────────────────────────┼────────────┼──────┼──────
1000     │ الأصول                         │ أصول       │ لا   │ نعم
  1100   │   الأصول المتداولة             │ أصول       │ لا   │ نعم
    1110 │     الصندوق (نقدية)            │ أصول       │ نعم  │ نعم  ← cash_account
    1120 │     البنوك                     │ أصول       │ لا   │ نعم
      1121│      البنك الرئيسي            │ أصول       │ نعم  │ نعم
    1210 │     الذمم المدينة (عملاء)      │ أصول       │ نعم  │ نعم  ← ar_account
    1310 │     المخزون                    │ أصول       │ نعم  │ نعم  ← inventory_account
    1320 │     ضريبة القيمة المضافة مدفوعة│ أصول       │ نعم  │ نعم  ← tax_input
    1350 │     إنتاج تحت التشغيل (WIP)    │ أصول       │ نعم  │ نعم  ← wip_account (تصنيع)
    1400 │     دفعات مقدمة لموردين        │ أصول       │ نعم  │ نعم  ← advance_supplier
  1500   │   الأصول الثابتة               │ أصول       │ لا   │ نعم
    1510 │     معدات وآلات                │ أصول       │ نعم  │ لا
    1590 │     مجمع الإهلاك               │ أصول       │ نعم  │ لا
2000     │ الخصوم                         │ خصوم       │ لا   │ نعم
  2100   │   الخصوم المتداولة             │ خصوم       │ لا   │ نعم
    2110 │     الذمم الدائنة (موردين)     │ خصوم       │ نعم  │ نعم  ← ap_account
    2120 │     دفعات مقدمة من عملاء       │ خصوم       │ نعم  │ نعم  ← advance_customer
    2210 │     ضريبة القيمة المضافة مستحقة│ خصوم       │ نعم  │ نعم  ← tax_output
3000     │ حقوق الملكية                   │ حقوق       │ لا   │ نعم
  3100   │   رأس المال                    │ حقوق       │ نعم  │ نعم
  3200   │   الأرباح المحتجزة             │ حقوق       │ نعم  │ نعم  ← retained_earnings
  3250   │   ملخص الدخل (مؤقت)            │ حقوق       │ نعم  │ نعم  ← income_summary
  3300   │   مسحوبات المالك               │ حقوق       │ نعم  │ نعم
4000     │ الإيرادات                      │ إيرادات    │ لا   │ نعم
  4100   │   إيرادات المبيعات             │ إيرادات    │ نعم  │ نعم  ← sales_revenue
  4200   │   إيرادات أخرى                 │ إيرادات    │ نعم  │ لا
  4300   │   إيرادات شحن                  │ إيرادات    │ نعم  │ نعم  ← shipping_revenue
  4400   │   إيرادات رسوم أخرى            │ إيرادات    │ نعم  │ لا
  4800   │   خصم مبيعات مسموح به          │ إيرادات    │ نعم  │ نعم  (contra) ← sales_discount
  4900   │   مرتجعات المبيعات             │ إيرادات    │ نعم  │ نعم  (contra-revenue)
5000     │ المصروفات                      │ مصروفات    │ لا   │ نعم
  5100   │   تكلفة البضاعة المباعة       │ مصروفات    │ نعم  │ نعم  ← cogs_account
  5150   │   تكلفة مواد خام مستخدمة      │ مصروفات    │ نعم  │ نعم  (تصنيع)
  5200   │   مصروفات تشغيلية              │ مصروفات    │ لا   │ نعم
    5210 │     إيجارات                    │ مصروفات    │ نعم  │ لا
    5220 │     رواتب وأجور                │ مصروفات    │ نعم  │ لا
    5230 │     كهرباء ومياه               │ مصروفات    │ نعم  │ لا
    5240 │     مواصلات                    │ مصروفات    │ نعم  │ لا
    5290 │     مصروفات متنوعة             │ مصروفات    │ نعم  │ نعم
    5295 │     فروقات تقريب               │ مصروفات    │ نعم  │ نعم  ← rounding_account
  5300   │   مصروفات إدارية وعمومية       │ مصروفات    │ نعم  │ لا
  5400   │   مصروفات شحن                  │ مصروفات    │ نعم  │ لا
```

**قاعدة:** الحسابات ذات `is_system = true` لا تُحذف ولا يُغيّر نوعها. يمكن تعديل الاسم فقط.

---

## 8. قواعد الترحيل المحاسبي

> **المعادلة المرجعية (من الكود `InvoiceService::calculateInvoiceTotals`):**
> ```
> total = subtotal − discount_amount + tax_amount + shipping_cost + other_charges
> ```
> - `subtotal` = مجموع بنود الأصناف فقط (لا يشمل الشحن/الرسوم)
> - `tax_amount` = يُحسب على المبلغ بعد الخصم
> - دائن حساب الإيرادات 4100 = `subtotal` **كاملاً** (لا يُطرح منه الشحن)
> - كل قيد أدناه مُثبَت التوازن جبرياً.

### 8.1 فاتورة مبيعات — نقدي (POS)

**الحدث:** `SalesInvoiceConfirmed`  
**الشرط:** `paid >= total`  
**مفتاح Idempotency:** `sales_invoice:{id}:confirmed`

```
السطر │ الحساب              │ مدين              │ دائن
──────┼─────────────────────┼───────────────────┼──────────
  1   │ 1110 الصندوق        │ total             │
  2   │ 4800 خصم مبيعات     │ discount_amount   │          (إن > 0)
  3   │ 4100 إيرادات مبيعات │                   │ subtotal
  4   │ 4300 إيرادات شحن    │                   │ shipping_cost  (إن > 0)
  5   │ 4400 إيرادات رسوم   │                   │ other_charges  (إن > 0)
  6   │ 2210 VAT مستحق      │                   │ tax_amount
```

**+ قيد COGS (دائماً — نفس الحدث، مفتاح `:cogs`):**

```
  1   │ 5100 COGS           │ cogs              │
  2   │ 1310 المخزون        │                   │ cogs
```

حيث `cogs = Σ(quantity × unit_cost_snapshot)` من `inventory_movements`.

**إثبات التوازن:** مدين = `total + discount` = `subtotal + tax + shipping + other` = دائن ✓

### 8.2 فاتورة مبيعات — آجل

**الشرط:** `paid = 0`

```
السطر │ الحساب              │ مدين              │ دائن
──────┼─────────────────────┼───────────────────┼──────────
  1   │ 1210 ذمم مدينة      │ total             │          (party: customer_id)
  2   │ 4800 خصم مبيعات     │ discount_amount   │          (إن > 0)
  3   │ 4100 إيرادات مبيعات │                   │ subtotal
  4   │ 4300 إيرادات شحن    │                   │ shipping_cost  (إن > 0)
  5   │ 4400 إيرادات رسوم   │                   │ other_charges  (إن > 0)
  6   │ 2210 VAT مستحق      │                   │ tax_amount
```

**+ قيد COGS** (كما في 8.1).

### 8.3 فاتورة مبيعات — دفع جزئي عند التأكيد

**الشرط:** `0 < paid < total`

```
السطر │ الحساب              │ مدين              │ دائن
──────┼─────────────────────┼───────────────────┼──────────
  1   │ 1110 الصندوق        │ paid              │
  2   │ 1210 ذمم مدينة      │ total - paid      │          (party: customer_id)
  3   │ 4800 خصم مبيعات     │ discount_amount   │          (إن > 0)
  4   │ 4100 إيرادات مبيعات │                   │ subtotal
  5   │ 4300 إيرادات شحن    │                   │ shipping_cost  (إن > 0)
  6   │ 4400 إيرادات رسوم   │                   │ other_charges  (إن > 0)
  7   │ 2210 VAT مستحق      │                   │ tax_amount
```

**+ قيد COGS** (كما في 8.1). عند دفع الباقي لاحقاً → سند قبض (8.6).

**المنطق:** `paid >= total` → 8.1، `0 < paid < total` → 8.3، `paid = 0` → 8.2.

### 8.4 مرتجع مبيعات

**الحدث:** `SalesReturnProcessed`  
**مفتاح:** `sales_return:{id}:processed`

```
عكس قيد المبيعات:
  1   │ 4900 مرتجعات مبيعات │ subtotal          │
  2   │ 2210 VAT مستحق      │ tax_amount        │
  3   │ 4800 خصم مبيعات     │                   │ discount_amount  (إن > 0)
  4   │ 1210/1110 عميل/نقد  │                   │ total  (party: customer_id)

+ عكس COGS:
  1   │ 1310 المخزون        │ cogs              │
  2   │ 5100 COGS           │                   │ cogs
```

### 8.5 فاتورة مشتريات

**الحدث:** `PurchaseInvoiceConfirmed`  
**مفتاح:** `purchase_invoice:{id}:confirmed`

```
السطر │ الحساب              │ مدين                              │ دائن
──────┼─────────────────────┼───────────────────────────────────┼──────────
  1   │ 1310 المخزون        │ subtotal − discount + shipping + other │        (Landed Cost)
  2   │ 1320 VAT مدفوع      │ tax_amount                        │
  3   │ 2110 ذمم دائنة      │                                   │ total   (party: supplier_id)
```

**لا يُسجّل كمصروف** — يذهب للمخزون حتى يُباع. المخزون يُحمَّل بالتكلفة الصافية (الخصم يُنقصها، الشحن يُضاف إليها).

**إثبات التوازن:** مدين = `(subtotal − discount + shipping + other) + tax` = `total` = دائن ✓

### 8.5b مرتجع مشتريات

**الحدث:** `PurchaseReturnProcessed`  
**مفتاح:** `purchase_return:{id}:processed`

```
السطر │ الحساب              │ مدين              │ دائن
──────┼─────────────────────┼───────────────────┼──────────
  1   │ 2110 ذمم دائنة      │ total             │          (party: supplier_id)
  2   │ 1310 المخزون        │                   │ subtotal − discount_amount
  3   │ 1320 VAT مدفوع      │                   │ tax_amount
```

**ملاحظة:** إن كان المرتجع نقدياً (المورد ردّ المبلغ) → مدين `1110` بدل `2110`.

### 8.6 دفعة عميل (سند قبض)

**الحدث:** `PaymentReceived` (payable = SalesInvoice)  
**مفتاح:** `payment:{id}:received`

```
السطر │ الحساب              │ مدين      │ دائن
──────┼─────────────────────┼───────────┼──────────
  1   │ 1110/1121 نقد/بنك  │ amount    │
  2   │ 1210 ذمم مدينة      │           │ amount  (party: customer_id)
```

### 8.7 دفعة مورد (سند صرف)

**الحدث:** `SupplierPaymentCreated`  
**مفتاح:** `supplier_payment:{id}:created`

```
السطر │ الحساب              │ مدين      │ دائن
──────┼─────────────────────┼───────────┼──────────
  1   │ 2110 ذمم دائنة      │ amount    │          (party: supplier_id)
  2   │ 1110/1121 نقد/بنك  │           │ amount
```

### 8.8 مصروف تشغيلي

> **المصدر (قرار الخيار A):** المصروف = `CashTransaction` نوع `withdrawal`. لا يوجد جدول `expenses` مستقل مستخدَم. الترحيل يتم عبر `postCashTransaction()` مع تحديد حساب المصروف من `category`.

**الحدث:** `CashTransactionCreated` (نوع withdrawal بتصنيف مصروف)  
**مفتاح:** `cash_transaction:{id}:created`

```
السطر │ الحساب              │ مدين      │ دائن
──────┼─────────────────────┼───────────┼──────────
  1   │ 52xx مصروف (حسب category) │ amount │
  2   │ 1110 الصندوق        │           │ amount
```

**ملاحظة:** يُربط كل `category` في `expense_categories` بحساب GL (عمود `account_id` يُضاف لـ `expense_categories`)، وإلا يُستخدم `5290 مصروفات متنوعة` افتراضياً.

### 8.9 إيداع/سحب خزينة يدوي

> **مهم:** الحساب المقابل **يختاره المستخدم** — لا يُفترض أنه إيراد/مصروف دائماً (قد يكون رأس مال، تحويل بنكي، تصحيح...).

**إيداع:**
```
  1   │ 1110 الصندوق              │ amount    │
  2   │ [counter_account_id]      │           │ amount   ← يختاره المستخدم
```

**سحب:**
```
  1   │ [counter_account_id]      │ amount    │          ← يختاره المستخدم
  2   │ 1110 الصندوق              │           │ amount
```

### 8.10 التصنيع (Manufacturing)

**المرحلة 1 — تأكيد الأمر (سحب مواد خام):**  
**الحدث:** `ManufacturingOrderConfirmed` — **مفتاح:** `manufacturing_order:{id}:confirmed`
```
  1   │ 1350 إنتاج تحت التشغيل (WIP) │ material_cost │
  2   │ 1310 المخزون (مواد خام)      │               │ material_cost
```
حيث `material_cost = Σ(component.quantity × component.unit_cost)`.

**المرحلة 2 — إكمال الأمر (إدخال منتج تام):**  
**الحدث:** `ManufacturingOrderCompleted` — **مفتاح:** `manufacturing_order:{id}:completed`
```
  1   │ 1310 المخزون (منتج تام)      │ production_cost │
  2   │ 1350 إنتاج تحت التشغيل (WIP) │                 │ production_cost
```
حيث `production_cost = order.cost_per_unit × order.quantity_produced`. بعد الإكمال رصيد WIP لهذا الأمر = 0.

> **ملاحظة:** أحداث التصنيع قد تحتاج إنشاء (لا توجد `ManufacturingOrderConfirmed/Completed` events حالياً — تُضاف في Phase 3، أو يُربط الترحيل مباشرة في `ManufacturingOrderService`).

### 8.11 إغلاق فترة مالية — عبر ملخص الدخل (3 قيود)

```
قيد 1 — إقفال الإيرادات:
  مدين: 4100/4200/4300 (كل حساب إيراد برصيده)  │ balance
  دائن: 3250 ملخص الدخل                          │ total_revenues

قيد 2 — إقفال المصروفات:
  مدين: 3250 ملخص الدخل                          │ total_expenses
  دائن: 5100/52xx (كل حساب مصروف برصيده)         │ balance

قيد 3 — ترحيل صافي النتيجة:
  ربح:   مدين 3250 / دائن 3200 (أرباح محتجزة)   │ net_income
  خسارة: مدين 3200 / دائن 3250                   │ net_loss
```

بعد الإقفال: رصيد كل حسابات الإيرادات والمصروفات و3250 = صفر.

### 8.12 إلغاء فاتورة (عكس)

**الحدث:** `SalesInvoiceCancelled` / `PurchaseInvoiceCancelled`  
**الإجراء:** إنشاء قيد عكسي مرتبط بـ `reversal_of_id` — لا حذف القيد الأصلي.

```php
// JournalEntryService::reverse(JournalEntry $original)
$reversal = $this->createReversalEntry($original);
// كل سطر: debit ↔ credit معكوس
$original->update(['status' => 'reversed', 'reversed_entry_id' => $reversal->id]);
```

---

## 9. الصفحات والواجهات

### 9.1 لوحة التحكم المالية (`/accounting/dashboard`)

**Widgets:**
| Widget | المصدر | التحديث |
|--------|--------|---------|
| إجمالي السيولة | `1110 + 1120` balances | لحظي |
| الذمم المدينة | `1210` balance | لحظي |
| الذمم الدائنة | `2110` balance | لحظي |
| صافي ربح الشهر | P&L الشهر الحالي | يومي |
| آخر 10 قيود | `journal_entries` | لحظي |
| رسم بياني إيرادات/مصروفات | 6 أشهر | يومي |
| تنبيهات | فواتير متأخرة، فترات لم تُغلق | يومي |

### 9.2 دليل الحسابات (`/accounting/accounts`)

- عرض شجري (Tree view) مع expand/collapse
- إنشاء حساب فرعي (تحت حساب أب غير ورقي)
- تعديل الاسم والوصف (للحسابات غير النظامية)
- تعطيل/تفعيل (لا حذف للحسابات ذات قيود)
- عرض رصيد كل حساب ورقي
- فلترة بالنوع والحالة

**Validation:**
- `code` فريد، أرقام فقط، يبدأ بكود الأب
- لا قيود على `is_leaf = false`
- لا حذف `is_system = true`

### 9.3 القيود اليومية (`/accounting/journal-entries`)

**القائمة:**
- فلاتر: تاريخ، حالة، مصدر، رقم
- أعمدة: رقم القيد، التاريخ، الوصف، مدين، دائن، الحالة، المصدر

**الإنشاء:**
- تاريخ القيد (التحقق: الفترة مفتوحة)
- وصف إلزامي
- سطور ديناميكية (Alpine.js): حساب، مدين، دائن، وصف
- **التحقق الفوري:** مجموع مدين = مجموع دائن قبل الحفظ
- زر "حفظ كمسودة" / "حفظ واعتماد"

**العرض:**
- تفاصيل القيد + السطور
- زر "اعتماد" (للمسودة)
- زر "عكس القيد" (للمعتمد)
- طباعة

### 9.4 سند قبض (`/accounting/receipts/create`)

- اختيار عميل (autocomplete)
- عرض فواتيره غير المسددة
- المبلغ (كلي أو جزئي على فواتير محددة)
- طريقة الدفع: نقدي / بنك / شيك
- حساب البنك (إن وُجد)
- تاريخ القبض
- **النتيجة:** `Payment` + `JournalEntry` + تحديث `payment_status` للفاتورة

### 9.5 سند صرف (`/accounting/payment-vouchers/create`)

- اختيار مورد أو نوع مصروف
- فواتير المورد غير المسددة (إن مورد)
- المبلغ، طريقة الدفع، التاريخ
- **النتيجة:** `SupplierPayment`/`Expense` + `JournalEntry`

### 9.6 القائمة الجانبية المحدّثة

```
الحسابات ▼
  ├── لوحة التحكم
  ├── الخزينة
  ├── المصروفات
  ├── المدفوعات
  ├── ── محاسبة متقدمة ──        [@feature:accounting_advanced]
  ├── دليل الحسابات
  ├── القيود اليومية
  ├── سند قبض
  ├── سند صرف
  ├── حسابات البنوك
  ├── الفترات المالية
  ├── إعدادات المحاسبة
  └── التقارير ▼
        ├── ميزان المراجعة
        ├── قائمة الدخل
        ├── الميزانية العمومية
        ├── دفتر الأستاذ
        ├── كشف حساب
        ├── أعمار الديون
        └── التدفقات النقدية
```

---

## 10. طبقة الخدمات (Services)

### 10.1 `JournalEntryService` — القلب

```php
class JournalEntryService
{
    /**
     * إنشاء قيد (مسودة)
     * @throws UnbalancedEntryException
     * @throws ClosedPeriodException
     * @throws NonLeafAccountException
     */
    public function create(array $header, array $lines, ?string $eventKey = null): JournalEntry;

    /**
     * اعتماد قيد — يُحدّث الأرصدة
     * @throws UnbalancedEntryException
     * @throws ClosedPeriodException
     */
    public function post(JournalEntry $entry): JournalEntry;

    /**
     * عكس قيد معتمد — ينشئ قيد عكسي جديد
     */
    public function reverse(JournalEntry $entry, string $reason): JournalEntry;

    /**
     * ترحيل تلقائي من مصدر — idempotent
     */
    public function postFromSource(
        string $sourceType,
        int $sourceId,
        string $eventKey,
        array $lines,
        string $description,
        Carbon $date
    ): ?JournalEntry;  // null إذا مُرحّل مسبقاً

    /**
     * التحقق: مدين = دائن
     */
    public function validateBalance(array $lines): void;

    /**
     * التحقق: الفترة مفتوحة
     */
    public function assertPeriodOpen(Carbon $date): void;

    /**
     * توليد رقم قيد تسلسلي
     */
    public function generateEntryNumber(): string;
}
```

### 10.2 `PostingService` — الترحيل التلقائي

```php
class PostingService
{
    public function postSalesInvoice(SalesInvoice $invoice): ?JournalEntry;
    public function postSalesInvoiceCogs(SalesInvoice $invoice): ?JournalEntry;
    public function postPurchaseInvoice(PurchaseInvoice $invoice): ?JournalEntry;
    public function postSalesReturn(SalesReturn $return): ?JournalEntry;
    public function postPayment(Payment $payment): ?JournalEntry;
    public function postSupplierPayment(SupplierPayment $payment): ?JournalEntry;
    public function postExpense(Expense $expense): ?JournalEntry;
    public function postCashTransaction(CashTransaction $txn): ?JournalEntry;
    public function reverseBySource(string $sourceType, int $sourceId): ?JournalEntry;
}
```

### 10.3 `AccountBalanceService`

```php
class AccountBalanceService
{
    /**
     * رصيد حساب حتى تاريخ معين
     * يحسب من journal_entry_lines WHERE status = posted AND entry_date <= $date
     */
    public function getBalance(int $accountId, ?Carbon $asOf = null): float;

    /**
     * أرصدة كل الحسابات الورقية — لميزان المراجعة
     */
    public function getAllBalances(?Carbon $asOf = null): Collection;

    /**
     * مطابقة: رصيد GL لعميل = customer.balance
     */
    public function reconcileCustomerBalance(int $customerId): ReconciliationResult;

    /**
     * مطابقة: رصيد GL لمورد = supplier.balance
     */
    public function reconcileSupplierBalance(int $supplierId): ReconciliationResult;
}
```

### 10.4 `FinancialReportService`

```php
class FinancialReportService
{
    public function trialBalance(Carbon $asOf): Collection;
    public function incomeStatement(Carbon $from, Carbon $to): array;
    public function balanceSheet(Carbon $asOf): array;
    public function generalLedger(int $accountId, Carbon $from, Carbon $to): Collection;
    public function accountStatement(int $accountId, Carbon $from, Carbon $to): Collection;
    public function cashFlowStatement(Carbon $from, Carbon $to): array;
    public function agingReport(string $type, Carbon $asOf): Collection; // ar | ap
}
```

---

## 11. التكامل مع الموديولات الحالية

### 11.1 EventServiceProvider — التسجيل

> **🔴 حالة الأحداث الفعلية (مؤكَّدة بالفحص المباشر للكود):**
> | الحدث | مُسجَّل في $listen؟ | يُطلَق فعلاً؟ | الإجراء |
> |-------|---------------|-------------|---------|
> | `SalesInvoiceConfirmed` | ✅ (UpdateCache) | ❌ **لا يُطلَق أبداً** | **إضافة `event()` في `InvoiceService`** + listener محاسبي |
> | `PurchaseInvoiceConfirmed` | ❌ **مفتاح غير موجود** | ❌ **لا يُطلَق أبداً** | إضافة المفتاح + **إضافة `event()` في `InvoiceService`** |
> | `SalesInvoiceCancelled` | ✅ | ✅ | إضافة listener عكسي |
> | `PurchaseInvoiceCancelled` | ✅ | ✅ | إضافة listener عكسي |
> | `PaymentReceived` | ✅ (Handle + Update + Log) | ✅ | إضافة listener محاسبي |
> | `SalesReturnProcessed` | ✅ | ✅ | إضافة listener محاسبي |
> | `PurchaseReturnProcessed` | ✅ | ✅ | إضافة listener محاسبي |
> | `SupplierPaymentCreated` | ❌ **الحدث غير موجود** | ❌ | إنشاء الحدث + إطلاقه من `SupplierPaymentController`/service |
> | `ManufacturingOrderConfirmed/Completed` | ❌ **غير موجودة** | ❌ | إنشاؤها + إطلاقها من `ManufacturingOrderService` (§8.10) |
>
> **⚠️ اكتشاف حرج:** الفاتورة تُنشأ مباشرة بـ `status='confirmed'` في `InvoiceService` (سطر 459 مبيعات، 1044 مشتريات) **دون إطلاق أي حدث**. لذلك حتى `SalesInvoiceConfirmed` المُسجَّل حالياً لا ينفّذ الـ listener. **Phase 3 يجب أن يضيف استدعاءات `event()` عند نقاط التأكيد أولاً**، وإلا لن يعمل الترحيل التلقائي إطلاقاً.
>
> **ملاحظة:** `RecordInAccountingLedger` موجود لكن غير مسجّل — **لا يُعاد استخدامه**، يُستبدل بـ `PostPaymentToLedger`.

**استدعاءات `event()` المطلوب إضافتها في `InvoiceService`:**
```php
// app/Services/InvoiceService.php — بعد حفظ الفاتورة داخل نفس DB::transaction

// عند تأكيد فاتورة المبيعات (قرب سطر 459):
event(new \App\Events\Invoice\SalesInvoiceConfirmed($invoice));

// عند تأكيد فاتورة المشتريات (قرب سطر 1044):
event(new \App\Events\Invoice\PurchaseInvoiceConfirmed($invoice));
```

```php
// app/Providers/EventServiceProvider.php — إضافات (تُدمج مع $listen الحالي)

\App\Events\Invoice\SalesInvoiceConfirmed::class => [
    \App\Listeners\Invoice\UpdateSalesInvoiceConfirmedCache::class, // موجود
    \App\Listeners\Accounting\PostSalesInvoiceToLedger::class,      // جديد
],

// مفتاح جديد بالكامل — يجب إضافته:
\App\Events\Invoice\PurchaseInvoiceConfirmed::class => [
    \App\Listeners\Accounting\PostPurchaseInvoiceToLedger::class,
],

\App\Events\Invoice\SalesInvoiceCancelled::class => [
    \App\Listeners\Invoice\HandleSalesInvoiceCancellation::class,   // موجود
    \App\Listeners\Accounting\ReverseInvoiceJournalEntry::class,    // جديد
],

\App\Events\Invoice\PurchaseInvoiceCancelled::class => [
    \App\Listeners\Invoice\HandlePurchaseInvoiceCancellation::class, // موجود
    \App\Listeners\Accounting\ReverseInvoiceJournalEntry::class,     // جديد
],

\App\Events\Payment\PaymentReceived::class => [
    \App\Listeners\Payment\HandlePaymentReceived::class,   // موجود
    \App\Listeners\Accounting\PostPaymentToLedger::class,  // جديد
],

\App\Events\Return\SalesReturnProcessed::class => [
    \App\Listeners\Return\HandleSalesReturnProcessed::class,      // موجود
    \App\Listeners\Accounting\PostSalesReturnToLedger::class,     // جديد
],

\App\Events\Return\PurchaseReturnProcessed::class => [
    \App\Listeners\Return\HandlePurchaseReturnProcessed::class,   // موجود
    \App\Listeners\Accounting\PostPurchaseReturnToLedger::class,  // جديد
],

// أحداث جديدة تُنشأ (Phase 2/3):
\App\Events\Payment\SupplierPaymentCreated::class => [
    \App\Listeners\Accounting\PostSupplierPaymentToLedger::class,
],
\App\Events\Manufacturing\ManufacturingOrderConfirmed::class => [
    \App\Listeners\Accounting\PostManufacturingConfirmToLedger::class,
],
\App\Events\Manufacturing\ManufacturingOrderCompleted::class => [
    \App\Listeners\Accounting\PostManufacturingCompleteToLedger::class,
],
```

### 11.1b أحداث يجب إنشاؤها + مكان إطلاقها

| الحدث | يُطلَق من | متى |
|-------------|-----------|------|
| `SalesInvoiceConfirmed` (موجود، غير مُطلَق) | **`InvoiceService` (سطر ~459)** | عند إنشاء/تأكيد فاتورة مبيعات |
| `PurchaseInvoiceConfirmed` (موجود، غير مُطلَق) | **`InvoiceService` (سطر ~1044)** | عند إنشاء/تأكيد فاتورة مشتريات |
| `SupplierPaymentCreated` (جديد) | `SupplierPaymentController::store()` أو service | عند تسجيل دفعة مورد |
| `ManufacturingOrderConfirmed` (جديد) | **`ManufacturingOrderService::confirmOrder()`** | عند سحب المواد الخام |
| `ManufacturingOrderCompleted` (جديد) | **`ManufacturingOrderService::completeOrder()`** | عند إدخال المنتج التام |

> **قاعدة معمارية:** أحداث الأعمال تُطلَق من طبقة الـ **Service** (حيث تُحفظ البيانات داخل `DB::transaction`) وليس من الـ Controller — لضمان اتساق الترحيل مع العملية الأصلية.

### 11.2 Listener نموذجي

```php
class PostSalesInvoiceToLedger implements ShouldQueue
{
    public function __construct(private PostingService $posting) {}

    public function handle(SalesInvoiceConfirmed $event): void
    {
        $invoice = $event->invoice;

        // تخطي إذا الإعدادات تعطل الترحيل التلقائي
        if (!AccountingSetting::first()?->auto_post_invoices) return;

        DB::transaction(function () use ($invoice) {
            $this->posting->postSalesInvoice($invoice);
            $this->posting->postSalesInvoiceCogs($invoice);
        });
    }
}
```

### 11.3 ربط POS Shift

عند إغلاق وردية (`PosShiftController::close`):
- مقارنة النقد الفعلي vs المتوقع
- إذا فرق → قيد تسوية (5290 ↔ 1110)

---

## 12. التقارير المالية

### 12.1 ميزان المراجعة (Trial Balance)

```
الحساب │ اسم الحساب │ مدين │ دائن
───────┼────────────┼──────┼──────
1110   │ الصندوق    │ xxx  │
1210   │ عملاء      │ xxx  │
...    │ ...        │ ...  │ ...
                              │ xxx  │ 2110 موردين
───────┼────────────┼──────┼──────
المجموع│            │ TOTAL│ TOTAL  ← يجب أن يتساويا
```

**مصدر البيانات:** `AccountBalanceService::getAllBalances($asOf)`

### 12.2 قائمة الدخل (Income Statement)

```
إيرادات المبيعات                    xxx
  (−) مرتجعات المبيعات              (xxx)
  (−) خصومات المبيعات               (xxx)
صافي المبيعات                       xxx
تكلفة البضاعة المباعة              (xxx)
────────────────────────────────────────
مجمل الربح                          xxx
مصروفات تشغيلية                    (xxx)
  إيجارات                           (xxx)
  رواتب                             (xxx)
  ...
────────────────────────────────────────
صافي الربح قبل الضريبة              xxx
```

**مصدر:** حسابات نوع `revenue` و `expense` (5100-5999) للفترة.

### 12.3 الميزانية العمومية (Balance Sheet)

```
الأصول
  الأصول المتداولة
    الصندوق والبنوك                  xxx
    الذمم المدينة                    xxx
    المخزون                          xxx
    ضريبة مدفوعة                     xxx
  الأصول الثابتة                     xxx
  (−) مجمع الإهلاك                   (xxx)
إجمالي الأصول                       xxx

الخصوم
  الذمم الدائنة                      xxx
  ضريبة مستحقة                       xxx
حقوق الملكية
  رأس المال                          xxx
  الأرباح المحتجزة                   xxx
  (−) مسحوبات                        (xxx)
إجمالي الخصوم + حقوق الملكية        xxx

التحقق: إجمالي الأصول = إجمالي الخصوم + حقوق الملكية
```

### 12.4 دفتر الأستاذ (General Ledger)

لكل حساب: كل القيود المعتمدة مرتبة بالتاريخ مع الرصيد الجاري.

### 12.5 أعمار الديون (Aging)

```
العميل │ إجمالي │ جاري │ 1-30 │ 31-60 │ 61-90 │ +90
───────┼────────┼──────┼──────┼───────┼───────┼────
...    │ ...    │ ...  │ ...  │ ...   │ ...   │ ...
```

**مصدر:** فواتير غير مسددة مجمّعة بعمر الفاتورة من `due_date`.

---

## 13. SaaS: الباقات والصلاحيات

### 13.1 Plan Features

> **مطلوب (مؤكَّد من الكود):** `PlanFeature` الحالي يحتوي فقط على: POS, MANUFACTURING, MULTI_WAREHOUSE, ACCOUNTING, STOCK_COUNT, PURCHASE, REPORTS_ADVANCED. **لا يوجد `accounting_advanced`**، و`PlanSeeder` لا يوزّعه على أي باقة. بدون هذا الإصلاح، أي route محمي بـ `feature:accounting_advanced` **سيُحجب عن جميع المستأجرين**.

**الخطوة 1 — إضافة الثابت:**
```php
// app/Models/PlanFeature.php
const ACCOUNTING_ADVANCED = 'accounting_advanced';
```

**الخطوة 2 — توزيعه في `database/seeders/PlanSeeder.php`:**
```php
// يُضاف إلى $proFeatures و $enterpriseFeatures و $allFeatures:
PlanFeature::ACCOUNTING_ADVANCED => ['enabled' => true, 'limit' => null],

// لا يُضاف إلى: starter, basic, pos (تبقى على accounting الأساسي فقط)
```

**الخطوة 3 — إعادة الزرع:** `php artisan db:seed --class=PlanSeeder` (مركزي — updateOrCreate آمن).

| Feature | المحتوى | Starter | Pro | Enterprise | pxxx/custom |
|---------|---------|---------|-----|------------|-------------|
| `accounting` | خزينة، مصروفات، مدفوعات، P&L بسيط | ✅ | ✅ | ✅ | ✅ |
| `accounting_advanced` | دليل حسابات، قيود، تقارير GL | ❌ | ✅ | ✅ | ✅ |
| `reports_advanced` | تصدير Excel/PDF، aging | ❌ | ✅ | ✅ | ✅ |

> **تنبيه توافقية:** الباقات القديمة (`basic`, `pos`, `manufacturing`) تحصل على `accounting` فقط. للترقية، يُضاف `accounting_advanced` يدوياً أو عبر `custom_features`.

### 13.2 صلاحيات جديدة (PermissionAndRoleSeeder)

```php
$accountingPermissions = [
    // موجودة
    ['name' => 'accounting.treasury', ...],
    ['name' => 'accounting.payments', ...],
    ['name' => 'accounting.expenses', ...],
    ['name' => 'accounting.statistics', ...],

    // جديدة
    ['name' => 'accounting.dashboard', 'module' => 'accounting', 'action' => 'read'],
    ['name' => 'accounting.chart-of-accounts.read', ...],
    ['name' => 'accounting.chart-of-accounts.create', ...],
    ['name' => 'accounting.chart-of-accounts.update', ...],
    ['name' => 'accounting.journal-entries.read', ...],
    ['name' => 'accounting.journal-entries.create', ...],
    ['name' => 'accounting.journal-entries.post', ...],
    ['name' => 'accounting.journal-entries.reverse', ...],
    ['name' => 'accounting.vouchers.create', ...],
    ['name' => 'accounting.bank-accounts.manage', ...],
    ['name' => 'accounting.fiscal-periods.close', ...],
    ['name' => 'accounting.reports.trial-balance', ...],
    ['name' => 'accounting.reports.balance-sheet', ...],
    ['name' => 'accounting.reports.general-ledger', ...],
    ['name' => 'accounting.reports.aging', ...],
    ['name' => 'accounting.settings', ...],
    ['name' => 'accounting.reconciliation', ...],
];
```

### 13.3 ربط الصلاحيات في Controllers

```php
// بدل admin.only فقط:
$this->authorize('accounting.journal-entries.create');
```

---

## 14. ترحيل البيانات القديمة

> **تحذير من الازدواجية:** لا يجوز ترحيل العمليات القديمة كقيود **و** إنشاء قيد افتتاحي بالأرصدة معاً — ذلك يُكرّر الأرصدة. يجب اختيار طريقة واحدة.

### 14.1 أمر Artisan: `accounting:migrate-legacy`

**الطريقة A — قيد افتتاحي فقط (الافتراضية والمُوصى بها):**
```
--method=opening
الخطوة 1: زرع دليل الحسابات الافتراضي + الإعدادات
الخطوة 2: إنشاء السنة المالية الحالية + 12 فترة
الخطوة 3: إنشاء قيد افتتاحي واحد بالأرصدة الحالية (انظر 14.2)
الخطوة 4: التحقق: مجموع مدين = مجموع دائن
الخطوة 5: من الآن فصاعداً — كل عملية جديدة تُرحّل تلقائياً
ملاحظة: العمليات القديمة تبقى في sub-ledgers كمرجع تاريخي (لا تدخل GL)
```

**الطريقة B — ترحيل تاريخي كامل (للعملاء المتقدمين، بطيء):**
```
--method=full-history
الخطوة 1: زرع دليل الحسابات + الإعدادات
الخطوة 2: إنشاء السنة المالية (تبدأ من أول عملية)
الخطوة 3: ترحيل كل sales_invoices المؤكدة → قيود (batch)
الخطوة 4: ترحيل كل purchase_invoices المؤكدة → قيود (batch)
الخطوة 5: ترحيل كل payments/supplier_payments → قيود
الخطوة 6: ترحيل كل expenses/cash_transactions → قيود
الخطوة 7: التحقق: ميزان المراجعة متوازن
ملاحظة: لا قيد افتتاحي — الأرصدة تتكوّن من القيود نفسها
```

> **لا تجمع الطريقتين.** الـ command يرفض تشغيل الاثنين معاً.

### 14.2 قيد افتتاحي (Opening Entry)

```
مدين: 1110 (رصيد الخزينة الفعلي)
مدين: 1210 (مجموع أرصدة العملاء)
مدين: 1310 (قيمة المخزون الحالية)
دائن: 2110 (مجموع أرصدة الموردين)
دائن: 3200 (الفرق = الأرباح المحتجزة الافتتاحية)
```

### 14.3 خيارات الترحيل

| الخيار | الافتراضي | الوصف |
|--------|----------|-------|
| `--method` | `opening` | `opening` (قيد افتتاحي) أو `full-history` (ترحيل كامل) |
| `--opening-date` | أول الشهر الحالي | تاريخ القيد الافتتاحي (طريقة A) |
| `--tenant` | all | تحديد tenant معيّن |
| `--dry-run` | false | عرض ما سيحدث بدون تنفيذ |

---

## 15. ضمانات سلامة محاسبية

### 15.1 Database Constraints

```sql
-- لا قيد غير متوازن
ALTER TABLE journal_entries ADD CONSTRAINT chk_balanced
    CHECK (status = 'draft' OR total_debit = total_credit);

-- لا سطر بمدين ودائن معاً
ALTER TABLE journal_entry_lines ADD CONSTRAINT chk_line_exclusive
    CHECK (NOT (debit > 0 AND credit > 0));

-- لا قيد على حساب غير ورقي (يُطبّق في Service + DB trigger اختياري)
```

### 15.2 Idempotency

```php
// كل ترحيل تلقائي يستخدم source_event_key فريد
JournalEntry::where('source_event_key', $key)->exists()
    ? null  // تخطي — مُرحّل مسبقاً
    : $this->createAndPost(...);
```

### 15.3 Period Lock

```php
// FiscalPeriodService
public function assertOpen(Carbon $date): void
{
    $period = FiscalPeriod::where('start_date', '<=', $date)
        ->where('end_date', '>=', $date)->first();

    if (!$period) throw new NoFiscalPeriodException();
    if ($period->is_closed) throw new ClosedPeriodException();
}
```

### 15.4 أمر التحقق الدوري: `accounting:validate-integrity`

```
✓ كل القيود المعتمدة متوازنة
✓ ميزان المراجعة: مجموع مدين = مجموع دائن
✓ الميزانية: أصول = خصوم + حقوق ملكية
✓ AR: رصيد GL 1210 = Σ(customer.balance)
✓ AP: رصيد GL 2110 = Σ(supplier.balance)
✓ لا قيود على فترات مغلقة بتواريخ جديدة
✓ لا قيود مزدوجة (source_event_key فريد)
✓ المخزون GL 1310 ≈ قيمة المخزون الفعلية (تحذير لا خطأ)
```

### 15.5 Audit Trail

كل عملية: `created` | `posted` | `reversed` | `period_closed` → `accounting_audit_logs`

---

## 16. خطة التنفيذ على مراحل

---

### المرحلة 1: الأساس المحاسبي (أسبوع 1-3)

> **الهدف:** دليل حسابات + قيود يومية يدوية + فترات مالية

#### Task 1.1: Migrations الأساسية
- [ ] `account_types` + seeder بيانات ثابتة
- [ ] `accounts`
- [ ] `fiscal_years` + `fiscal_periods`
- [ ] `journal_entries` + `journal_entry_lines`
- [ ] `accounting_settings`
- [ ] `accounting_audit_logs`
- [ ] تشغيل: `php artisan tenants:migrate`

#### Task 1.2: Models + Enums
- [ ] `Account`, `AccountType`, `JournalEntry`, `JournalEntryLine`
- [ ] `FiscalYear`, `FiscalPeriod`, `AccountingSetting`, `AccountingAuditLog`
- [ ] `AccountTypeEnum`, `JournalEntryStatus`, `JournalEntrySource`, `NormalBalance`
- [ ] Relationships + Scopes (`posted`, `draft`, `leaf`, `byType`)

#### Task 1.3: Seeders
- [ ] `DefaultChartOfAccountsSeeder` — يُشغّل عند إنشاء tenant
- [ ] `AccountingSettingsSeeder` — ربط الحسابات الافتراضية
- [ ] تعديل `TenancyServiceProvider` لإضافة seeders بعد migrate

#### Task 1.4: JournalEntryService
- [ ] `create()` — مع validation
- [ ] `post()` — اعتماد
- [ ] `reverse()` — عكس
- [ ] `validateBalance()`
- [ ] `assertPeriodOpen()`
- [ ] `generateEntryNumber()`
- [ ] Unit tests لكل method

#### Task 1.5: ChartOfAccountsService + Controller
- [ ] CRUD حسابات
- [ ] Tree builder
- [ ] Validation: code فريد، leaf فقط للقيود
- [ ] Views: index (tree), create, edit

#### Task 1.6: JournalEntryController
- [ ] index (قائمة + فلاتر)
- [ ] create (نموذج ديناميكي — Alpine.js)
- [ ] show (عرض + طباعة)
- [ ] post (اعتماد)
- [ ] reverse (عكس)
- [ ] Form Request: `StoreJournalEntryRequest`

#### Task 1.7: FiscalPeriodService + Controller
- [ ] إنشاء سنة مالية + 12 فترة
- [ ] إغلاق فترة
- [ ] Views

#### Task 1.8: AccountingSettings
- [ ] Controller + View
- [ ] ربط الحسابات الافتراضية

#### Task 1.9: Routes + Navigation
- [ ] إضافة routes في `tenant.php`
- [ ] تحديث `layouts/app.blade.php`
- [ ] إضافة `accounting_advanced` في `PlanFeature` + `PlanSeeder`

**معيار القبول المرحلة 1:**
- [ ] إنشاء دليل حسابات وعرضه شجرياً
- [ ] إنشاء قيد يدوي متوازن واعتماده
- [ ] عكس قيد معتمد
- [ ] رفض قيد على فترة مغلقة
- [ ] رفض قيد غير متوازن
- [ ] ميزان مراجعة يظهر أرصدة صحيحة

---

### المرحلة 2: الخزينة والبنوك (أسبوع 4-5)

> **الهدف:** تطوير الخزينة + حسابات بنكية + سندات قبض/صرف

#### Task 2.1: Bank Accounts
- [ ] Migration `bank_accounts`
- [ ] Model + Service + Controller
- [ ] ربط كل بنك بحساب GL فرعي تحت 1120

#### Task 2.2: TreasuryService (تطوير)
- [ ] استبدال `AccountingService` تدريجياً
- [ ] ربط `cash_transactions` بـ `journal_entry_id`
- [ ] رصيد الخزينة من GL بدل SUM مباشر

#### Task 2.3: Receipt Voucher (سند قبض)
- [ ] `ReceiptVoucherController` + Service
- [ ] ربط بعميل + فواتير + Payment + JournalEntry
- [ ] View

#### Task 2.4: Payment Voucher (سند صرف)
- [ ] `PaymentVoucherController` + Service
- [ ] ربط بمورد/مصروف + JournalEntry
- [ ] تفعيل `SupplierPaymentController` routes

#### Task 2.5: تطوير Views الموجودة
- [ ] `treasury.blade.php` — عرض رصيد GL + حركات
- [ ] `expenses.blade.php` — ربط بحسابات المصروفات
- [ ] `payments.blade.php` — عرض القيود المرتبطة

**معيار القبول المرحلة 2:**
- [ ] إنشاء حساب بنكي وربطه بـ GL
- [ ] سند قبض ينشئ Payment + JournalEntry
- [ ] سند صرف ينشئ SupplierPayment + JournalEntry
- [ ] رصيد الخزينة = رصيد حساب 1110 في GL

---

### المرحلة 3: الترحيل التلقائي (أسبوع 6-7)

> **الهدف:** ربط كل العمليات التجارية بالحسابات تلقائياً

#### Task 3.1: PostingService
- [ ] `postSalesInvoice()` — نقدي + آجل
- [ ] `postSalesInvoiceCogs()`
- [ ] `postPurchaseInvoice()`
- [ ] `postSalesReturn()`
- [ ] `postPayment()`
- [ ] `postSupplierPayment()`
- [ ] `postExpense()`
- [ ] `reverseBySource()`

#### Task 3.2: Listeners
- [ ] `PostSalesInvoiceToLedger`
- [ ] `PostPurchaseInvoiceToLedger`
- [ ] `PostSalesReturnToLedger`
- [ ] `PostPaymentToLedger`
- [ ] `PostSupplierPaymentToLedger`
- [ ] `PostExpenseToLedger`
- [ ] `ReverseInvoiceJournalEntry`
- [ ] `LogAccountingAudit`
- [ ] تسجيل الكل في `EventServiceProvider`

#### Task 3.3: AccountBalanceService
- [ ] `getBalance()`
- [ ] `getAllBalances()`
- [ ] `reconcileCustomerBalance()`
- [ ] `reconcileSupplierBalance()`

#### Task 3.4: تحديث كشوف الحساب
- [ ] `CustomerController::statement` — من GL
- [ ] `SupplierController::statement` — من GL

#### Task 3.5: Idempotency Tests
- [ ] إطلاق نفس الحدث مرتين → قيد واحد فقط
- [ ] إلغاء فاتورة → قيد عكسي صحيح

**معيار القبول المرحلة 3:**
- [ ] فاتورة مبيعات مؤكدة → قيد إيراد + COGS تلقائي
- [ ] فاتورة مشتريات → قيد مخزون + AP
- [ ] دفعة عميل → قيد قبض
- [ ] إلغاء فاتورة → قيد عكسي
- [ ] لا ترحيل مزدوج
- [ ] AR balance = مجموع أرصدة العملاء

---

### المرحلة 4: التقارير المالية (أسبوع 8-9)

> **الهدف:** تقارير محاسبية كاملة معتمدة

#### Task 4.1: FinancialReportService
- [ ] `trialBalance()`
- [ ] `incomeStatement()`
- [ ] `balanceSheet()`
- [ ] `generalLedger()`
- [ ] `accountStatement()`
- [ ] `cashFlowStatement()`
- [ ] `agingReport()`

#### Task 4.2: ReportingController — إضافات
- [ ] `trialBalance` + view
- [ ] `balanceSheet` + view
- [ ] `generalLedger` + view
- [ ] `accountStatement` + view
- [ ] `cashFlow` + view
- [ ] `aging` + view

#### Task 4.3: تطوير P&L الموجود
- [ ] `profitLossReport()` يعتمد على GL بدل SUM مباشر
- [ ] فصل COGS عن المصروفات
- [ ] إضافة مرتجعات المبيعات

#### Task 4.4: Export
- [ ] Excel export لكل تقرير (Maatwebsite/Excel)
- [ ] PDF print-friendly views

#### Task 4.5: Dashboard
- [ ] `AccountingDashboardController`
- [ ] Widgets + Charts (Chart.js)

**معيار القبول المرحلة 4:**
- [ ] ميزان مراجعة متوازن
- [ ] قائمة دخل: صافي الربح = إيرادات − COGS − مصروفات
- [ ] ميزانية عمومية: أصول = خصوم + حقوق ملكية
- [ ] دفتر أستاذ يطابق كشف حساب
- [ ] تصدير Excel يعمل

---

### المرحلة 5: متقدم (أسبوع 10-12)

> **الهدف:** مطابقة بنكية، أعمار ديون، إغلاق سنوي، ترحيل بيانات قديمة

#### Task 5.1: Bank Reconciliation
- [ ] Migration + Model + Service + Controller
- [ ] View: مطابقة كشف بنكي مع GL

#### Task 5.2: Period Closing
- [ ] إغلاق فترة شهرية
- [ ] إغلاق سنة مالية + قيد إقفال
- [ ] قيد افتتاحي للسنة الجديدة

#### Task 5.3: Legacy Migration
- [ ] `accounting:migrate-legacy` command
- [ ] `--dry-run` support
- [ ] تقرير نتائج الترحيل

#### Task 5.4: Integrity Command
- [ ] `accounting:validate-integrity`
- [ ] جدولة يومية (Scheduler)

#### Task 5.5: Cost Centers (اختياري)
- [ ] Migration + Model
- [ ] ربط بسطور القيود
- [ ] تقرير ربحية بالمركز

#### Task 5.6: Permissions Wiring
- [ ] ربط كل routes بالصلاحيات
- [ ] تحديث `PermissionsController` UI

#### Task 5.7: POS Shift Reconciliation
- [ ] قيد تسوية عند إغلاق وردية

**معيار القبول المرحلة 5:**
- [ ] مطابقة بنكية تعمل
- [ ] إغلاق سنة مالية + قيد افتتاحي
- [ ] ترحيل بيانات قديمة بنجاح
- [ ] `accounting:validate-integrity` يمر بدون أخطاء

---

## 17. اختبارات التحقق المحاسبي

### 17.1 Unit Tests

```php
// tests/Unit/Accounting/JournalEntryServiceTest.php

test('rejects unbalanced entry');
test('rejects entry on closed period');
test('rejects entry on non-leaf account');
test('posts balanced entry successfully');
test('reversal creates opposite entry');
test('reversal marks original as reversed');
test('idempotent posting skips duplicate');
test('entry number is sequential');
```

### 17.2 Feature Tests

```php
// tests/Feature/Accounting/

test('sales invoice cash creates correct journal entry');
test('sales invoice credit creates AR entry');
test('sales invoice creates COGS entry');
test('purchase invoice creates inventory entry not expense');
test('payment reduces AR balance');
test('cancelled invoice creates reversal entry');
test('trial balance is always balanced');
test('balance sheet equation holds');
test('income statement net income matches equity change');
test('customer statement matches GL account');
test('legacy migration produces balanced opening entry');
```

### 17.3 سيناريوهات اختبار يدوية (UAT)

| # | السيناريو | الخطوات | النتيجة المتوقعة |
|---|----------|---------|-----------------|
| 1 | بيع نقدي POS | بيع 1000 + ضريبة 140 | مدين صندوق 1140 / دائن إيراد 1000 + VAT 140 |
| 2 | بيع آجل | فاتورة 5000 لعميل | مدين AR 5000 / دائن إيراد |
| 3 | قبض من عميل | دفع 3000 | مدين صندوق / دائن AR |
| 4 | شراء مخزون | فاتورة مشتريات 2000 | مدين مخزون / دائن AP |
| 5 | دفع مورد | سداد 2000 | مدين AP / دائن صندوق |
| 6 | مصروف إيجار | 5000 | مدين مصروف / دائن صندوق |
| 7 | مرتجع مبيعات | 200 | عكس قيد البيع |
| 8 | إلغاء فاتورة | إلغاء فاتورة مؤكدة | قيد عكسي |
| 9 | قيد يدوي | مصروف + صندوق | قيد متوازن معتمد |
| 10 | فترة مغلقة | قيد بتاريخ الشهر المغلق | رفض |
| 11 | ميزان مراجعة | بعد كل العمليات | مدين = دائن |
| 12 | ميزانية عمومية | بعد كل العمليات | أصول = خصوم + حقوق ملكية |

---

## 18. مخاطر ومعالجاتها

| # | الخطر | الاحتمال | التأثير | المعالجة |
|---|-------|---------|---------|----------|
| 1 | ترحيل مزدوج للفواتير | متوسط | قيود مكررة | `source_event_key` UNIQUE |
| 2 | COGS خاطئ | عالي | أرباح مضللة | `unit_cost_snapshot` من inventory_movements |
| 3 | بيانات قديمة غير متسقة | عالي | ميزان غير متوازن | `migrate-legacy` + opening entry |
| 4 | تعديل فاتورة بعد الترحيل | متوسط | قيود لا تطابق | عكس + إعادة ترحيل |
| 5 | أداء التقارير | متوسط | بطء | Indexes + caching + aggregate |
| 6 | صلاحيات غير مربوطة | منخفض | وصول غير مصرح | Policies + authorize |
| 7 | Schema drift (Expense) | عالي | أخطاء runtime | توحيد migration + model |
| 8 | فترة مالية غير معرّفة | متوسط | رفض كل القيود | Auto-create fiscal year on seed |

---

## 19. ملحق: قائمة الملفات

### ملفات جديدة (تقريبي: ~65 ملف)

| النوع | العدد |
|-------|-------|
| Migrations | 14 |
| Models | 12 |
| Services | 10 |
| Controllers | 9 |
| Form Requests | 7 |
| Listeners | 9 |
| Events | 3 |
| Policies | 3 |
| Enums | 4 |
| Seeders | 2 |
| Views | ~25 |
| Tests | ~15 |
| Commands | 3 |

### ملفات معدّلة

| الملف | التعديل |
|-------|---------|
| `routes/tenant.php` | إضافة routes المحاسبة |
| `app/Providers/EventServiceProvider.php` | تسجيل listeners |
| `app/Providers/TenancyServiceProvider.php` | seeders عند إنشاء tenant |
| `app/Models/PlanFeature.php` | `ACCOUNTING_ADVANCED` |
| `database/seeders/PlanSeeder.php` | الباقات |
| `database/seeders/PermissionAndRoleSeeder.php` | صلاحيات جديدة |
| `resources/views/layouts/app.blade.php` | قائمة جانبية |
| `app/Services/ReportingService.php` | P&L من GL |
| `app/Http/Controllers/CustomerController.php` | statement من GL |
| `app/Http/Controllers/SupplierController.php` | statement من GL |
| `app/Http/Controllers/ReportingController.php` | تقارير جديدة |

---

## ملاحظات ختامية

1. **ابدأ بالمرحلة 1 فقط** — لا تنتقل للمرحلة 2 قبل اجتياز معايير القبول.
2. **كل مرحلة = PR منفصل** — سهولة المراجعة والاختبار.
3. **شغّل `accounting:validate-integrity` بعد كل مرحلة.**
4. **لا تعدّل قيوداً معتمدة أبداً** — عكس فقط.
5. **المشتريات ≠ مصروفات** — هذا أكثر خطأ محاسبي شائع يجب تجنبه.

---

*آخر تحديث: 2026-07-03 | الإصدار 2.1 (بعد Senior QA + Code Verification — القيود متوازنة والافتراضات متحقَّقة من الكود)*
