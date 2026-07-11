<?php

namespace App\Services\Accounting;

use App\Enums\JournalEntrySource;
use App\Models\AccountingSetting;
use App\Models\AccountingPostingFailure;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use App\Models\SupplierPayment;
use App\Services\Accounting\PostingFailureRetryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PostingService
 *
 * المحرك الرئيسي لترحيل الأحداث المالية إلى دفتر الأستاذ العام (GL).
 *
 * قواعد التصميم:
 *  - كل دالة ترحيل مستقلة: تُنشئ قيداً مُتوازناً وتُعيده
 *  - Idempotency مُدمجة: كل قيد له source_event_key فريد
 *  - عند الفشل: تُسجَّل في accounting_posting_failures ولا تُوقف النظام
 *  - الترحيل التلقائي مشروط بـ AccountingSetting.auto_post_*
 *
 * ======================================================
 * القيود المحاسبية المدعومة (مُثبَتة التوازن جبرياً):
 * ======================================================
 *
 * 1) فاتورة مبيعات مؤكدة:
 *    DR  1210 ذمم مدينة           = total (شامل ضريبة وشحن وتكاليف)
 *      CR 4100 إيرادات المبيعات   = subtotal - discount_amount
 *      CR 4800 خصم مسموح به       = discount_amount        (إن وُجد)
 *      CR 4300 إيرادات شحن        = shipping_cost          (إن وُجد)
 *      CR 4400 إيرادات رسوم أخرى  = other_charges          (إن وُجد)
 *      CR 2210 ضريبة القيمة مستحقة= tax_amount             (إن وُجد)
 *
 * 2) تكلفة البضاعة المباعة (COGS):
 *    DR  5100 COGS                = cogs_amount
 *      CR 1310 مخزون              = cogs_amount
 *
 * 3) إلغاء فاتورة مبيعات: عكس القيد 1 + عكس القيد 2
 *
 * 4) فاتورة مشتريات مؤكدة:
 *    DR  1310 مخزون               = subtotal - discount + shipping (capitalize_freight)
 *    DR  1320 ضريبة مدفوعة        = tax_amount
 *      CR 2110 ذمم دائنة           = total
 *
 * 5) إلغاء فاتورة مشتريات: عكس القيد 4
 *
 * 6) استلام دفعة عميل:
 *    DR  1110/1121 نقدية/بنك       = amount
 *      CR 1210 ذمم مدينة           = amount
 *
 * 7) دفع للمورد:
 *    DR  2110 ذمم دائنة            = amount
 *      CR 1110/1121 نقدية/بنك      = amount
 *
 * 8) مرتجع مبيعات:
 *    DR  4900 مرتجعات مبيعات       = total
 *      CR 1210 ذمم مدينة           = total
 *    + عكس COGS
 *
 * 9) مرتجع مشتريات:
 *    DR  2110 ذمم دائنة            = total
 *      CR 1310 مخزون               = total
 *
 * 10) عملية صندوق (إيداع/سحب):
 *    DR/CR 1110 نقدية  ↔  CR/DR 5290 مصروف / 4200 إيراد
 */
class PostingService
{
    public function __construct(
        private readonly JournalEntryService  $journalService,
        private readonly AccountBalanceService $balanceService,
    ) {}

    // ═══════════════════════════════════════════════════════════
    // 1. SALES INVOICE POSTING
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل فاتورة مبيعات مؤكدة
     */
    public function postSalesInvoice(SalesInvoice $invoice): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_invoices) {
            return null;
        }

        return $this->safePost(
            key: "sales_invoice:{$invoice->id}:confirmed",
            description: "فاتورة مبيعات #{$invoice->invoice_number}",
            callback: function () use ($invoice, $settings) {
                $lines = $this->buildSalesInvoiceLines($invoice, $settings);
                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $invoice->invoice_date ?? $invoice->created_at,
                    'description'      => "فاتورة مبيعات #{$invoice->invoice_number}",
                    'source_type'      => JournalEntrySource::SALES_INVOICE->value,
                    'source_id'        => $invoice->id,
                    'source_event_key' => "sales_invoice:{$invoice->id}:confirmed",
                    'lines'            => $lines,
                ]);

                $invoice->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    /**
     * ترحيل COGS لفاتورة مبيعات
     */
    public function postSalesInvoiceCogs(SalesInvoice $invoice, float $cogsAmount): ?JournalEntry
    {
        if ($cogsAmount <= 0) {
            return null;
        }

        $settings = $this->getSettings();

        return $this->safePost(
            key: "sales_invoice:{$invoice->id}:cogs",
            description: "COGS فاتورة مبيعات #{$invoice->invoice_number}",
            callback: function () use ($invoice, $cogsAmount, $settings) {
                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $invoice->invoice_date ?? $invoice->created_at,
                    'description'      => "تكلفة البضاعة المباعة — فاتورة #{$invoice->invoice_number}",
                    'source_type'      => JournalEntrySource::SALES_INVOICE->value,
                    'source_id'        => $invoice->id,
                    'source_event_key' => "sales_invoice:{$invoice->id}:cogs",
                    'lines'            => [
                        ['account_id' => $settings->cogs_account_id,      'debit'  => $cogsAmount, 'credit' => 0, 'description' => 'COGS'],
                        ['account_id' => $settings->inventory_account_id, 'debit'  => 0,            'credit' => $cogsAmount, 'description' => 'تخفيض مخزون'],
                    ],
                ]);

                $invoice->update(['cogs_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    /**
     * عكس ترحيل فاتورة مبيعات (عند الإلغاء)
     */
    public function reverseSalesInvoice(SalesInvoice $invoice, string $reason = 'إلغاء الفاتورة'): void
    {
        $this->reverseIfExists($invoice->journal_entry_id, $reason);
        $this->reverseIfExists($invoice->cogs_entry_id,    $reason . ' (COGS)');
    }

    // ═══════════════════════════════════════════════════════════
    // 2. PURCHASE INVOICE POSTING
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل فاتورة مشتريات مؤكدة
     */
    public function postPurchaseInvoice(PurchaseInvoice $invoice): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_invoices) {
            return null;
        }

        return $this->safePost(
            key: "purchase_invoice:{$invoice->id}:confirmed",
            description: "فاتورة مشتريات #{$invoice->invoice_number}",
            callback: function () use ($invoice, $settings) {
                $lines = $this->buildPurchaseInvoiceLines($invoice, $settings);
                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $invoice->invoice_date ?? $invoice->created_at,
                    'description'      => "فاتورة مشتريات #{$invoice->invoice_number}",
                    'source_type'      => JournalEntrySource::PURCHASE_INVOICE->value,
                    'source_id'        => $invoice->id,
                    'source_event_key' => "purchase_invoice:{$invoice->id}:confirmed",
                    'lines'            => $lines,
                ]);

                $invoice->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    /**
     * عكس ترحيل فاتورة مشتريات (عند الإلغاء)
     */
    public function reversePurchaseInvoice(PurchaseInvoice $invoice, string $reason = 'إلغاء فاتورة المشتريات'): void
    {
        $this->reverseIfExists($invoice->journal_entry_id, $reason);
    }

    // ═══════════════════════════════════════════════════════════
    // 3. CUSTOMER PAYMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل دفعة عميل (Payment)
     */
    public function postCustomerPayment(Payment $payment): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_payments) {
            return null;
        }

        return $this->safePost(
            key: "payment:{$payment->id}:received",
            description: "دفعة عميل #{$payment->id}",
            callback: function () use ($payment, $settings) {
                $cashAccountId = $this->resolveCashAccount($payment->payment_method ?? 'cash', $settings);

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $payment->payment_date ?? $payment->created_at,
                    'description'      => "استلام دفعة — {$payment->payable_type} #{$payment->payable_id}",
                    'source_type'      => JournalEntrySource::PAYMENT->value,
                    'source_id'        => $payment->id,
                    'source_event_key' => "payment:{$payment->id}:received",
                    'lines'            => [
                        ['account_id' => $cashAccountId,          'debit'  => $payment->amount, 'credit' => 0,               'description' => 'استلام نقدي/بنكي'],
                        ['account_id' => $settings->ar_account_id,'debit'  => 0,               'credit' => $payment->amount, 'description' => 'تخفيض ذمم العميل', 'party_type' => 'customer', 'party_id' => (str_contains($payment->payable_type, 'Customer') ? $payment->payable_id : null)],
                    ],
                ]);

                $payment->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    // ═══════════════════════════════════════════════════════════
    // 4. SUPPLIER PAYMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل دفعة مورد (SupplierPayment)
     */
    public function postSupplierPayment(SupplierPayment $payment): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_payments) {
            return null;
        }

        return $this->safePost(
            key: "supplier_payment:{$payment->id}:paid",
            description: "دفعة مورد #{$payment->id}",
            callback: function () use ($payment, $settings) {
                $cashAccountId = $this->resolveCashAccount($payment->payment_method ?? 'cash', $settings);

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $payment->payment_date ?? $payment->created_at,
                    'description'      => "دفعة للمورد — #{$payment->id}",
                    'source_type'      => JournalEntrySource::SUPPLIER_PAYMENT->value,
                    'source_id'        => $payment->id,
                    'source_event_key' => "supplier_payment:{$payment->id}:paid",
                    'lines'            => [
                        ['account_id' => $settings->ap_account_id, 'debit'  => $payment->amount, 'credit' => 0,               'description' => 'تسوية ذمة المورد', 'party_type' => 'supplier', 'party_id' => $payment->supplier_id ?? null],
                        ['account_id' => $cashAccountId,           'debit'  => 0,               'credit' => $payment->amount, 'description' => 'صرف نقدي/بنكي'],
                    ],
                ]);

                $payment->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    // ═══════════════════════════════════════════════════════════
    // 5. SALES RETURN
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل مرتجع مبيعات
     */
    public function postSalesReturn(SalesReturn $return, float $cogsAmount = 0): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_invoices) {
            return null;
        }

        return $this->safePost(
            key: "sales_return:{$return->id}:confirmed",
            description: "مرتجع مبيعات #{$return->return_number}",
            callback: function () use ($return, $cogsAmount, $settings) {
                $total          = (float) $return->total;
                $subtotal       = (float) $return->subtotal;
                $discountAmount = (float) ($return->discount_amount ?? 0);
                $taxAmount      = (float) ($return->tax_amount ?? 0);

                // نجلب حساب 4900 من الكود
                $returnsAccount = \App\Models\Account::where('code', '4900')->first();
                $returnsAccountId = $returnsAccount ? $returnsAccount->id : 4900;

                $lines = [
                    // DR مرتجعات المبيعات بقيمة subtotal الكاملة
                    ['account_id' => $returnsAccountId, 'debit' => $subtotal, 'credit' => 0, 'description' => "مرتجع مبيعات #{$return->return_number}"],
                    // CR ذمم العميل بقيمة total الفعلي المرتجع
                    ['account_id' => $settings->ar_account_id, 'debit' => 0, 'credit' => $total, 'description' => 'إعادة ذمة العميل', 'party_type' => 'customer', 'party_id' => $return->customer_id],
                ];

                // DR عكس ضريبة المخرجات لخفض الالتزام الضريبي
                if ($taxAmount > 0 && $settings->tax_account_output_id) {
                    $lines[] = ['account_id' => $settings->tax_account_output_id, 'debit' => $taxAmount, 'credit' => 0, 'description' => 'عكس ضريبة مخرجات (مرتجع)'];
                }

                // CR عكس الخصم الأصلي لتقليل الخصم الممنوح
                if ($discountAmount > 0 && $settings->sales_discount_account_id) {
                    $lines[] = ['account_id' => $settings->sales_discount_account_id, 'debit' => 0, 'credit' => $discountAmount, 'description' => 'عكس خصم مبيعات (مرتجع)'];
                }

                $lines = $this->fixRounding($lines, $settings);

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $return->return_date ?? $return->created_at,
                    'description'      => "مرتجع مبيعات #{$return->return_number}",
                    'source_type'      => JournalEntrySource::SALES_RETURN->value,
                    'source_id'        => $return->id,
                    'source_event_key' => "sales_return:{$return->id}:confirmed",
                    'lines'            => $lines,
                ]);

                $return->update(['journal_entry_id' => $entry->id]);

                // عكس COGS إذا كانت ممكّنة
                if ($cogsAmount > 0) {
                    $cogsEntry = $this->journalService->createAndPost([
                        'entry_date'       => $return->return_date ?? $return->created_at,
                        'description'      => "عكس COGS — مرتجع #{$return->return_number}",
                        'source_type'      => JournalEntrySource::SALES_RETURN->value,
                        'source_id'        => $return->id,
                        'source_event_key' => "sales_return:{$return->id}:cogs_reversal",
                        'lines'            => [
                            ['account_id' => $settings->inventory_account_id, 'debit'  => $cogsAmount, 'credit' => 0,            'description' => 'إعادة مخزون'],
                            ['account_id' => $settings->cogs_account_id,      'debit'  => 0,           'credit' => $cogsAmount,  'description' => 'عكس COGS'],
                        ],
                    ]);
                }

                return $entry;
            }
        );
    }

    // ═══════════════════════════════════════════════════════════
    // 6. PURCHASE RETURN
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل مرتجع مشتريات
     *
     *   DR  2110 ذمم دائنة     = total
     *   CR  1310 مخزون          = subtotal (بدون ضريبة)
     *   CR  1320 VAT مدفوع      = tax_amount
     */
    public function postPurchaseReturn(PurchaseReturn $return): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_invoices) {
            return null;
        }

        return $this->safePost(
            key: "purchase_return:{$return->id}:confirmed",
            description: "مرتجع مشتريات #{$return->return_number}",
            callback: function () use ($return, $settings) {
                $total          = (float) $return->total;
                $subtotal       = (float) ($return->subtotal ?? $total);
                $discountAmount = (float) ($return->discount_amount ?? 0);
                $taxAmount      = (float) ($return->tax_amount ?? 0);

                // صافي القيمة المرتجعة من المخزون بعد خصم قيمة الخصم الأصلي
                $inventoryValue = round($subtotal - $discountAmount, 2);

                $lines = [
                    ['account_id' => $settings->ap_account_id,        'debit' => $total,    'credit' => 0, 'description' => 'تخفيض ذمة المورد', 'party_type' => 'supplier', 'party_id' => $return->supplier_id ?? null],
                    ['account_id' => $settings->inventory_account_id, 'debit' => 0,         'credit' => $inventoryValue, 'description' => 'تخفيض مخزون مُرتجَع (بالصافي)'],
                ];

                if ($taxAmount > 0 && $settings->tax_account_input_id) {
                    $lines[] = ['account_id' => $settings->tax_account_input_id, 'debit' => 0, 'credit' => $taxAmount, 'description' => 'استرداد ضريبة مدفوعة'];
                }

                $lines = $this->fixRounding($lines, $settings);

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $return->return_date ?? $return->created_at,
                    'description'      => "مرتجع مشتريات #{$return->return_number}",
                    'source_type'      => JournalEntrySource::PURCHASE_RETURN->value,
                    'source_id'        => $return->id,
                    'source_event_key' => "purchase_return:{$return->id}:confirmed",
                    'lines'            => $lines,
                ]);

                $return->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    // ═══════════════════════════════════════════════════════════
    // 7. CASH TRANSACTION (EXPENSE / INCOME)
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل عملية صندوق (إيداع/سحب)
     *
     * الحساب المقابل يُحدَّد من:
     *   1. counter_account_id (اختيار المستخدم) — الأولوية
     *   2. Fallback: 4200 إيراد أخرى (إيداع) / 5290 مصروفات متنوعة (سحب)
     */
    public function postCashTransaction(CashTransaction $tx): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_expenses) {
            return null;
        }

        return $this->safePost(
            key: "cash_tx:{$tx->id}:{$tx->transaction_type}",
            description: "عملية صندوق #{$tx->id}",
            callback: function () use ($tx, $settings) {
                $cashAccountId = $settings->cash_account_id;

                // الأولوية: الحساب الذي اختاره المستخدم
                $counterAccountId = $tx->counter_account_id;

                // Fallback إذا لم يختر المستخدم
                if (!$counterAccountId) {
                    $counterAccountId = $tx->transaction_type === 'deposit'
                        ? (\App\Models\Account::where('code', '4200')->value('id') ?? $settings->sales_revenue_account_id)
                        : (\App\Models\Account::where('code', '5290')->value('id') ?? $settings->cogs_account_id);
                }

                $isDeposit = $tx->transaction_type === 'deposit';

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => $tx->transaction_date ?? $tx->created_at,
                    'description'      => $tx->description ?? "عملية صندوق",
                    'source_type'      => JournalEntrySource::CASH_TRANSACTION->value,
                    'source_id'        => $tx->id,
                    'source_event_key' => "cash_tx:{$tx->id}:{$tx->transaction_type}",
                    'lines'            => $isDeposit ? [
                        ['account_id' => $cashAccountId,    'debit'  => $tx->amount, 'credit' => 0,           'description' => 'إيداع صندوق'],
                        ['account_id' => $counterAccountId, 'debit'  => 0,           'credit' => $tx->amount, 'description' => $tx->description ?? 'طرف مقابل'],
                    ] : [
                        ['account_id' => $counterAccountId, 'debit'  => $tx->amount, 'credit' => 0,           'description' => $tx->description ?? 'طرف مقابل'],
                        ['account_id' => $cashAccountId,    'debit'  => 0,           'credit' => $tx->amount, 'description' => 'سحب من الصندوق'],
                    ],
                ]);

                $tx->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    // ═══════════════════════════════════════════════════════════
    // 8. MANUFACTURING ORDER POSTING
    // ═══════════════════════════════════════════════════════════

    /**
     * ترحيل تأكيد أمر تصنيع (سحب مواد خام → WIP)
     *
     *   DR  1350 WIP           = material_cost
     *   CR  1310 المخزون       = material_cost
     */
    public function postManufacturingConfirm(\App\Models\ManufacturingOrder $order): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_manufacturing) {
            return null;
        }

        $totalComponentsCost = round($order->getComponentsTotalCost() * $order->quantity_produced, 2);
        $totalOverheadCost   = round((float)$order->total_cost - $totalComponentsCost, 2);
        $totalManufacturingCost = $totalComponentsCost + $totalOverheadCost;

        if ($totalManufacturingCost <= 0) {
            return null;
        }

        return $this->safePost(
            key: "manufacturing:{$order->id}:confirmed",
            description: "تصنيع (سحب مواد) #{$order->order_number}",
            callback: function () use ($order, $totalComponentsCost, $totalOverheadCost, $totalManufacturingCost, $settings) {
                $lines = [
                    ['account_id' => $settings->wip_account_id,       'debit' => $totalManufacturingCost, 'credit' => 0,             'description' => 'إنتاج تحت التشغيل'],
                    ['account_id' => $settings->inventory_account_id, 'debit' => 0,                       'credit' => $totalComponentsCost, 'description' => 'سحب مواد خام ومكونات'],
                ];

                if ($totalOverheadCost > 0) {
                    $overheadCreditAccount = $settings->accrued_overheads_account_id ?? $settings->cash_account_id ?? $settings->ap_account_id;
                    if ($overheadCreditAccount) {
                        $lines[] = [
                            'account_id'  => $overheadCreditAccount,
                            'debit'       => 0,
                            'credit'      => $totalOverheadCost,
                            'description' => 'تكاليف تحويل وتصنيع إضافية (أجور، شحن، إكراميات)',
                        ];
                    }
                }

                return $this->journalService->createAndPost([
                    'entry_date'       => $order->confirmed_at ?? $order->updated_at,
                    'description'      => "سحب مواد خام — أمر تصنيع #{$order->order_number}",
                    'source_type'      => JournalEntrySource::MANUFACTURING->value,
                    'source_id'        => $order->id,
                    'source_event_key' => "manufacturing:{$order->id}:confirmed",
                    'lines'            => $lines,
                ]);
            }
        );
    }

    /**
     * ترحيل إكمال أمر تصنيع (WIP → منتج تام)
     *
     *   DR  1310 المخزون       = production_cost
     *   CR  1350 WIP           = production_cost
     */
    /**
     * ترحيل فرق صندوق POS عند إغلاق الوردية
     *
     *   عجز (actual < expected): DR rounding/expense, CR cash
     *   فائض (actual > expected): DR cash, CR rounding/income
     */
    public function postPosShiftVariance(\App\Models\PosShift $shift): ?JournalEntry
    {
        $settings = $this->getSettings();
        if (!$settings) {
            return null;
        }

        $variance = round((float) $shift->cash_difference, 2);
        if (abs($variance) < 0.01) {
            return null;
        }

        $roundingAccount = $settings->rounding_account_id;
        if (!$roundingAccount) {
            Log::warning("[PostingService] POS shift variance skipped: no rounding account configured.");
            return null;
        }

        return $this->safePost(
            key: "pos_shift:{$shift->id}:variance",
            description: "فرق صندوق POS — وردية #{$shift->id}",
            callback: function () use ($shift, $variance, $settings, $roundingAccount) {
                $isShortage = $variance < 0;
                $amount     = abs($variance);

                $lines = $isShortage
                    ? [
                        ['account_id' => $roundingAccount,           'debit' => $amount, 'credit' => 0,      'description' => 'عجز صندوق POS'],
                        ['account_id' => $settings->cash_account_id, 'debit' => 0,      'credit' => $amount, 'description' => 'تسوية نقدية — عجز'],
                    ]
                    : [
                        ['account_id' => $settings->cash_account_id, 'debit' => $amount, 'credit' => 0,      'description' => 'فائض صندوق POS'],
                        ['account_id' => $roundingAccount,           'debit' => 0,      'credit' => $amount, 'description' => 'تسوية نقدية — فائض'],
                    ];

                $entry = $this->journalService->createAndPost([
                    'entry_date'       => ($shift->closed_at ?? now())->toDateString(),
                    'description'      => "فرق صندوق POS — وردية #{$shift->id} ({$variance})",
                    'source_type'      => JournalEntrySource::CASH_TRANSACTION->value,
                    'source_id'        => $shift->id,
                    'source_event_key' => "pos_shift:{$shift->id}:variance",
                    'lines'            => $lines,
                ]);

                $shift->update(['journal_entry_id' => $entry->id]);

                return $entry;
            }
        );
    }

    public function postManufacturingComplete(\App\Models\ManufacturingOrder $order): ?JournalEntry
    {
        $settings = $this->getSettings();

        if (!$settings?->auto_post_manufacturing) {
            return null;
        }

        $productionCost = (float) ($order->cost_per_unit * $order->quantity_produced);
        if ($productionCost <= 0) {
            return null;
        }

        return $this->safePost(
            key: "manufacturing:{$order->id}:completed",
            description: "تصنيع (إنتاج تام) #{$order->order_number}",
            callback: function () use ($order, $productionCost, $settings) {
                return $this->journalService->createAndPost([
                    'entry_date'       => $order->produced_at ?? $order->updated_at,
                    'description'      => "إنتاج تام — أمر تصنيع #{$order->order_number}",
                    'source_type'      => JournalEntrySource::MANUFACTURING->value,
                    'source_id'        => $order->id,
                    'source_event_key' => "manufacturing:{$order->id}:completed",
                    'lines'            => [
                        ['account_id' => $settings->inventory_account_id, 'debit' => $productionCost, 'credit' => 0,               'description' => 'منتج تام الصنع'],
                        ['account_id' => $settings->wip_account_id,       'debit' => 0,               'credit' => $productionCost, 'description' => 'إنهاء WIP'],
                    ],
                ]);
            }
        );
    }

    /**
     * Gap 4 — Post a late-invoice batch price adjustment.
     *
     * Called by LateInvoicePriceAdjustmentService — does the idempotent
     * GL post for a single PurchaseInvoiceItem. Three-line shape:
     *
     *   DR  Inventory   (raw stock + finished stock portion)  inventory_impact
     *   DR  COGS        (sold-thru portion)                   cogs_impact
     *     CR  AP        (always inverse of total)             inventory + cogs
     *
     * Or, in fallback mode (entire diff to 5160):
     *
     *   DR  5160 Variance Account     total_impact
     *     CR  AP                       total_impact
     *
     * The orchestrator (LateInvoicePriceAdjustmentService) builds the
     * exact lines and invokes this helper just for the safePost +
     * atomic-lock + failure-capture machinery.
     *
     * Idempotency key is the caller's `source_event_key` (the orchestrator
     * decides what that is per item / fallback path).
     */
    public function postBatchPriceAdjustment(
        string $sourceEventKey,
        string $description,
        array $lines,
        ?int $purchaseInvoiceItemId = null,
    ): ?JournalEntry {
        $productionCost = array_sum(array_map(fn ($l) => (float) $l['debit'], $lines));
        $creditTotal = array_sum(array_map(fn ($l) => (float) $l['credit'], $lines));
        if (abs($productionCost - $creditTotal) > 0.01) {
            Log::warning('[PostingService] postBatchPriceAdjustment unbalanced', [
                'source_event_key' => $sourceEventKey,
                'debits'           => $productionCost,
                'credits'          => $creditTotal,
            ]);
            return null;
        }

        return $this->safePost(
            key: $sourceEventKey,
            description: $description,
            callback: function () use ($sourceEventKey, $description, $lines, $purchaseInvoiceItemId) {
                return $this->journalService->createAndPost([
                    'entry_date'       => now()->toDateString(),
                    'description'      => $description,
                    'source_type'      => JournalEntrySource::MANUAL->value,
                    'source_id'        => $purchaseInvoiceItemId,
                    'source_event_key' => $sourceEventKey,
                    'lines'            => $lines,
                ]);
            }
        );
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════

    /**
     * بناء أسطر قيد فاتورة مبيعات (مُتوازنة جبرياً):
     *
     *   DR  AR/Cash           = total (ما يدفعه العميل فعلياً)
     *   DR  Sales Discount    = discount_amount  (contra-revenue — يُخصَم)
     *   CR  Revenue           = subtotal (كامل قبل الخصم)
     *   CR  Shipping Revenue  = shipping_cost
     *   CR  Other Charges     = other_charges
     *   CR  VAT Output        = tax_amount
     *
     * التوازن: total + discount = subtotal + shipping + other + tax
     *         (sub - disc + tax + ship + other) + disc = sub + ship + other + tax ✓
     */
    private function buildSalesInvoiceLines(SalesInvoice $invoice, AccountingSetting $settings): array
    {
        $total          = (float) $invoice->total;
        $subtotal       = (float) $invoice->subtotal;
        $discountAmount = (float) ($invoice->discount_amount ?? 0);
        $taxAmount      = (float) ($invoice->tax_amount ?? 0);
        $shipping       = (float) ($invoice->shipping_cost ?? 0);
        $otherCharges   = (float) ($invoice->other_charges ?? 0);
        $paid           = (float) ($invoice->paid ?? 0);

        // ── تحديد الطرف المدين: نقدي، آجل، أو مختلط ──
        $lines = [];

        if ($paid >= $total || $invoice->payment_type === 'cash') {
            // بيع نقدي كامل
            $lines[] = ['account_id' => $settings->cash_account_id, 'debit' => $total, 'credit' => 0, 'description' => "تحصيل نقدي — فاتورة #{$invoice->invoice_number}"];
        } elseif ($paid > 0 && $paid < $total) {
            // دفع جزئي: split بين النقدية والذمم
            $remaining = round($total - $paid, 2);
            $lines[] = ['account_id' => $settings->cash_account_id, 'debit' => $paid, 'credit' => 0, 'description' => "تحصيل نقدي جزئي — فاتورة #{$invoice->invoice_number}"];
            $lines[] = ['account_id' => $settings->ar_account_id,   'debit' => $remaining, 'credit' => 0, 'description' => "ذمة عميل (متبقي) — فاتورة #{$invoice->invoice_number}", 'party_type' => 'customer', 'party_id' => $invoice->customer_id];
        } else {
            // آجل بالكامل
            $lines[] = ['account_id' => $settings->ar_account_id, 'debit' => $total, 'credit' => 0, 'description' => "ذمة عميل — فاتورة #{$invoice->invoice_number}", 'party_type' => 'customer', 'party_id' => $invoice->customer_id];
        }

        // DR خصم مسموح به (4800) — contra-revenue يُخصَم لتخفيض الإيراد
        if ($discountAmount > 0 && $settings->sales_discount_account_id) {
            $lines[] = ['account_id' => $settings->sales_discount_account_id, 'debit' => $discountAmount, 'credit' => 0, 'description' => 'خصم مبيعات مسموح به'];
        }

        // CR إيرادات مبيعات = subtotal الكامل (قبل الخصم)
        $lines[] = ['account_id' => $settings->sales_revenue_account_id, 'debit' => 0, 'credit' => $subtotal, 'description' => 'إيراد مبيعات'];

        // CR إيرادات شحن
        if ($shipping > 0 && $settings->shipping_revenue_account_id) {
            $lines[] = ['account_id' => $settings->shipping_revenue_account_id, 'debit' => 0, 'credit' => $shipping, 'description' => 'إيرادات شحن'];
        }

        // CR رسوم أخرى
        if ($otherCharges > 0 && $settings->other_charges_account_id) {
            $lines[] = ['account_id' => $settings->other_charges_account_id, 'debit' => 0, 'credit' => $otherCharges, 'description' => 'رسوم أخرى'];
        }

        // CR ضريبة القيمة المضافة
        if ($taxAmount > 0 && $settings->tax_account_output_id) {
            $lines[] = ['account_id' => $settings->tax_account_output_id, 'debit' => 0, 'credit' => $taxAmount, 'description' => 'ضريبة القيمة المضافة'];
        }

        $lines = $this->fixRounding($lines, $settings);

        return $lines;
    }

    /**
     * بناء أسطر قيد فاتورة مشتريات:
     *   DR  Inventory = (subtotal - discount) + shipping (إذا capitalize_freight)
     *   DR  VAT Input = tax_amount
     *   CR  AP = total
     */
    private function buildPurchaseInvoiceLines(PurchaseInvoice $invoice, AccountingSetting $settings): array
    {
        $total          = (float) $invoice->total;
        $subtotal       = (float) $invoice->subtotal;
        $discountAmount = (float) $invoice->discount_amount;
        $taxAmount      = (float) $invoice->tax_amount;
        $shipping       = (float) $invoice->shipping_cost;
        $otherCharges   = (float) $invoice->other_charges;

        // قيمة الإضافة للمخزون
        $inventoryValue = $subtotal - $discountAmount;

        // رسملة الشحن في تكلفة المخزون إذا كانت الإعدادات تسمح
        if ($settings->capitalize_freight) {
            $inventoryValue += $shipping + $otherCharges;
        }

        $lines = [
            ['account_id' => $settings->inventory_account_id, 'debit' => $inventoryValue, 'credit' => 0, 'description' => 'إضافة مخزون'],
            ['account_id' => $settings->ap_account_id,        'debit' => 0,               'credit' => $total, 'description' => "مديونية مورد — فاتورة #{$invoice->invoice_number}", 'party_type' => 'supplier', 'party_id' => $invoice->supplier_id],
        ];

        // DR ضريبة مدفوعة
        if ($taxAmount > 0 && $settings->tax_account_input_id) {
            $lines[] = ['account_id' => $settings->tax_account_input_id, 'debit' => $taxAmount, 'credit' => 0, 'description' => 'ضريبة قيمة مضافة مدفوعة'];
        }

        // الشحن غير المرسمل كمصروف شحن
        if (!$settings->capitalize_freight && $shipping > 0) {
            $shippingExpenseAccount = \App\Models\Account::where('code', '5400')->value('id');
            if ($shippingExpenseAccount) {
                $lines[] = ['account_id' => $shippingExpenseAccount, 'debit' => $shipping, 'credit' => 0, 'description' => 'مصروف شحن'];
            }
        }

        // الرسوم الأخرى غير المرسملة كمصروف
        if (!$settings->capitalize_freight && $otherCharges > 0) {
            $otherChargesExpenseAccount = \App\Models\Account::where('code', '5290')->value('id');
            if ($otherChargesExpenseAccount) {
                $lines[] = ['account_id' => $otherChargesExpenseAccount, 'debit' => $otherCharges, 'credit' => 0, 'description' => 'مصروفات رسوم أخرى'];
            }
        }

        $lines = $this->fixRounding($lines, $settings);

        return $lines;
    }

    /**
     * تصحيح فروق التقريب بإضافة فرق صغير لحساب التقريب إذا لزم
     */
    private function fixRounding(array $lines, AccountingSetting $settings): array
    {
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));
        $diff        = round($totalDebit - $totalCredit, 2);

        if (abs($diff) > 0 && abs($diff) <= 0.05 && $settings->rounding_account_id) {
            if ($diff > 0) {
                // المدين أكبر — نضيف للدائن
                $lines[] = ['account_id' => $settings->rounding_account_id, 'debit' => 0, 'credit' => $diff, 'description' => 'فرق تقريب'];
            } else {
                // الدائن أكبر — نضيف للمدين
                $lines[] = ['account_id' => $settings->rounding_account_id, 'debit' => abs($diff), 'credit' => 0, 'description' => 'فرق تقريب'];
            }
        }

        return $lines;
    }

    /**
     * عكس قيد محاسبي إن وُجد
     */
    private function reverseIfExists(?int $entryId, string $reason): void
    {
        if (!$entryId) {
            return;
        }

        $entry = JournalEntry::find($entryId);

        if (!$entry) {
            return;
        }

        if ($entry->status->value === 'posted') {
            try {
                $reversalEntry = $this->journalService->reverse($entry, $reason);
            } catch (\Throwable $e) {
                Log::error("[PostingService] Failed to reverse entry #{$entryId}: " . $e->getMessage());
            }
        }
    }

    /**
     * حساب معرف الحساب النقدي حسب طريقة الدفع
     */
    private function resolveCashAccount(string $paymentMethod, AccountingSetting $settings): int
    {
        return match ($paymentMethod) {
            'bank', 'transfer', 'cheque' => $settings->cash_account_id, // سيُحدَّث لاحقاً ليدعم BankAccount
            default                       => $settings->cash_account_id,
        };
    }

    /**
     * تشغيل آمن مع:
     *  1. Atomic lock لمنع الترحيل المتوازي لنفس الحدث
     *  2. Idempotency check (داخل القفل)
     *  3. تسجيل الفشل في جدول accounting_posting_failures
     */
    private function safePost(string $key, string $description, callable $callback): ?JournalEntry
    {
        try {
            return Cache::lock("posting:{$key}", 10)->block(5, function () use ($key, $callback) {
                // Idempotency check inside lock
                $existing = JournalEntry::where('source_event_key', $key)->first();
                if ($existing) {
                    Log::info("[PostingService] Idempotency: key={$key} already posted as entry #{$existing->id}");
                    return $existing;
                }

                return $callback();
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            Log::warning("[PostingService] Lock timeout for key={$key}, retrying without lock...");

            // إذا انتهت مهلة القفل، نتحقق إذا الترحيل تم بالفعل
            $existing = JournalEntry::where('source_event_key', $key)->first();
            if ($existing) {
                return $existing;
            }

            return null;
        } catch (\Throwable $e) {
            Log::error("[PostingService] Posting failed for key={$key}: " . $e->getMessage(), [
                'exception' => $e,
            ]);

            try {
                $parsed = PostingFailureRetryService::parseEventKey($key);

                $failure = AccountingPostingFailure::firstOrNew([
                    'source_event_key' => $key,
                    'resolved'         => false,
                ]);

                $failure->fill([
                    'source_type'   => $parsed['type'] ?? null,
                    'source_id'     => $parsed['id'] ?? null,
                    'event_key'     => $key,
                    'description'   => $description,
                    'error_message' => $e->getMessage(),
                    'error_trace'   => substr($e->getTraceAsString(), 0, 3000),
                    'error_class'   => get_class($e),
                    'failed_at'     => now(),
                    'resolved'      => false,
                    'attempts'      => ($failure->exists ? (int) $failure->attempts : 0) + 1,
                ]);
                $failure->save();
            } catch (\Throwable) {
                // تجاهل فشل تسجيل الخطأ نفسه
            }

            return null;
        }
    }

    /**
     * جلب الإعدادات المحاسبية (مع cache بسيط في الذاكرة لتقليل الاستعلامات)
     */
    private function getSettings(): ?AccountingSetting
    {
        return AccountingSetting::first();
    }
}
