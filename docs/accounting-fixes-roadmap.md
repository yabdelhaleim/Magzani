# Roadmap إصلاح الدورة المحاسبية — Magzani SaaS

> **الهدف:** إصلاح كل الأخطاء المحاسبية والتقنية المكتشفة في الخطة الأصلية قبل البدء بأي تنفيذ  
> **التاريخ:** 2026-07-03  
> **الأولوية:** يُنفَّذ بالكامل قبل كتابة أول سطر كود

---

## ملخص الأخطاء المكتشفة

| المستوى | العدد | التأثير |
|---------|-------|---------|
| Critical (خلل محاسبي مباشر) | 10 | أرقام مالية خاطئة |
| Production Gap (فجوة تقنية) | 6 | عدم استقرار في الإنتاج |
| Missing Feature (فيتشر ناقص) | 12 | عدم اكتمال النظام |
| Enhancement (تحسين) | 10 | رفع الجودة |

---

## مراجعة الجودة (Senior QA Review) — v1.1

> تمت مراجعة كل قيد في هذا الملف رياضياً مقابل معادلة الفاتورة الفعلية في الكود  
> (`InvoiceService::calculateInvoiceTotals` سطر 639).

### المعادلة المرجعية (مصدر الحقيقة)

```
total = subtotal − discount_amount + tax_amount + shipping_cost + other_charges
```

- `subtotal` = مجموع بنود الأصناف فقط (لا يشمل الشحن/الرسوم)
- `tax_amount` = يُحسب على المبلغ **بعد الخصم** (`afterDiscount × taxRate`) — مؤكَّد من الكود سطر 668
- `shipping_cost` + `other_charges` = تُضاف **فوق** الـ subtotal

### أخطاء تم اكتشافها وتصحيحها في هذه المراجعة (كانت في مسودة v1.0)

| # | الخطأ | الموقع | التصحيح |
|---|-------|--------|---------|
| QA-1 | دائن 4100 = `subtotal − shipping − other` → قيد غير متوازن بمقدار (shipping+other) | RULE-POST-01/02/03, FIX-04 | دائن 4100 = `subtotal` كاملاً |
| QA-2 | معادلة الفاتورة في FIX-01 كانت ناقصة (بدون shipping/other) | FIX-01 | إضافة الحدّين للمعادلة |
| QA-3 | قيد المشتريات يتجاهل discount/shipping/other → غير متوازن | RULE-POST-05 | مخزون = Landed Cost `subtotal − discount + shipping + other` |
| QA-4 | مرتجع المبيعات لا يعكس الخصم الأصلي → غير متوازن عند وجود خصم | RULE-POST-04 | إضافة دائن 4800 بقيمة الخصم |
| QA-5 | مرتجع المشتريات يعكس subtotal دون خصم | RULE-POST-06 / FIX-05 | دائن مخزون = `subtotal − discount_amount` |

### قواعد تم التحقق من توازنها ✓

كل القيود في القسم 4 (RULE-POST-01 حتى 14) تم إثبات `Σ مدين = Σ دائن` لها جبرياً بعد التصحيح.

### ملاحظة على الميزانية العمومية (أثناء الفترة المفتوحة)

قبل قيد الإقفال (FIX-07)، تبقى أرصدة الإيرادات/المصروفات مفتوحة. لذلك حساب **الميزانية العمومية** يجب أن يضيف صافي ربح الفترة الجارية `(الإيرادات − المصروفات)` ضمن حقوق الملكية، وإلا لن تتوازن `الأصول = الخصوم + حقوق الملكية`. هذا شرط في فحص التكامل (PROD-02).

---

## القسم 1: إصلاحات الدورة المحاسبية (Critical Fixes)

---

### FIX-01: فصل خصم المبيعات عن المرتجعات

**الخطأ:** الخطة تستخدم حساب `4900 مرتجعات مبيعات` لتسجيل الخصم.  
**لماذا خطأ:** الخصم التجاري ≠ المرتجع. الخصم هو تنازل عن جزء من السعر، المرتجع هو إعادة بضاعة. خلطهم يُفسد تحليل الإيرادات.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| دليل الحسابات (Seeder) | إضافة حساب `4800 — خصم مبيعات مسموح به` (contra-revenue) |
| `accounting_settings` migration | إضافة عمود `sales_discount_account_id` |
| `PostingService::postSalesInvoice()` | الخصم يُرحّل لـ 4800 وليس 4900 |
| الخطة القسم 8.1 | تصحيح قاعدة الترحيل |

**القيد الصحيح (بيع نقدي بخصم):**
```
مدين: 1110 الصندوق              │ total (ما دفعه العميل فعلاً)
مدين: 4800 خصم مبيعات           │ discount_amount
دائن: 4100 إيرادات المبيعات     │ subtotal (السعر الأصلي)
دائن: 2210 VAT مستحق            │ tax_amount
```

**قاعدة الفاتورة (مؤكَّدة من الكود `InvoiceService::calculateInvoiceTotals`):**
```
total = subtotal − discount_amount + tax_amount + shipping_cost + other_charges
```
> **مهم جداً:** `subtotal` هو مجموع بنود الأصناف فقط، و `shipping_cost` و `other_charges` تُضاف **فوقه** (ليست جزءاً منه). لذلك دائن حساب الإيرادات 4100 = `subtotal` كاملاً (وليس `subtotal − shipping − other`). أي محاولة لطرح الشحن من الإيراد تُنتج قيداً غير متوازن.

---

### FIX-02: إضافة COGS لكل عملية بيع (نقدي وآجل)

**الخطأ:** قيد COGS مذكور فقط في البيع الآجل (8.2) ومفقود من البيع النقدي (8.1).  
**لماذا خطأ:** كل بيع يُنقص المخزون → يجب تسجيل تكلفة البضاعة المباعة بغض النظر عن طريقة الدفع. بدون COGS في البيع النقدي: مجمل الربح = الإيرادات (مبالغ فيه).

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| `PostingService::postSalesInvoice()` | إنشاء قيد COGS دائماً (نقدي + آجل + جزئي) |
| `PostingService::postSalesInvoiceCogs()` | يُستدعى من `postSalesInvoice()` بدون شرط |
| الخطة القسم 8.1 | إضافة قيد COGS |

**القيد الإضافي (يُنشأ مع كل فاتورة مبيعات مؤكدة):**
```
مدين: 5100 تكلفة البضاعة المباعة │ Σ(qty × unit_cost_snapshot)
دائن: 1310 المخزون                │ Σ(qty × unit_cost_snapshot)
```

**مصدر التكلفة:** `inventory_movements.unit_cost_snapshot` — يُحسب وقت البيع.

---

### FIX-03: معالجة الدفع الجزئي عند تأكيد الفاتورة

**الخطأ:** الخطة تقول "كالمبيعات الآجلة" للفاتورة الجزئية، لكن الفاتورة فيها `paid > 0` عند التأكيد.  
**لماذا خطأ:** لو سجّلنا `total` كله في ذمم مدينة و`paid` موجود → AR مضخّم، والنقدية ناقصة.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| `PostingService::postSalesInvoice()` | فحص `$invoice->paid` وتقسيم القيد |
| الخطة القسم 8.3 | إعادة كتابة كاملة |

**القيد الصحيح (فاتورة total=5000, paid=3000, remaining=2000):**
```
مدين: 1110 الصندوق       │ 3000 (paid)
مدين: 1210 ذمم مدينة     │ 2000 (total - paid)     [party: customer_id]
مدين: 4800 خصم مبيعات    │ discount_amount (إن وُجد)
دائن: 4100 إيرادات       │ subtotal                ← كامل
دائن: 4300 شحن          │ shipping_cost (إن وُجد)
دائن: 4400 رسوم أخرى     │ other_charges (إن وُجد)
دائن: 2210 VAT          │ tax_amount
```
> إجمالي المدين = `paid + (total − paid) + discount` = `total + discount`. وهو يساوي إجمالي الدائن كما في إثبات FIX-04.

**المنطق البرمجي:**
```php
if ($invoice->paid >= $invoice->total) {
    // بيع نقدي كامل → 1110 فقط
} elseif ($invoice->paid > 0) {
    // جزئي → split بين 1110 و 1210
} else {
    // آجل كامل → 1210 فقط
}
```

---

### FIX-04: ترحيل shipping_cost و other_charges

**الخطأ:** الفاتورة تحتوي `shipping_cost` و `other_charges` لكن قواعد الترحيل تتجاهلهم.  
**لماذا خطأ:** المبالغ المحصّلة من العميل مقابل الشحن والرسوم هي إيرادات منفصلة. بدون تسجيلها: `total ≠ sum(credits)` → قيد غير متوازن أو إيرادات مبيعات مضخمة.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| دليل الحسابات (Seeder) | إضافة `4300 إيرادات شحن` + `4400 إيرادات رسوم أخرى` |
| `accounting_settings` migration | إضافة `shipping_revenue_account_id` |
| `PostingService::postSalesInvoice()` | توزيع الـ credit على 4100 + 4300 + 4400 |
| الخطة أقسام 8.1, 8.2, 8.3 | تعديل كل قواعد ترحيل المبيعات |

**القيد الكامل المُصحَّح (بيع نقدي كامل):**
```
مدين: 1110 الصندوق              │ total
مدين: 4800 خصم مبيعات           │ discount_amount (إن > 0)
دائن: 4100 إيرادات المبيعات     │ subtotal            ← كامل، لا يُطرح منه الشحن
دائن: 4300 إيرادات شحن          │ shipping_cost (إن > 0)
دائن: 4400 إيرادات رسوم         │ other_charges (إن > 0)
دائن: 2210 VAT مستحق            │ tax_amount

+ COGS:
مدين: 5100 COGS                 │ cogs
دائن: 1310 المخزون              │ cogs
```

**إثبات التوازن:**
```
مدين  = total + discount = (subtotal − discount + tax + shipping + other) + discount
      = subtotal + tax + shipping + other
دائن  = subtotal + shipping + other + tax
مدين = دائن ✓
```

**ملاحظة:** إذا `shipping_cost = 0` و `other_charges = 0` → لا تُنشأ سطور لهم (تجنب سطور صفرية).

---

### FIX-05: إضافة ترحيل مرتجع المشتريات

**الخطأ:** الخطة تغطي مرتجع المبيعات (8.4) لكن **لا تذكر مرتجع المشتريات** أبداً.  
**لماذا خطأ:** `PurchaseReturn` model و `PurchaseReturnProcessed` event موجودان فعلاً في الكود. بدون ترحيل: AP لا ينقص عند المرتجع، المخزون GL يظل مضخّم.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| الخطة | إضافة قسم 8.5b كامل |
| `PostingService` | إضافة `postPurchaseReturn()` |
| Listeners | إضافة `PostPurchaseReturnToLedger` |
| `EventServiceProvider` | تسجيل listener على `PurchaseReturnProcessed` |

**القيد:**
```
الحدث: PurchaseReturnProcessed
مفتاح Idempotency: purchase_return:{id}:processed

مدين: 2110 ذمم دائنة (موردين)  │ total              [party: supplier_id]
دائن: 1310 المخزون              │ subtotal − discount_amount
دائن: 1320 VAT مدفوع           │ tax_amount
```

**ملاحظة:** إذا المرتجع نقدي (المورد ردّ الفلوس):
```
مدين: 1110 الصندوق              │ total
دائن: 1310 المخزون              │ subtotal
دائن: 1320 VAT مدفوع           │ tax_amount
```

---

### FIX-06: إيداع/سحب الخزينة — اختيار الحساب المقابل

**الخطأ:** الخطة تفترض أن كل إيداع = `إيراد أخرى` وكل سحب = `مصروفات متنوعة`.  
**لماذا خطأ:**
- إيداع رأس مال المالك → ليس إيراد (يذهب لـ 3100 حقوق ملكية)
- تحويل من بنك للصندوق → أصل ↔ أصل (لا إيراد ولا مصروف)
- سداد قرض → خصم ينقص (لا مصروف)
- تصحيح خطأ → لا إيراد

تسجيل كل الإيداعات كإيراد يُضخّم الأرباح ويُفسد قائمة الدخل.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| `treasury.blade.php` | نموذج الإيداع/السحب يتضمن dropdown لاختيار "الحساب المقابل" |
| `AccountingController::storeDeposit()` | يستقبل `counter_account_id` من النموذج |
| `PostingService::postCashTransaction()` | يستخدم الحساب المقابل المُختار |
| `StoreDepositRequest` | validation: `counter_account_id` required, exists in accounts, is_leaf |
| الخطة القسم 8.9 | إعادة كتابة كاملة |

**القيد المُصحَّح:**
```
إيداع:
  مدين: 1110 الصندوق                      │ amount
  دائن: [الحساب الذي يختاره المستخدم]     │ amount

سحب:
  مدين: [الحساب الذي يختاره المستخدم]     │ amount
  دائن: 1110 الصندوق                      │ amount
```

**اقتراحات سريعة في الـ UI:**
```
أسباب الإيداع الشائعة:
  - إيراد أخرى (4200)
  - رأس مال إضافي (3100)
  - تحويل من بنك (1121)
  - تصحيح عجز سابق (5295)

أسباب السحب الشائعة:
  - مصروف تشغيلي (52xx)
  - مسحوبات المالك (3300)
  - تحويل لبنك (1121)
  - سلفة موظف (1400 لو موجود)
```

---

### FIX-07: إغلاق الفترة المالية — 3 قيود عبر ملخص الدخل

**الخطأ:** الخطة تُقفل الإيرادات والمصروفات مباشرة إلى `3200 أرباح محتجزة` في قيد واحد.  
**لماذا خطأ:** المعيار المحاسبي يتطلب:
1. أولاً: إقفال كل حسابات الإيرادات → ملخص الدخل
2. ثانياً: إقفال كل حسابات المصروفات → ملخص الدخل
3. أخيراً: ترحيل صافي ملخص الدخل → أرباح محتجزة

**لماذا مهم:** يسمح بعرض صافي الربح كسطر منفصل في الميزانية العمومية قبل الإقفال النهائي، ويوفر audit trail واضح.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| دليل الحسابات (Seeder) | إضافة `3250 — ملخص الدخل` (حساب مؤقت) |
| `accounting_settings` | إضافة `income_summary_account_id` |
| `FiscalPeriodService::closePeriod()` | إنشاء 3 قيود إقفال |
| الخطة القسم 8.10 | إعادة كتابة كاملة |

**القيود الصحيحة لإغلاق السنة/الفترة:**

```
قيد 1 — إقفال الإيرادات (لكل حساب إيرادات برصيد):
  مدين: 4100 إيرادات المبيعات     │ balance
  مدين: 4200 إيرادات أخرى         │ balance
  مدين: 4300 إيرادات شحن          │ balance
  دائن: 3250 ملخص الدخل           │ total_revenues

قيد 2 — إقفال المصروفات (لكل حساب مصروفات برصيد):
  مدين: 3250 ملخص الدخل           │ total_expenses
  دائن: 5100 COGS                 │ balance
  دائن: 5210 إيجارات              │ balance
  دائن: 5220 رواتب                │ balance
  دائن: ... (كل حساب مصروف)      │ balance

قيد 3 — ترحيل صافي الربح (أو الخسارة):
  إذا ربح (revenues > expenses):
    مدين: 3250 ملخص الدخل         │ net_income
    دائن: 3200 أرباح محتجزة       │ net_income

  إذا خسارة (expenses > revenues):
    مدين: 3200 أرباح محتجزة       │ net_loss
    دائن: 3250 ملخص الدخل         │ net_loss
```

**بعد الإقفال:** رصيد كل حسابات الإيرادات والمصروفات و3250 = صفر.

---

### FIX-08: ترحيل عمليات التصنيع (Manufacturing → GL)

**الخطأ:** الخطة لا تذكر التصنيع أبداً رغم أن `ManufacturingOrderService::completeOrder()` يسحب مواد خام ويُدخل منتجات تامة.  
**لماذا خطأ:** بدون ترحيل: GL inventory balance ≠ physical inventory. تكاليف التصنيع لا تظهر في أي تقرير مالي.

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| دليل الحسابات (Seeder) | إضافة `1350 — إنتاج تحت التشغيل (WIP)` |
| `accounting_settings` | إضافة `wip_account_id` |
| Events | إنشاء `ManufacturingOrderConfirmed` + `ManufacturingOrderCompleted` (أو استخدام الموجود) |
| Listeners | `PostManufacturingConfirmToLedger` + `PostManufacturingCompleteToLedger` |
| `PostingService` | `postManufacturingConfirm()` + `postManufacturingComplete()` |
| `EventServiceProvider` | تسجيل |

**الدورة المحاسبية للتصنيع:**

```
المرحلة 1: تأكيد أمر التصنيع (سحب مواد خام)
══════════════════════════════════════════════
الحدث: ManufacturingOrderConfirmed (أو عند dispense)
مفتاح: manufacturing_order:{id}:confirmed

  مدين: 1350 إنتاج تحت التشغيل (WIP)  │ material_cost
  دائن: 1310 المخزون (مواد خام)        │ material_cost

حيث material_cost = Σ(component.quantity × component.unit_cost)


المرحلة 2: إكمال التصنيع (إدخال منتج تام)
══════════════════════════════════════════════
الحدث: ManufacturingOrderCompleted (status → completed)
مفتاح: manufacturing_order:{id}:completed

  مدين: 1310 المخزون (منتج تام)        │ total_production_cost
  دائن: 1350 إنتاج تحت التشغيل (WIP)  │ total_production_cost

حيث total_production_cost = order.cost_per_unit × order.quantity_produced
```

**بعد الإكمال:** رصيد WIP = 0 لهذا الأمر. المخزون GL يعكس المنتج الجديد بتكلفته.

**ملاحظة:** إذا فيه تكاليف إضافية (عمالة، overhead) تُحمّل على WIP قبل الإكمال:
```
  مدين: 1350 WIP                       │ labor_cost
  دائن: 5220 رواتب (أو 2xxx مستحقات)  │ labor_cost
```

---

### FIX-09: إصلاح منطق ترحيل البيانات القديمة (منع الازدواجية)

**الخطأ:** الخطة تجمع بين ترحيل كل العمليات القديمة كقيود (خطوات 3-7) + قيد افتتاحي (خطوة 8). هذا يسبب ازدواجية.  
**لماذا خطأ:** لو رحّلت فاتورة مبيعات قديمة كقيد (مدين AR / دائن Revenue) **+** سجّلت رصيد AR في القيد الافتتاحي → AR مكرر.

**الإصلاح — طريقتان (يُختار واحدة):**

#### الطريقة A: قيد افتتاحي فقط (المُوصى بها)

```
الخطوة 1: زرع دليل الحسابات + إعدادات
الخطوة 2: إنشاء السنة المالية + 12 فترة
الخطوة 3: إنشاء قيد افتتاحي واحد بالأرصدة الحالية:
  مدين: 1110 (رصيد الصندوق الفعلي — من cash_transactions)
  مدين: 1121 (رصيد البنك — يدوي)
  مدين: 1210 (Σ customer.balance)
  مدين: 1310 (Σ product_warehouse.quantity × purchase_price)
  دائن: 2110 (Σ supplier outstanding)
  دائن: 2210 (VAT مستحق — يدوي إن وُجد)
  دائن: 3200 (الفرق = أرباح محتجزة افتتاحية)
الخطوة 4: التحقق: مجموع مدين = مجموع دائن
الخطوة 5: من الآن: كل عملية جديدة تُرحّل تلقائياً

ملاحظة: العمليات القديمة تبقى في sub-ledgers كمرجع تاريخي
```

**مزايا:** بسيط، سريع، لا ازدواجية.  
**عيوب:** لا تقارير GL للفترة قبل التفعيل.

#### الطريقة B: ترحيل تاريخي كامل (للعملاء المتقدمين)

```
الخطوة 1: زرع دليل الحسابات
الخطوة 2: السنة المالية (تبدأ من أول عملية)
الخطوة 3: ترحيل كل sales_invoices المؤكدة → قيود
الخطوة 4: ترحيل كل purchase_invoices المؤكدة → قيود
الخطوة 5: ترحيل كل payments → قيود قبض
الخطوة 6: ترحيل كل expenses → قيود
الخطوة 7: التحقق: ميزان المراجعة متوازن
الخطوة 8: لا قيد افتتاحي — الأرصدة تتكون من القيود

ملاحظة: بطيء جداً للـ tenants الكبيرة (batch processing مطلوب)
```

**المنطق البرمجي:**
```php
// accounting:migrate-legacy --method=opening (default)
// accounting:migrate-legacy --method=full-history
// accounting:migrate-legacy --dry-run

if ($method === 'opening') {
    $this->createOpeningEntry();  // طريقة A
} else {
    $this->migrateAllTransactions();  // طريقة B — بدون قيد افتتاحي
}
```

---

### FIX-10: Race Condition في ترقيم القيود + Posting المتوازي

**الخطأ:** لا آلية concurrency في `generateEntryNumber()` أو في فحص `source_event_key`.  
**لماذا خطأ:**
- ترقيم: طلبان متزامنان → `SELECT MAX` يرجع نفس الرقم → رقمان مكرران
- Posting: Listener يُنفَّذ مرتين (retry) → فحص exists + insert ليسا atomic → قيدان مكرران

**الإصلاح المطلوب:**

| الملف/المكان | التعديل |
|-------------|---------|
| `JournalEntryService::generateEntryNumber()` | Pessimistic lock أو DB sequence |
| `JournalEntryService::postFromSource()` | Atomic lock + try/catch for duplicate key |
| كل Listeners | Wrap في `Cache::lock()` |

**الحل للترقيم:**
```php
public function generateEntryNumber(): string
{
    return DB::transaction(function () {
        // Lock the settings row to prevent concurrent reads
        $settings = AccountingSetting::lockForUpdate()->first();
        
        $lastEntry = JournalEntry::where('entry_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();
        
        $nextNum = $lastEntry 
            ? intval(substr($lastEntry->entry_number, strlen($prefix) + 5)) + 1 
            : 1;
        
        $year = now()->format('Y');
        return sprintf('%s-%s-%06d', $settings->numbering_prefix_je, $year, $nextNum);
    });
}
```

**الحل للـ Posting:**
```php
public function postFromSource(string $sourceType, int $sourceId, string $eventKey, ...): ?JournalEntry
{
    $lockKey = "accounting:post:{$eventKey}";
    
    return Cache::lock($lockKey, 10)->block(5, function () use ($eventKey, ...) {
        // Double-check after acquiring lock
        if (JournalEntry::where('source_event_key', $eventKey)->exists()) {
            return null; // Already posted — skip
        }
        
        return DB::transaction(function () use (...) {
            $entry = $this->create($header, $lines, $eventKey);
            return $this->post($entry);
        });
    });
}
```

**الحل البديل (أبسط):** الاعتماد على UNIQUE constraint + try/catch:
```php
try {
    return DB::transaction(function () use ($eventKey, ...) {
        return $this->createAndPost(...); // source_event_key = $eventKey
    });
} catch (UniqueConstraintViolationException $e) {
    // Already posted by another process — safe to ignore
    return JournalEntry::where('source_event_key', $eventKey)->first();
}
```

---

## القسم 2: إصلاحات قاعدة البيانات

---

### DB-FIX-01: حسابات ناقصة في الدليل الافتراضي

| الكود | الاسم | النوع | الغرض |
|-------|-------|-------|-------|
| 1350 | إنتاج تحت التشغيل (WIP) | أصول | التصنيع |
| 1400 | دفعات مقدمة لموردين | أصول | عربون مورد |
| 2120 | دفعات مقدمة من عملاء | خصوم | عربون عميل |
| 3250 | ملخص الدخل | حقوق ملكية | إقفال مؤقت |
| 4300 | إيرادات شحن | إيرادات | shipping_cost |
| 4400 | إيرادات رسوم أخرى | إيرادات | other_charges |
| 4800 | خصم مبيعات مسموح به | إيرادات (contra) | discount_amount |
| 5150 | تكلفة مواد خام مستخدمة | مصروفات | تصنيع |
| 5295 | فروقات تقريب | مصروفات | rounding |
| 5400 | مصروفات شحن | مصروفات | لو المنشأة تتحمل |

---

### DB-FIX-02: أعمدة ناقصة في `accounting_settings`

```sql
ADD COLUMN wip_account_id              BIGINT UNSIGNED NULL;
ADD COLUMN income_summary_account_id   BIGINT UNSIGNED NULL;
ADD COLUMN sales_discount_account_id   BIGINT UNSIGNED NULL;
ADD COLUMN shipping_revenue_account_id BIGINT UNSIGNED NULL;
ADD COLUMN rounding_account_id         BIGINT UNSIGNED NULL;
ADD COLUMN advance_customer_account_id BIGINT UNSIGNED NULL;
ADD COLUMN advance_supplier_account_id BIGINT UNSIGNED NULL;
```

---

### DB-FIX-03: إضافة جدول `account_balances` (Materialized)

**لماذا:** حساب الرصيد من `journal_entry_lines` كل مرة = بطء O(n). جدول materialized يعطي O(1).

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

**متى يُحدَّث:** عند كل `post()` أو `reverse()` في `JournalEntryService`.

---

### DB-FIX-04: ربط `journal_entry_id` بالجداول المصدرية

```sql
ALTER TABLE sales_invoices    ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
ALTER TABLE sales_invoices    ADD COLUMN cogs_entry_id    BIGINT UNSIGNED NULL;
ALTER TABLE purchase_invoices ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
ALTER TABLE sales_returns     ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
ALTER TABLE purchase_returns  ADD COLUMN journal_entry_id BIGINT UNSIGNED NULL;
```

**الفائدة:** تتبع عكسي سهل — من الفاتورة يمكن رؤية القيد المرتبط.

---

### DB-FIX-05: جدول `posting_failures` (للمراقبة)

```sql
CREATE TABLE accounting_posting_failures (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    source_type     VARCHAR(50) NOT NULL,
    source_id       BIGINT UNSIGNED NOT NULL,
    event_key       VARCHAR(100) NOT NULL,
    error_message   TEXT NOT NULL,
    error_class     VARCHAR(200) NOT NULL,
    attempts        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    resolved_at     TIMESTAMP NULL,
    resolved_by     BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_failures_unresolved (resolved_at, created_at),
    INDEX idx_failures_source (source_type, source_id)
);
```

---

## القسم 3: إصلاحات Production Readiness

---

### PROD-01: Error Recovery for Queue Listeners

**المشكلة:** Listeners مع `ShouldQueue` ممكن تفشل → العملية تتسجل في sub-ledger بدون GL.

**الإصلاح:**
```php
class PostSalesInvoiceToLedger implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [10, 60, 300]; // 10s, 1m, 5m
    
    public function handle(SalesInvoiceConfirmed $event): void
    {
        // ... posting logic
    }
    
    public function failed(SalesInvoiceConfirmed $event, \Throwable $e): void
    {
        // Record in posting_failures table
        AccountingPostingFailure::create([
            'source_type' => 'sales_invoice',
            'source_id' => $event->invoice->id,
            'event_key' => "sales_invoice:{$event->invoice->id}:confirmed",
            'error_message' => $e->getMessage(),
            'error_class' => get_class($e),
        ]);
        
        // Notify admin
        Notification::send(
            User::admins()->get(),
            new PostingFailedNotification($event->invoice, $e)
        );
    }
}
```

---

### PROD-02: Daily Integrity Check (Scheduled)

```php
// app/Console/Kernel.php
$schedule->command('accounting:validate-integrity')
    ->dailyAt('02:00')
    ->onOneServer()
    ->emailOutputOnFailure(config('accounting.admin_email'));
```

**ما يُفحص:**
```
✓ Σ(debit) = Σ(credit) for all posted entries
✓ Trial balance: Σ(debit_balances) = Σ(credit_balances)
✓ Balance sheet: Assets = Liabilities + Equity
✓ AR GL balance = Σ(customer.balance where balance > 0)
✓ AP GL balance = Σ(supplier outstanding)
✓ account_balances table matches recalculated values
✓ No entries on closed periods with post_date after close
✓ No orphan journal_entry_lines (missing header)
✓ All source_event_keys are unique
✓ Inventory GL ≈ Σ(product_warehouse × cost) (warning, not error)
```

---

### PROD-03: Performance — Materialized Balance Update Strategy

```php
// In JournalEntryService::post()
private function updateMaterializedBalances(JournalEntry $entry): void
{
    foreach ($entry->lines as $line) {
        AccountBalance::updateOrCreate(
            ['account_id' => $line->account_id],
            []
        )->increment('ytd_debit', $line->debit)
         ->increment('ytd_credit', $line->credit);
        
        // Recalculate balance based on normal_balance
        $account = $line->account->load('accountType');
        $ab = AccountBalance::find($line->account_id);
        $ab->balance = $account->accountType->normal_balance === 'debit'
            ? $ab->ytd_debit - $ab->ytd_credit
            : $ab->ytd_credit - $ab->ytd_debit;
        $ab->last_entry_id = $entry->id;
        $ab->last_entry_date = $entry->entry_date;
        $ab->save();
    }
}
```

**Rebuild command:** `accounting:rebuild-balances --tenant=all`

---

### PROD-04: Tenant Onboarding Wizard

**عند تفعيل `accounting_advanced` لأول مرة لـ tenant موجود:**

```
Step 1: "مراجعة دليل الحسابات"
  - عرض الشجرة الافتراضية
  - إمكانية تعديل الأسماء
  - إضافة حسابات فرعية حسب الحاجة

Step 2: "إعداد السنة المالية"
  - اختيار شهر بداية السنة (1=يناير default)
  - إنشاء السنة + الفترات تلقائياً

Step 3: "الأرصدة الافتتاحية"
  - جدول: حساب | رصيد مدين | رصيد دائن
  - Auto-fill من البيانات الموجودة:
    - صندوق = cash_transactions SUM
    - عملاء = customers.balance SUM
    - مخزون = product_warehouse × cost SUM
  - يمكن التعديل يدوياً
  - Validation: مجموع مدين = مجموع دائن

Step 4: "إعدادات الترحيل التلقائي"
  - ☑ ترحيل فواتير المبيعات تلقائياً
  - ☑ ترحيل فواتير المشتريات تلقائياً
  - ☑ ترحيل المدفوعات تلقائياً
  - ☑ ترحيل المصروفات تلقائياً
  - ☐ ترحيل التصنيع تلقائياً

Step 5: "التحقق والتفعيل"
  - ميزان مراجعة أولي (يجب أن يتوازن)
  - زر "تفعيل المحاسبة المتقدمة"
```

---

### PROD-05: Security Hardening

| الإصلاح | التفاصيل |
|---------|----------|
| Authorization per method | كل controller method يستدعي `$this->authorize(...)` |
| Amount validation | `min:0.01`, `max:999999999.99` على كل مبلغ |
| Negative prevention | Backend rejects negative debit/credit (CHECK constraint + PHP) |
| Audit immutability | `accounting_audit_logs` لا `SoftDeletes`، لا `update`، لا `delete` |
| Rate limiting | 60 journal entries / minute / user max |
| IP logging | في `AccountingAuditLog` لكل عملية حساسة |

---

### PROD-06: Monitoring Alerts

| الحدث | الإجراء |
|-------|---------|
| `accounting:validate-integrity` fails | Email + in-app notification |
| Posting failure (3 retries exhausted) | Immediate admin notification |
| Trial balance off by > 0.01 | Critical alert |
| account_balances drift detected | Warning + auto-rebuild |
| Queue accounting jobs > 100 pending | Warning |
| Fiscal period not closed > 45 days | Reminder |

---

## القسم 4: قواعد الترحيل المُصحَّحة (النسخة النهائية)

هنا كل قواعد الترحيل بالشكل الصحيح بعد كل الإصلاحات:

---

### RULE-POST-01: فاتورة مبيعات — نقدي كامل

```
الحدث: SalesInvoiceConfirmed
الشرط: paid >= total
مفتاح: sales_invoice:{id}:confirmed

قيد الإيراد:
  مدين: 1110 الصندوق                │ total
  مدين: 4800 خصم مبيعات             │ discount_amount (إن > 0)
  دائن: 4100 إيرادات مبيعات         │ subtotal            ← كامل
  دائن: 4300 إيرادات شحن            │ shipping_cost (إن > 0)
  دائن: 4400 إيرادات رسوم           │ other_charges (إن > 0)
  دائن: 2210 VAT مستحق              │ tax_amount

قيد COGS (منفصل — نفس المفتاح + ":cogs"):
  مدين: 5100 COGS                   │ Σ(qty × unit_cost_snapshot)
  دائن: 1310 المخزون                │ Σ(qty × unit_cost_snapshot)
```

---

### RULE-POST-02: فاتورة مبيعات — آجل كامل

```
الشرط: paid = 0
مفتاح: sales_invoice:{id}:confirmed

قيد الإيراد:
  مدين: 1210 ذمم مدينة              │ total        [party: customer_id]
  مدين: 4800 خصم مبيعات             │ discount_amount (إن > 0)
  دائن: 4100 إيرادات مبيعات         │ subtotal            ← كامل
  دائن: 4300 إيرادات شحن            │ shipping_cost (إن > 0)
  دائن: 4400 إيرادات رسوم           │ other_charges (إن > 0)
  دائن: 2210 VAT مستحق              │ tax_amount

+ قيد COGS (كالسابق)
```

---

### RULE-POST-03: فاتورة مبيعات — دفع جزئي عند التأكيد

```
الشرط: 0 < paid < total
مفتاح: sales_invoice:{id}:confirmed

قيد الإيراد:
  مدين: 1110 الصندوق                │ paid
  مدين: 1210 ذمم مدينة              │ (total - paid) [party: customer_id]
  مدين: 4800 خصم مبيعات             │ discount_amount (إن > 0)
  دائن: 4100 إيرادات مبيعات         │ subtotal            ← كامل
  دائن: 4300 إيرادات شحن            │ shipping_cost (إن > 0)
  دائن: 4400 إيرادات رسوم           │ other_charges (إن > 0)
  دائن: 2210 VAT مستحق              │ tax_amount

+ قيد COGS (كالسابق)
```

---

### RULE-POST-04: مرتجع مبيعات

```
الحدث: SalesReturnProcessed
مفتاح: sales_return:{id}:processed

عكس الإيراد:
  مدين: 4900 مرتجعات مبيعات        │ subtotal (هنا 4900 صح — لأنه مرتجع فعلاً)
  مدين: 2210 VAT مستحق              │ tax_amount
  دائن: 4800 خصم مبيعات             │ discount_amount (إن > 0 — عكس الخصم الأصلي)
  دائن: 1210 ذمم مدينة / 1110 نقد  │ total        [party: customer_id]

عكس COGS:
  مدين: 1310 المخزون                │ cogs
  دائن: 5100 COGS                   │ cogs
```

---

### RULE-POST-05: فاتورة مشتريات

```
الحدث: PurchaseInvoiceConfirmed
مفتاح: purchase_invoice:{id}:confirmed

  مدين: 1310 المخزون                │ subtotal − discount + shipping + other  (التكلفة المحمَّلة/Landed cost)
  مدين: 1320 VAT مدفوع              │ tax_amount
  دائن: 2110 ذمم دائنة (موردين)    │ total        [party: supplier_id]
```

> **تصحيح مهم:** فاتورة المشتريات أيضاً بها `discount_amount` و `shipping_cost` و `other_charges`. المخزون يُحمَّل بالتكلفة الصافية (Landed Cost): `subtotal − discount + shipping + other`. الخصم يُنقص تكلفة المخزون، والشحن يُضاف إليها.

**إثبات التوازن:**
```
مدين  = (subtotal − discount + shipping + other) + tax
دائن  = total = subtotal − discount + tax + shipping + other
مدين = دائن ✓
```

**ملاحظة (اختياري متقدم):** بعض المنشآت تُفضّل تسجيل الشحن في حساب مصروف منفصل (5400) بدل تحميله على المخزون. القرار يُترك لإعداد `capitalize_freight` في `accounting_settings`.

---

### RULE-POST-06: مرتجع مشتريات

```
الحدث: PurchaseReturnProcessed
مفتاح: purchase_return:{id}:processed

  مدين: 2110 ذمم دائنة              │ total        [party: supplier_id]
  دائن: 1310 المخزون                │ subtotal − discount_amount  (نفس التكلفة التي دخل بها)
  دائن: 1320 VAT مدفوع              │ tax_amount
```
> **تنبيه:** يجب أن تُعكَس تكلفة المخزون بنفس القيمة التي دخل بها الصنف (بعد الخصم). إذا كان الصنف دخل بتكلفة محمَّلة شاملة الشحن، فالمرتجع يجب أن يعكس النسبة المقابلة. للحفاظ على البساطة: `subtotal − discount_amount` للمرتجع الجزئي بدون شحن.

---

### RULE-POST-07: سند قبض (دفعة من عميل)

```
الحدث: PaymentReceived (payable = SalesInvoice)
مفتاح: payment:{id}:received

  مدين: 1110/1121 نقد/بنك          │ amount
  دائن: 1210 ذمم مدينة             │ amount       [party: customer_id]
```

---

### RULE-POST-08: سند صرف (دفعة لمورد)

```
الحدث: SupplierPaymentCreated
مفتاح: supplier_payment:{id}:created

  مدين: 2110 ذمم دائنة             │ amount       [party: supplier_id]
  دائن: 1110/1121 نقد/بنك          │ amount
```

---

### RULE-POST-09: مصروف تشغيلي

```
الحدث: ExpenseCreated
مفتاح: expense:{id}:created

  مدين: 52xx (حسب الفئة)           │ amount
  دائن: 1110 الصندوق               │ amount
```

---

### RULE-POST-10: إيداع/سحب خزينة يدوي

```
مفتاح: cash_transaction:{id}:created

إيداع:
  مدين: 1110 الصندوق                │ amount
  دائن: [counter_account_id]        │ amount  ← يختاره المستخدم

سحب:
  مدين: [counter_account_id]        │ amount  ← يختاره المستخدم
  دائن: 1110 الصندوق                │ amount
```

---

### RULE-POST-11: تصنيع — تأكيد (سحب مواد)

```
الحدث: ManufacturingOrderConfirmed
مفتاح: manufacturing_order:{id}:confirmed

  مدين: 1350 WIP                    │ material_cost
  دائن: 1310 المخزون                │ material_cost
```

---

### RULE-POST-12: تصنيع — إكمال (إدخال منتج تام)

```
الحدث: ManufacturingOrderCompleted
مفتاح: manufacturing_order:{id}:completed

  مدين: 1310 المخزون (منتج تام)    │ production_cost
  دائن: 1350 WIP                    │ production_cost
```

---

### RULE-POST-13: إلغاء فاتورة (عكس)

```
الحدث: SalesInvoiceCancelled / PurchaseInvoiceCancelled
مفتاح: {type}:{id}:reversed

الإجراء: إنشاء قيد عكسي (كل مدين ↔ دائن)
الأصلي: status → 'reversed', reversed_entry_id → العكسي
العكسي: reversal_of_id → الأصلي, source_type → 'reversal'
```

---

### RULE-POST-14: إغلاق فترة/سنة مالية

```
3 قيود إقفال (انظر FIX-07 أعلاه):
  قيد 1: إيرادات → ملخص الدخل (3250)
  قيد 2: مصروفات → ملخص الدخل (3250)
  قيد 3: ملخص الدخل → أرباح محتجزة (3200)
```

---

## القسم 5: ملخص تنفيذي — ترتيب الإصلاحات

### الأولوية 1: قبل كتابة أي كود (تعديل التصميم فقط)

| # | الإصلاح | الوقت المقدّر |
|---|---------|--------------|
| 1 | تحديث دليل الحسابات (+10 حسابات) | 30 دقيقة |
| 2 | تحديث `accounting_settings` schema (+7 أعمدة) | 15 دقيقة |
| 3 | إضافة `account_balances` table | 15 دقيقة |
| 4 | إضافة `posting_failures` table | 10 دقيقة |
| 5 | إضافة `journal_entry_id` للجداول المصدرية | 10 دقيقة |
| 6 | تحديث كل قواعد الترحيل (هذا الملف) | — (تم) |

### الأولوية 2: أثناء كتابة Phase 1

| # | الإصلاح | أين في الكود |
|---|---------|-------------|
| 7 | Pessimistic lock في `generateEntryNumber()` | `JournalEntryService` |
| 8 | Atomic lock في `postFromSource()` | `JournalEntryService` |
| 9 | Materialized balance update في `post()`/`reverse()` | `JournalEntryService` |
| 10 | Exception classes (4 exceptions) | `app/Exceptions/Accounting/` |

### الأولوية 3: أثناء Phase 3 (الترحيل التلقائي)

| # | الإصلاح | أين في الكود |
|---|---------|-------------|
| 11 | FIX-01: خصم → 4800 | `PostingService::postSalesInvoice()` |
| 12 | FIX-02: COGS دائماً | `PostingService::postSalesInvoiceCogs()` |
| 13 | FIX-03: Partial payment split | `PostingService::postSalesInvoice()` |
| 14 | FIX-04: shipping + other_charges | `PostingService::postSalesInvoice()` |
| 15 | FIX-05: Purchase return | `PostingService::postPurchaseReturn()` |
| 16 | FIX-06: Counter account UI | `AccountingController` + views |
| 17 | FIX-08: Manufacturing | `PostingService::postManufacturing*()` |
| 18 | Queue error recovery + DLQ | All Listeners |

### الأولوية 4: أثناء Phase 5 (Production Hardening)

| # | الإصلاح | أين في الكود |
|---|---------|-------------|
| 19 | FIX-07: Period closing 3 entries | `FiscalPeriodService::closePeriod()` |
| 20 | FIX-09: Legacy migration (Opening Balance) | `AccountingMigrateLegacyData` command |
| 21 | Daily integrity check | Scheduled command |
| 22 | Monitoring alerts | Notifications |
| 23 | Security hardening | All controllers |
| 24 | Onboarding wizard | New controller + views |

---

## القسم 6: Validation Checklist — بعد كل Fix

بعد تطبيق كل إصلاح، شغّل هذه الفحوصات:

```
□ ميزان المراجعة = 0 فرق (debit total = credit total)
□ القيد الجديد: مجموع مدين = مجموع دائن (بالقرش)
□ لا سطور بقيمة صفر في القيود
□ لا سطور بمدين ودائن معاً
□ source_event_key فريد (لا مكرر)
□ كل حساب مُستخدم is_leaf = true
□ كل حساب مُستخدم is_active = true
□ account_balances.balance يطابق المحسوب من lines
□ الاختبار: نفس الحدث مرتين = قيد واحد فقط
□ الاختبار: قيد على فترة مغلقة = رفض
```

---

*آخر تحديث: 2026-07-03 | الإصدار 1.1 (بعد Senior QA Review) | يُطبَّق قبل بدء التنفيذ*
