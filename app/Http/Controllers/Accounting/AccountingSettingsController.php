<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountingSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountingSettingsController extends Controller
{
    /**
     * صفحة الإعدادات المحاسبية
     *
     * إصلاح #12: أسماء الحقول مطابقة لـ AccountingSetting model:
     *   ar_account_id, ap_account_id, cash_account_id, sales_revenue_account_id, ...
     */
    public function index(): View
    {
        $settings = AccountingSetting::first();

        // جلب كل الحسابات للـ dropdowns
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('accounting.settings.index', compact('settings', 'accounts'));
    }

    /**
     * تحديث الإعدادات المحاسبية
     * إصلاح #12: أسماء الحقول مطابقة للـ Model الفعلي
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // حسابات الذمم
            'ar_account_id'              => 'required|exists:accounts,id',
            'ap_account_id'              => 'required|exists:accounts,id',
            // حسابات السيولة
            'cash_account_id'            => 'required|exists:accounts,id',
            // حسابات الإيرادات والتكاليف
            'sales_revenue_account_id'   => 'required|exists:accounts,id',
            'cogs_account_id'            => 'required|exists:accounts,id',
            'inventory_account_id'       => 'required|exists:accounts,id',
            // حسابات الضريبة
            'tax_account_output_id'      => 'nullable|exists:accounts,id',
            'tax_account_input_id'       => 'nullable|exists:accounts,id',
            // حسابات الأرباح والإقفال
            'retained_earnings_id'       => 'required|exists:accounts,id',
            'income_summary_account_id'  => 'nullable|exists:accounts,id',
            // حسابات اختيارية
            'sales_discount_account_id'  => 'nullable|exists:accounts,id',
            'shipping_revenue_account_id'=> 'nullable|exists:accounts,id',
            'other_charges_account_id'   => 'nullable|exists:accounts,id',
            'rounding_account_id'        => 'nullable|exists:accounts,id',
            'wip_account_id'             => 'nullable|exists:accounts,id',
            // إعدادات عامة
            'default_currency'           => 'required|string|size:3',
            'fiscal_year_start_month'    => 'required|integer|min:1|max:12',
            'numbering_prefix_je'        => 'nullable|string|max:10',
            // الترحيل التلقائي
            'auto_post_invoices'         => 'boolean',
            'auto_post_payments'         => 'boolean',
            'auto_post_expenses'         => 'boolean',
            'auto_post_manufacturing'    => 'boolean',
            // الضريبة
            'tax_enabled'                => 'boolean',
            'default_tax_rate'           => 'nullable|numeric|min:0|max:100',
            // المخزون
            'capitalize_freight'         => 'boolean',
        ]);

        // الحقول البولية قد تكون غائبة (checkbox) → تعامل معها صراحةً
        foreach (['auto_post_invoices', 'auto_post_payments', 'auto_post_expenses', 'auto_post_manufacturing', 'tax_enabled', 'capitalize_freight'] as $boolField) {
            $validated[$boolField] = $request->boolean($boolField);
        }

        $settings = AccountingSetting::first();
        if ($settings) {
            $settings->update($validated);
        } else {
            AccountingSetting::create($validated);
        }

        return back()->with('success', '✅ تم حفظ الإعدادات المحاسبية بنجاح.');
    }
}
