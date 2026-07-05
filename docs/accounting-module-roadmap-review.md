# تقييم خطة المحاسبة + Roadmap للإنتاج — Magzani SaaS

> **التاريخ:** 2026-07-03  
> **النوع:** مراجعة فنية ومحاسبية + خريطة طريق تكميلية  
> **المرجع:** `docs/accounting-module-implementation-plan.md` v1.0

---

## الجزء الأول: تقييم الخطة الحالية

### التقييم العام

| المحور | الدرجة | ملاحظات |
|--------|--------|---------|
| المبادئ المحاسبية | 9/10 | قوية — Double-Entry, Accrual, Perpetual |
| تصميم DB | 8/10 | جيد مع ثغرات يجب سدّها |
| قواعد الترحيل | 7/10 | فيها أخطاء محاسبية وسيناريوهات ناقصة |
| المعمارية التقنية | 9/10 | متوافقة تماماً مع المشروع |
| التكامل مع الموجود | 8/10 | جيد لكن ناقص Manufacturing |
| التقارير | 8/10 | تغطية جيدة — ناقص comparative |
| Production Readiness | 6/10 | فجوات كبيرة في concurrency, error recovery, monitoring |
| SaaS Gating | 9/10 | مدروس جيداً |
| الاختبارات | 7/10 | محتاجة توسيع كبير |

**التقييم الإجمالي: 7.9/10** — خطة ممتازة كأساس لكن محتاجة تعديلات جوهرية قبل البدء.

---

## الجزء الثاني: أخطاء وثغرات في الدورة المحاسبية (Accounting Cycle Bugs)

### خطأ #1: خلط خصم المبيعات مع المرتجعات (Critical)

**الموقع:** القسم 8.1 — فاتورة مبيعات نقدي

**المشكلة:**
```
السطر 2: 4900 مرتجعات مبيعات │ discount │
```

الخصم **ليس** مرتجع. استخدام حساب 4900 (مرتجعات) لتسجيل الخصم خطأ محاسبي.

**الحل:**
```
إضافة حساب 4800 — خصم مبيعات مسموح به (Sales Discounts Allowed)
نوعه: contra-revenue — يُخصم من الإيرادات
```

**القيد الصحيح لفاتورة مبيعات نقدي بخصم:**
```
مدين: 1110 الصندوق           │ total (المحصّل فعلياً)
مدين: 4800 خصم مبيعات        │ discount_amount
دائن: 4100 إيرادات المبيعات  │ subtotal (قبل الخصم)
دائن: 2210 VAT مستحق         │ tax_amount
```

---

### خطأ #2: COGS مفقود في البيع النقدي (Critical)

**الموقع:** القسم 8.1

**المشكلة:** قيد COGS مذكور فقط في 8.2 (الآجل) لكن **غير موجود** في 8.1 (النقدي). كل بيع يجب أن ينشئ قيد COGS بغض النظر عن طريقة الدفع.

**الحل:** إضافة قيد COGS لكل أنواع فواتير المبيعات:
```
مدين: 5100 COGS      │ cogs
دائن: 1310 المخزون   │ cogs
```

---

### خطأ #3: المبيعات الجزئية (Partial Payment at Confirmation) — غير مُعالَج

**الموقع:** القسم 8.3

**المشكلة:** الخطة تقول "كالمبيعات الآجلة" لكن الواقع مختلف. عند تأكيد فاتورة `paid = 3000` و `total = 5000`:

- الحالي لو عاملناه آجل كامل: `1210 (AR) = 5000` — خطأ لأن 3000 دُفعت فعلاً
- لازم نفصل الفوري عن الآجل

**الحل — قيدان في نفس اللحظة:**
```
قيد الفاتورة:
  مدين: 1110 الصندوق    │ 3000 (paid)
  مدين: 1210 ذمم مدينة  │ 2000 (remaining)
  دائن: 4100 إيرادات    │ subtotal
  دائن: 2210 VAT       │ tax_amount
```

---

### خطأ #4: shipping_cost و other_charges غير مُعالَجة

**الموقع:** كل قواعد الترحيل

**المشكلة:** `SalesInvoice` يحتوي على `shipping_cost` و `other_charges` ولكن قواعد الترحيل تتعامل فقط مع `subtotal + tax`. هذه المبالغ يجب أن تُسجّل في حسابات منفصلة.

**الحل:**
```
إضافة حسابات:
  4300 — إيرادات شحن (إذا العميل يدفع الشحن)
  5400 — مصروفات شحن (إذا المنشأة تتحمل)
  4400 — إيرادات رسوم أخرى

القيد يصبح:
  مدين: 1110/1210        │ total (شامل الشحن والرسوم)
  دائن: 4100             │ subtotal (بدون شحن)
  دائن: 4300             │ shipping_cost
  دائن: 4400             │ other_charges
  دائن: 2210             │ tax_amount
```

---

### خطأ #5: مرتجع المشتريات (Purchase Return) مفقود

**الموقع:** القسم 8 — غير موجود

**المشكلة:** يوجد `PurchaseReturn` و `PurchaseReturnProcessed` event في الكود لكن الخطة لا تحتوي على قاعدة ترحيل لمرتجع المشتريات.

**الحل — إضافة القسم 8.5b:**
```
الحدث: PurchaseReturnProcessed
مفتاح: purchase_return:{id}:processed

  مدين: 2110 ذمم دائنة (موردين) │ total    (party: supplier_id)
  دائن: 1310 المخزون             │ subtotal
  دائن: 1320 VAT مدفوع          │ tax_amount
```

---

### خطأ #6: إيداع الخزينة يُسجّل كإيراد — خطأ

**الموقع:** القسم 8.9

**المشكلة:**
```
إيداع:
  مدين: 1110 الصندوق     │ amount
  دائن: 4200 إيرادات أخرى │ amount
```

الإيداع في الخزينة **ليس بالضرورة إيراد**. قد يكون:
- إيداع رأس مال (حقوق ملكية)
- تحويل من بنك (أصل ↔ أصل)
- قرض (خصم)
- إيراد آخر (فعلاً إيراد)

**الحل:** الإيداع/السحب يدوي يجب أن يطلب من المستخدم **اختيار الحساب المقابل** بدلاً من افتراض إيراد/مصروف.

```
إيداع:
  مدين: 1110 الصندوق        │ amount
  دائن: [حساب يختاره المستخدم] │ amount

سحب:
  مدين: [حساب يختاره المستخدم] │ amount
  دائن: 1110 الصندوق        │ amount
```

---

### خطأ #7: إغلاق الفترة المالية — قيد الإقفال ناقص

**الموقع:** القسم 8.10

**المشكلة:** القيد يُقفل الإيرادات والمصروفات في خطوة واحدة. الصحيح محاسبياً:

1. إقفال **كل** حسابات الإيرادات فردياً إلى ملخص الدخل
2. إقفال **كل** حسابات المصروفات فردياً إلى ملخص الدخل
3. ترحيل صافي ملخص الدخل إلى الأرباح المحتجزة

**الحل:**
```
إضافة حساب: 3250 — ملخص الدخل (Income Summary) - مؤقت

قيد 1 — إقفال الإيرادات:
  مدين: 4100 إيرادات المبيعات  │ balance
  مدين: 4200 إيرادات أخرى      │ balance
  دائن: 3250 ملخص الدخل        │ total revenues

قيد 2 — إقفال المصروفات:
  مدين: 3250 ملخص الدخل │ total expenses
  دائن: 5100 COGS       │ balance
  دائن: 52xx كل مصروف   │ balance

قيد 3 — ترحيل الربح:
  مدين: 3250 ملخص الدخل   │ net_income
  دائن: 3200 أرباح محتجزة │ net_income
  (أو العكس إذا خسارة)
```

---

### خطأ #8: التصنيع (Manufacturing) غير مُغطّى

**الموقع:** القسم 8 + 11

**المشكلة:** المشروع يحتوي على `ManufacturingOrderService::completeOrder()` الذي:
1. يسحب مواد خام من المخزون
2. يُدخل منتج تام الصنع بتكلفة `cost_per_unit`

لكن الخطة المحاسبية **لا تذكر التصنيع أبداً**. هذا يعني أن حركات التصنيع لن تنعكس في GL.

**الحل — إضافة حسابات وقواعد:**
```
حسابات جديدة:
  1350 — إنتاج تحت التشغيل (WIP - Work in Progress)
  5150 — تكلفة المواد الخام المستخدمة

عند تأكيد أمر التصنيع (مواد خام → WIP):
  مدين: 1350 WIP           │ material_cost
  دائن: 1310 المخزون       │ material_cost

عند إكمال أمر التصنيع (WIP → منتج تام):
  مدين: 1310 المخزون       │ total_cost (cost_per_unit × quantity)
  دائن: 1350 WIP           │ total_cost
```

---

### خطأ #9: ترحيل البيانات القديمة — ازدواجية

**الموقع:** القسم 14.1

**المشكلة:** الخطوات 3-7 (ترحيل cash_transactions, expenses, payments, invoices) **تتعارض** مع الخطوة 8 (قيد افتتاحي). إذا رحّلت كل العمليات القديمة كقيود **+** أنشأت قيد افتتاحي بأرصدة، ستكون هناك ازدواجية.

**الحل — يجب الاختيار بين طريقتين:**

**الطريقة A: قيد افتتاحي فقط (مُوصى به)**
```
الخطوة 1: زرع الدليل
الخطوة 2: إنشاء السنة + الفترات
الخطوة 3: قيد افتتاحي بأرصدة فعلية (صندوق + عملاء + مخزون + موردين + ...)
الخطوة 4: من اليوم فصاعداً — كل عملية جديدة تُرحّل تلقائياً
ملاحظة: العمليات القديمة تبقى في sub-ledgers كمرجع لكن لا تدخل GL
```

**الطريقة B: ترحيل تاريخي كامل (معقد + بطيء)**
```
ترحيل كل عملية قديمة → بدون قيد افتتاحي
الخطر: بطء شديد للـ tenants ذات البيانات الكثيرة
```

---

### خطأ #10: `source_event_key` UNIQUE لا يكفي لمنع race conditions

**الموقع:** القسم 15.2

**المشكلة:** في بيئة Queue مع ShouldQueue listeners، ممكن نفس الحدث يتنفّذ بالتوازي (retry, duplicate dispatch). الـ UNIQUE constraint سيعطي DB error بدل تعامل سلس.

**الحل:**
```php
// استخدام DB::transaction + SELECT FOR UPDATE أو Laravel atomic lock
Cache::lock("posting:{$eventKey}", 10)->block(5, function () use (...) {
    if (JournalEntry::where('source_event_key', $eventKey)->exists()) {
        return null;
    }
    return $this->createAndPost(...);
});
```

---

## الجزء الثالث: فجوات Production Readiness

### 3.1 Concurrency & Locking (غير موجود)

| المشكلة | التأثير | الحل |
|---------|---------|------|
| `generateEntryNumber()` — race condition | أرقام مكررة | `SELECT MAX(...) FOR UPDATE` + retry |
| Parallel posting listeners | duplicate entries | Atomic lock per event key |
| Period closing while posting | entries in limbo | Pessimistic lock on fiscal_period |
| Balance calculation during posting | stale reads | READ COMMITTED + re-verify at post |

### 3.2 Queue Error Recovery (غير موجود)

| المشكلة | التأثير | الحل |
|---------|---------|------|
| Listener fails after partial work | GL inconsistency | DB::transaction في كل listener |
| Queue retry → duplicate posting | double entries | Idempotency key + lock |
| Dead letter queue | lost journal entries | DLQ monitoring + manual review page |
| Event dispatched, listener never runs | sub-ledger ≠ GL | Daily reconciliation job |

### 3.3 Performance (محتاج benchmarks)

| الـ Query | الخطر | الحل |
|-----------|-------|------|
| `getAllBalances()` — full table scan | بطء O(n) مع نمو القيود | **Materialized balance table** أو summary cache |
| Trial Balance لآلاف الحسابات | timeout | Paginated + cached daily |
| General Ledger لحساب نشط جداً | memory | Cursor-based pagination |
| Legacy migration لـ 100k+ records | timeout | Batch processing + progress tracking |

**التوصية الهامة:** إضافة جدول `account_balances` (materialized):
```sql
CREATE TABLE account_balances (
    account_id     BIGINT UNSIGNED PRIMARY KEY,
    debit_total    DECIMAL(15,2) NOT NULL DEFAULT 0,
    credit_total   DECIMAL(15,2) NOT NULL DEFAULT 0,
    balance        DECIMAL(15,2) NOT NULL DEFAULT 0,
    last_entry_id  BIGINT UNSIGNED NULL,
    updated_at     TIMESTAMP
);
-- يُحدَّث عند كل post/reverse عبر trigger أو service
-- يُستخدم للـ dashboard + trial balance الفوري
-- يُعاد حسابه بالكامل عبر accounting:rebuild-balances
```

### 3.4 Monitoring & Alerting (غير موجود)

| ماذا نراقب | الآلية |
|------------|--------|
| ميزان غير متوازن | Scheduled job يومي + Slack/email alert |
| فرق GL vs sub-ledger | `accounting:validate-integrity` + notification |
| Queue lag (listeners متأخرة) | Horizon dashboard أو custom metric |
| فشل ترحيل متكرر | Error count threshold → alert |
| حساب بنك سلبي | Real-time check at posting |

### 3.5 Backup & Recovery (غير موجود)

| السيناريو | الخطة |
|-----------|-------|
| Legacy migration فشلت في المنتصف | `--dry-run` + snapshot before + rollback transaction |
| Tenant يطلب حذف كل GL | `accounting:reset --tenant=X` (يحتاج confirmation) |
| DB corruption | Point-in-time recovery + `accounting:rebuild-balances` |

### 3.6 Tenant Onboarding Wizard (غير موجود)

عند تفعيل `accounting_advanced` لأول مرة لـ tenant موجود:
```
Step 1: مراجعة دليل الحسابات الافتراضي (تعديل أسماء)
Step 2: إعداد السنة المالية (تاريخ بداية)
Step 3: إدخال أرصدة افتتاحية يدوياً أو استيراد
Step 4: اختيار الترحيل التلقائي (on/off per source)
Step 5: تشغيل مطابقة أولية
```

---

## الجزء الرابع: فيتشرز ناقصة (Missing Features for Production)

### 4.1 فيتشرز أساسية مطلوبة (Must-Have)

| # | الفيتشر | السبب | المرحلة المقترحة |
|---|---------|-------|-----------------|
| 1 | **حساب خصم مبيعات** (4800) | خطأ محاسبي بدونه | Phase 1 (DB) |
| 2 | **حسابات shipping/charges** (4300/4400) | بيانات موجودة في الفواتير | Phase 1 (DB) |
| 3 | **ترحيل Manufacturing** (WIP 1350) | موديول موجود وشغال | Phase 3 |
| 4 | **ترحيل مرتجع مشتريات** | Event موجود | Phase 3 |
| 5 | **Materialized balances table** | أداء + real-time dashboard | Phase 1 (DB) |
| 6 | **Concurrency locks** | سلامة بيانات | Phase 1 (Service) |
| 7 | **Queue failure handling** | reliability | Phase 3 |
| 8 | **Tenant onboarding wizard** | UX لـ existing tenants | Phase 2 |
| 9 | **حسابات فرعية لكل عميل/مورد** (sub-ledger) | تفصيل AR/AP | Phase 3 |
| 10 | **خصم المشتريات المكتسب** (2300) | لو في خصم من المورد | Phase 3 |
| 11 | **Rounding account** (5295) | فروقات تقريب | Phase 1 (DB) |
| 12 | **ربط journal_entry_id بـ sales_invoices** | تتبع عكسي | Phase 3 |

### 4.2 فيتشرز مهمة (Should-Have)

| # | الفيتشر | الوصف | المرحلة |
|---|---------|-------|---------|
| 13 | **Recurring Journal Entries** | قيود شهرية متكررة (إيجار، رواتب) | Phase 4 |
| 14 | **Cheque Lifecycle** | شيك مستلم → إيداع → تحصيل/ارتجاع | Phase 5 |
| 15 | **Advance Payments** (عربون) | دفعات مقدمة قبل الفاتورة | Phase 5 |
| 16 | **VAT Settlement** | تسوية ضريبة (Input vs Output) | Phase 5 |
| 17 | **Comparative Reports** | هذه الفترة vs السابقة | Phase 4 |
| 18 | **Budget Module** | ميزانيات تقديرية vs فعلي | Phase 6 |
| 19 | **Financial Ratios** | نسب سيولة، ربحية، نشاط | Phase 4 |
| 20 | **Multi-branch P&L** | ربحية لكل مستودع/فرع | Phase 5 |
| 21 | **Depreciation Engine** | إهلاك أصول ثابتة شهري | Phase 6 |
| 22 | **Employee Advances** | سلف وعهد موظفين | Phase 6 |

### 4.3 فيتشرز اختيارية (Nice-to-Have)

| # | الفيتشر | الوصف | المرحلة |
|---|---------|-------|---------|
| 23 | **Chart of Accounts Import** | CSV/Excel import | Phase 5 |
| 24 | **Custom Report Builder** | Drag-drop report designer | Phase 7 |
| 25 | **External Auditor View** | Read-only portal للمراجع الخارجي | Phase 7 |
| 26 | **Bank API Integration** | جلب كشف بنكي تلقائي | Phase 7 |
| 27 | **Multi-Currency** | عملات متعددة + فروقات صرف | Phase 7 |
| 28 | **E-Invoicing** | ربط مع ZATCA/Tax Authority | Phase 7 |
| 29 | **Petty Cash** | إدارة صناديق نثرية | Phase 6 |
| 30 | **Payment Terms** | شروط دفع (net30, 2/10 net30) | Phase 5 |
| 31 | **Auto-reminder for overdue** | تنبيه تلقائي للفواتير المتأخرة | Phase 5 |

---

## الجزء الخامس: تعديلات مطلوبة على قاعدة البيانات

### 5.1 حسابات ناقصة في الدليل الافتراضي

```
إضافات مطلوبة:
──────────────────────────────────────────────────────────────
  1350 │ إنتاج تحت التشغيل (WIP)       │ أصول  │ نعم │ نعم  ← wip_account
  1400 │ دفعات مقدمة لموردين            │ أصول  │ نعم │ نعم
  2120 │ دفعات مقدمة من عملاء           │ خصوم  │ نعم │ نعم
  2300 │ خصم مشتريات مكتسب             │ خصوم  │ نعم │ لا
  3250 │ ملخص الدخل (مؤقت)             │ حقوق  │ نعم │ نعم  ← income_summary
  4300 │ إيرادات شحن                    │ إيرادات│ نعم │ نعم
  4400 │ إيرادات رسوم أخرى             │ إيرادات│ نعم │ لا
  4800 │ خصم مبيعات مسموح به           │ إيرادات│ نعم │ نعم  (contra) ← sales_discount
  5150 │ تكلفة مواد خام مستخدمة        │ مصروفات│ نعم │ نعم
  5295 │ فروقات تقريب                   │ مصروفات│ نعم │ نعم  ← rounding_account
  5400 │ مصروفات شحن                    │ مصروفات│ نعم │ لا
```

### 5.2 أعمدة ناقصة في `accounting_settings`

```sql
-- إضافات مطلوبة
wip_account_id              BIGINT UNSIGNED NULL,  -- 1350
income_summary_account_id   BIGINT UNSIGNED NULL,  -- 3250
sales_discount_account_id   BIGINT UNSIGNED NULL,  -- 4800
shipping_revenue_account_id BIGINT UNSIGNED NULL,  -- 4300
rounding_account_id         BIGINT UNSIGNED NULL,  -- 5295
advance_customer_account_id BIGINT UNSIGNED NULL,  -- 2120
advance_supplier_account_id BIGINT UNSIGNED NULL,  -- 1400
```

### 5.3 جدول إضافي: `account_balances` (Materialized)

```sql
CREATE TABLE account_balances (
    account_id      BIGINT UNSIGNED PRIMARY KEY,
    period_debit    DECIMAL(15,2) NOT NULL DEFAULT 0,  -- حركة الفترة مدين
    period_credit   DECIMAL(15,2) NOT NULL DEFAULT 0,  -- حركة الفترة دائن
    ytd_debit       DECIMAL(15,2) NOT NULL DEFAULT 0,  -- من بداية السنة مدين
    ytd_credit      DECIMAL(15,2) NOT NULL DEFAULT 0,  -- من بداية السنة دائن
    balance         DECIMAL(15,2) NOT NULL DEFAULT 0,  -- الرصيد الحالي
    last_entry_id   BIGINT UNSIGNED NULL,
    last_entry_date DATE NULL,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (account_id) REFERENCES accounts(id)
);
```

### 5.4 عمود إضافي: `journal_entry_id` في `sales_invoices`

```sql
ALTER TABLE sales_invoices
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL,
    ADD COLUMN cogs_entry_id BIGINT UNSIGNED NULL;

ALTER TABLE purchase_invoices
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;

ALTER TABLE sales_returns
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;

ALTER TABLE purchase_returns
    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
```

### 5.5 جدول: `recurring_journal_entries`

```sql
CREATE TABLE recurring_journal_entries (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    template_name       VARCHAR(200) NOT NULL,
    description         TEXT NOT NULL,
    frequency           ENUM('daily','weekly','monthly','quarterly','yearly') NOT NULL,
    next_run_date       DATE NOT NULL,
    last_run_date       DATE NULL,
    end_date            DATE NULL,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    auto_post           BOOLEAN NOT NULL DEFAULT FALSE,
    created_by          BIGINT UNSIGNED NULL,
    created_at          TIMESTAMP,
    updated_at          TIMESTAMP
);

CREATE TABLE recurring_journal_entry_lines (
    id                          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    recurring_journal_entry_id  BIGINT UNSIGNED NOT NULL,
    line_number                 SMALLINT UNSIGNED NOT NULL,
    account_id                  BIGINT UNSIGNED NOT NULL,
    debit                       DECIMAL(15,2) NOT NULL DEFAULT 0,
    credit                      DECIMAL(15,2) NOT NULL DEFAULT 0,
    description                 VARCHAR(500) NULL,

    FOREIGN KEY (recurring_journal_entry_id)
        REFERENCES recurring_journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);
```

---

## الجزء السادس: Roadmap مُعدَّل ومُوسَّع

### Overview — 7 مراحل (16 أسبوع)

```
Phase 1: الأساس المحاسبي (أسبوع 1-3)         ← مُعدَّل
Phase 2: الخزينة والبنوك (أسبوع 4-5)          ← مُعدَّل
Phase 3: الترحيل التلقائي الكامل (أسبوع 6-8)  ← مُوسَّع (كان 6-7)
Phase 4: التقارير المالية (أسبوع 9-10)         ← مُعدَّل
Phase 5: Production Hardening (أسبوع 11-12)    ← جديد بالكامل
Phase 6: متقدم (أسبوع 13-14)                  ← مُحدَّث
Phase 7: مستقبلي (أسبوع 15-16+)               ← جديد
```

---

### Phase 1: الأساس المحاسبي (أسبوع 1-3) — مُعدَّل

**الإضافات عن الخطة الأصلية:**

- [ ] **FIX:** إضافة الحسابات الناقصة للدليل (4800, 4300, 4400, 1350, 3250, 5295)
- [ ] **FIX:** جدول `account_balances` materialized
- [ ] **FIX:** أعمدة إضافية في `accounting_settings`
- [ ] **NEW:** Entry number generation مع pessimistic lock
- [ ] **NEW:** `AccountBalanceService::updateOnPost()` — يحدّث materialized balance
- [ ] **NEW:** Database CHECK constraints تشمل rounding tolerance (±0.01)
- [ ] **NEW:** Exception classes: `UnbalancedEntryException`, `ClosedPeriodException`, `NonLeafAccountException`, `DuplicatePostingException`
- [ ] **NEW:** `accounting:rebuild-balances` command (لإعادة حساب materialized balances)

**ملفات إضافية:**
```
database/migrations/tenant/2026_07_02_000015_create_account_balances_table.php
app/Exceptions/Accounting/UnbalancedEntryException.php
app/Exceptions/Accounting/ClosedPeriodException.php
app/Exceptions/Accounting/NonLeafAccountException.php
app/Exceptions/Accounting/DuplicatePostingException.php
app/Console/Commands/AccountingRebuildBalances.php
```

---

### Phase 2: الخزينة والبنوك (أسبوع 4-5) — مُعدَّل

**الإضافات:**

- [ ] **FIX:** إيداع/سحب يطلب اختيار الحساب المقابل (ليس hardcoded إيراد/مصروف)
- [ ] **NEW:** Tenant Onboarding Wizard (عند أول تفعيل accounting_advanced)
- [ ] **NEW:** `AccountingSetupController` — wizard steps
- [ ] **NEW:** Opening balance entry UI (إدخال يدوي بواجهة سهلة)

**ملفات إضافية:**
```
app/Http/Controllers/Accounting/AccountingSetupController.php
resources/views/accounting/setup/
    step1-chart.blade.php
    step2-fiscal-year.blade.php
    step3-opening-balances.blade.php
    step4-auto-posting.blade.php
    step5-verify.blade.php
```

---

### Phase 3: الترحيل التلقائي الكامل (أسبوع 6-8) — مُوسَّع

**الإضافات عن الخطة الأصلية:**

- [ ] **FIX:** Partial payment at confirmation (split cash + AR)
- [ ] **FIX:** shipping_cost + other_charges في قيد المبيعات
- [ ] **FIX:** discount_amount → حساب 4800 (ليس 4900)
- [ ] **NEW:** `PostPurchaseReturnToLedger` listener
- [ ] **NEW:** `PostManufacturingOrderToLedger` listener (WIP flow)
- [ ] **NEW:** `journal_entry_id` FK في invoices/returns tables
- [ ] **NEW:** Atomic lock for posting (Cache::lock)
- [ ] **NEW:** DLQ handling — failed posting → `accounting_posting_failures` table + admin notification
- [ ] **NEW:** Sub-ledger accounts per customer/supplier (اختياري: حساب GL فرعي لكل عميل)
- [ ] **NEW:** Daily reconciliation scheduled job

**ملفات إضافية:**
```
app/Listeners/Accounting/PostPurchaseReturnToLedger.php
app/Listeners/Accounting/PostManufacturingToLedger.php
database/migrations/tenant/2026_07_02_000016_add_journal_entry_to_invoices.php
database/migrations/tenant/2026_07_02_000017_create_posting_failures_table.php
app/Console/Commands/AccountingReconcileDaily.php
```

**قواعد الترحيل الإضافية:**

```
فاتورة مبيعات نقدي (مُصحَّحة):
  مدين: 1110 الصندوق            │ total (المحصّل)
  مدين: 4800 خصم مبيعات         │ discount_amount (إن وُجد)
  دائن: 4100 إيرادات المبيعات   │ subtotal
  دائن: 4300 إيرادات شحن        │ shipping_cost (إن وُجد)
  دائن: 4400 رسوم أخرى          │ other_charges (إن وُجد)
  دائن: 2210 VAT مستحق          │ tax_amount
  + قيد COGS (دائماً)

فاتورة مبيعات جزئي (مُصحَّحة):
  مدين: 1110 الصندوق            │ paid
  مدين: 1210 ذمم مدينة          │ remaining (total - paid)
  دائن: 4100 + 4300 + 4400      │ ...
  دائن: 2210 VAT                │ tax_amount
  + قيد COGS

مرتجع مشتريات (جديد):
  مدين: 2110 ذمم دائنة          │ total
  دائن: 1310 المخزون            │ subtotal
  دائن: 1320 VAT مدفوع          │ tax_amount

تصنيع — تأكيد (سحب مواد):
  مدين: 1350 WIP                │ material_cost
  دائن: 1310 المخزون            │ material_cost

تصنيع — إكمال (إدخال منتج تام):
  مدين: 1310 المخزون            │ production_cost
  دائن: 1350 WIP                │ production_cost
```

---

### Phase 4: التقارير المالية (أسبوع 9-10) — مُعدَّل

**الإضافات:**

- [ ] **NEW:** Comparative reports (الفترة الحالية vs السابقة / السنة الحالية vs السابقة)
- [ ] **NEW:** Financial ratios dashboard
  - Current ratio (نسبة السيولة)
  - Quick ratio (نسبة السيولة السريعة)
  - Debt-to-equity
  - Gross margin %
  - Net margin %
  - Receivables turnover
  - Inventory turnover
- [ ] **NEW:** Report date range presets (هذا الشهر، الربع، السنة، custom)
- [ ] **FIX:** Balance Sheet يعرض صافي ربح الفترة (قبل الإقفال) تحت حقوق الملكية
- [ ] **FIX:** Cash Flow يستخدم الطريقة غير المباشرة (indirect method)

---

### Phase 5: Production Hardening (أسبوع 11-12) — جديد بالكامل

> **هدف:** ضمان استقرار وسلامة النظام في بيئة إنتاج حقيقية

#### 5.1 Performance Optimization
- [ ] Indexes review & optimization
- [ ] `account_balances` cache warming strategy
- [ ] Lazy loading → eager loading في التقارير
- [ ] Query analysis: N+1 elimination
- [ ] Report pagination for large datasets
- [ ] Database query timeout configuration

#### 5.2 Error Recovery & Resilience
- [ ] Failed posting retry mechanism (3 attempts, exponential backoff)
- [ ] `posting_failures` admin review page
- [ ] Manual re-post action from UI
- [ ] Graceful degradation: if GL posting fails, sub-ledger still works
- [ ] Transaction rollback testing (every scenario)

#### 5.3 Monitoring & Alerting
- [ ] `accounting:validate-integrity` scheduled daily (2 AM)
- [ ] Balance mismatch alert → email/notification
- [ ] Queue lag monitoring
- [ ] Slow query logging for accounting queries
- [ ] Admin notification: "X entries pending posting"

#### 5.4 Security Hardening
- [ ] Audit log cannot be deleted (no soft delete, no hard delete)
- [ ] IP logging on all accounting mutations
- [ ] Rate limiting on journal entry creation
- [ ] CSRF protection review for all accounting forms
- [ ] Input sanitization for amounts (prevent negative via UI hack)
- [ ] Authorization on every controller method (not just middleware)

#### 5.5 Data Integrity
- [ ] `accounting:validate-integrity` covers all new scenarios
- [ ] Materialized balance drift detection (compare vs recalculated)
- [ ] Orphan detection: entries without valid source
- [ ] Circular parent detection in chart of accounts
- [ ] Fiscal year overlap prevention

#### 5.6 Legacy Migration
- [ ] `accounting:migrate-legacy` command (Opening Balance approach)
- [ ] `--dry-run` + `--tenant=X` support
- [ ] Progress bar + summary report
- [ ] Rollback capability (delete all GL data for tenant)
- [ ] Migration idempotency (run twice = same result)

#### 5.7 Testing
- [ ] Load test: 10,000 journal entries
- [ ] Stress test: 100 concurrent postings
- [ ] Trial balance verification after bulk operations
- [ ] Edge cases: zero-amount lines, max decimal precision, very long descriptions
- [ ] Regression test suite (automated)

---

### Phase 6: متقدم (أسبوع 13-14) — مُحدَّث

**الإضافات عن الخطة الأصلية:**

- [ ] **NEW:** Recurring Journal Entries (شهري: إيجار، رواتب)
- [ ] **NEW:** Cheque management lifecycle:
  ```
  Received → Under Collection → Cleared / Bounced
  Issued → Presented → Cleared / Returned
  ```
- [ ] **FIX:** Period closing يستخدم Income Summary account (3250)
- [ ] **NEW:** Year-end closing wizard UI
- [ ] **NEW:** Opening balances for new fiscal year (auto from closing)
- [ ] **NEW:** POS shift GL reconciliation + variance entry
- [ ] **NEW:** VAT settlement journal entry (quarterly)
- [ ] **NEW:** Payment terms on customers/suppliers (Net30, etc.)
- [ ] **NEW:** Auto-reminder for overdue invoices (scheduled)

---

### Phase 7: مستقبلي (أسبوع 15-16+) — جديد

- [ ] Budget module (تخطيط vs فعلي)
- [ ] Fixed asset register + depreciation engine
- [ ] Employee advances & loans tracking
- [ ] Multi-branch P&L (per warehouse)
- [ ] Chart of Accounts import/export (Excel)
- [ ] Custom financial report builder
- [ ] External auditor read-only portal
- [ ] Bank API integration (import statement)
- [ ] Multi-currency + exchange rate differences
- [ ] E-invoicing integration (country-specific)
- [ ] Petty cash sub-system
- [ ] Cost centers (ربط بالتصنيع)
- [ ] Mobile app API endpoints for accounting

---

## الجزء السابع: ملخص التعديلات المطلوبة على الخطة الأصلية

### تعديلات Critical (يجب قبل أي كود):

| # | التعديل | القسم في الخطة الأصلية |
|---|---------|----------------------|
| 1 | إضافة حساب 4800 خصم مبيعات (فصل عن 4900) | 7 + 8.1 |
| 2 | إضافة قيد COGS في البيع النقدي | 8.1 |
| 3 | معالجة Partial payment correctly | 8.3 |
| 4 | إضافة shipping_cost/other_charges في الترحيل | 8.1, 8.2 |
| 5 | إضافة مرتجع مشتريات | 8 (جديد 8.5b) |
| 6 | إيداع/سحب → اختيار حساب مقابل | 8.9 |
| 7 | إقفال الفترة → 3 قيود عبر ملخص الدخل | 8.10 |
| 8 | إضافة ترحيل التصنيع | 8 (جديد) + 11 |
| 9 | إصلاح ترحيل البيانات القديمة (Opening Balance فقط) | 14 |
| 10 | Concurrency locks for entry numbering + posting | 15 |

### تعديلات Important (قبل Production):

| # | التعديل | الموقع |
|---|---------|--------|
| 11 | جدول `account_balances` materialized | 6 (DB) |
| 12 | Queue error recovery + DLQ | 15 (جديد) |
| 13 | Performance benchmarks | 18 (جديد) |
| 14 | Tenant onboarding wizard | 9 (جديد) |
| 15 | Monitoring + daily integrity check | 15 (توسيع) |
| 16 | Security: authorization في كل method | 13 |
| 17 | `journal_entry_id` FK في invoices/returns | 6.9 (توسيع) |
| 18 | `recurring_journal_entries` table | 6 (جديد) |

---

## الجزء الثامن: Production Checklist (قبل Go-Live)

```
□ كل القيود المعتمدة متوازنة (automated check)
□ ميزان المراجعة = 0 difference
□ AR GL = Σ customer.balance
□ AP GL = Σ supplier.balance
□ Inventory GL ≈ Σ (product_warehouse.quantity × purchase_price)
□ لا قيود مكررة (no duplicate source_event_key)
□ Entry number sequence has no gaps
□ All accounting routes have authorization
□ Audit log records all mutations
□ Queue dead letter is empty
□ Performance: Trial Balance < 2 seconds
□ Performance: Journal Entry creation < 500ms
□ Performance: Dashboard load < 3 seconds
□ Error: UnbalancedEntry → clear user message
□ Error: ClosedPeriod → clear user message
□ Error: Posting failure → admin notified
□ Backup: Daily automated backup includes tenant DBs
□ Recovery: Tested point-in-time restore
□ Security: No raw SQL injection vectors
□ Security: Amount fields reject negative via both UI + backend
□ UX: Arabic RTL rendering correct in all reports
□ UX: Print layout correct for A4 and thermal
□ UX: Mobile responsive for dashboard at minimum
□ Tenant isolation: Tenant A cannot see Tenant B's GL
□ Feature gate: accounting_advanced correctly blocks/allows
```

---

## الخلاصة

الخطة الأصلية **قوية كأساس** (7.9/10) لكن فيها:

- **10 أخطاء محاسبية** — أغلبها critical يجب إصلاحه قبل كتابة أي كود
- **6 فجوات production readiness** — concurrency, monitoring, recovery, performance
- **12 فيتشر أساسي ناقص** — أهمها Manufacturing GL, Purchase Returns, Materialized Balances
- **20 فيتشر إضافي** — لرفع المنتج لمستوى ERP محترف

**التوصية:** اعتماد هذا الـ Roadmap المُعدَّل كمرجع، وتنفيذ Phase 1 بعد تطبيق التصحيحات الـ 10 الـ Critical أولاً.

---

*آخر تحديث: 2026-07-03 | الإصدار 1.0*
