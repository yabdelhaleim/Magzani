<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */

class AccountingSetting extends Model
{
    protected $fillable = [
        'company_name',
        'fiscal_year_start_month',
        'default_currency',
        'tax_enabled',
        'default_tax_rate',
        'tax_account_output_id',
        'tax_account_input_id',
        'cash_account_id',
        'ar_account_id',
        'ap_account_id',
        'inventory_account_id',
        'cogs_account_id',
        'sales_revenue_account_id',
        'retained_earnings_id',
        'wip_account_id',
        'income_summary_account_id',
        'sales_discount_account_id',
        'shipping_revenue_account_id',
        'other_charges_account_id',
        'rounding_account_id',
        'accrued_overheads_account_id',
        'advance_customer_account_id',
        'advance_supplier_account_id',
        'capitalize_freight',
        'auto_post_invoices',
        'auto_post_payments',
        'auto_post_expenses',
        'auto_post_manufacturing',
        'standard_costing_enabled',
        'variance_posting_account_id',
        'numbering_prefix_je',
        'strict_posting_mode',
        'max_posting_failures',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:2',
        'tax_enabled' => 'boolean',
        'capitalize_freight' => 'boolean',
        'auto_post_invoices' => 'boolean',
        'auto_post_payments' => 'boolean',
        'auto_post_expenses' => 'boolean',
        'auto_post_manufacturing' => 'boolean',
        'standard_costing_enabled' => 'boolean',
        'strict_posting_mode' => 'boolean',
        'max_posting_failures' => 'integer',
    ];

    /**
     * Check if the tenant is blocking new entries due to strict posting mode
     *
     * @throws \Exception
     */
    public static function checkStrictPostingLimit(): void
    {
        $settings = self::first();
        if ($settings && $settings->strict_posting_mode) {
            $unresolvedCount = \App\Models\AccountingPostingFailure::where('resolved', false)->count();
            $maxFailures = $settings->max_posting_failures ?? 5;
            if ($unresolvedCount > $maxFailures) {
                throw new \Exception("❌ لا يمكن إكمال هذه العملية بسبب وجود قيود محاسبية معلّقة لم يتم ترحيلها بنجاح (وضع الصرامة مفعل). يرجى مراجعة صفحة 'إدارة الترحيلات الفاشلة' أو التواصل مع المحاسب لحل المشاكل قبل المحاولة مجدداً.");
            }
        }
    }

    public function taxOutputAccount()
    {
        return $this->belongsTo(Account::class, 'tax_account_output_id');
    }

    public function taxInputAccount()
    {
        return $this->belongsTo(Account::class, 'tax_account_input_id');
    }

    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function arAccount()
    {
        return $this->belongsTo(Account::class, 'ar_account_id');
    }

    public function apAccount()
    {
        return $this->belongsTo(Account::class, 'ap_account_id');
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function cogsAccount()
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function salesRevenueAccount()
    {
        return $this->belongsTo(Account::class, 'sales_revenue_account_id');
    }

    public function retainedEarningsAccount()
    {
        return $this->belongsTo(Account::class, 'retained_earnings_id');
    }

    public function wipAccount()
    {
        return $this->belongsTo(Account::class, 'wip_account_id');
    }

    public function incomeSummaryAccount()
    {
        return $this->belongsTo(Account::class, 'income_summary_account_id');
    }

    public function salesDiscountAccount()
    {
        return $this->belongsTo(Account::class, 'sales_discount_account_id');
    }

    public function shippingRevenueAccount()
    {
        return $this->belongsTo(Account::class, 'shipping_revenue_account_id');
    }

    public function otherChargesAccount()
    {
        return $this->belongsTo(Account::class, 'other_charges_account_id');
    }

    public function roundingAccount()
    {
        return $this->belongsTo(Account::class, 'rounding_account_id');
    }

    public function accruedOverheadsAccount()
    {
        return $this->belongsTo(Account::class, 'accrued_overheads_account_id');
    }

    public function advanceCustomerAccount()
    {
        return $this->belongsTo(Account::class, 'advance_customer_account_id');
    }

    public function advanceSupplierAccount()
    {
        return $this->belongsTo(Account::class, 'advance_supplier_account_id');
    }

    /**
     * Gap 2 — Manufacturing Cost Variance account (5160 by default).
     * Resolves the account the tenant wants variance entries posted to.
     * Falls back to whatever account carries code 5160 in the COA if no
     * override is configured.
     */
    public function variancePostingAccount()
    {
        return $this->belongsTo(Account::class, 'variance_posting_account_id');
    }

    /**
     * Convenience: returns the resolved variance account row.
     */
    public function getResolvedVarianceAccount(): ?Account
    {
        if ($this->variance_posting_account_id) {
            return $this->variancePostingAccount;
        }

        return Account::where('code', '5160')->first();
    }
}
