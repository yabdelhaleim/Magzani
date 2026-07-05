<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountingSetting;
use Illuminate\Database\Seeder;

class AccountingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxOutput = Account::where('code', '2210')->first();
        $taxInput = Account::where('code', '1320')->first();
        $cash = Account::where('code', '1110')->first();
        $ar = Account::where('code', '1210')->first();
        $ap = Account::where('code', '2110')->first();
        $inventory = Account::where('code', '1310')->first();
        $cogs = Account::where('code', '5100')->first();
        $salesRevenue = Account::where('code', '4100')->first();
        $retainedEarnings = Account::where('code', '3200')->first();
        $wip = Account::where('code', '1350')->first();
        $incomeSummary = Account::where('code', '3250')->first();
        $salesDiscount = Account::where('code', '4800')->first();
        $shippingRevenue = Account::where('code', '4300')->first();
        $otherCharges = Account::where('code', '4400')->first();
        $rounding = Account::where('code', '5295')->first();
        $accruedOverheads = Account::where('code', '2140')->first();
        $advanceCustomer = Account::where('code', '2120')->first();
        $advanceSupplier = Account::where('code', '1400')->first();

        AccountingSetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'شركة مخازني',
                'fiscal_year_start_month' => 1,
                'default_currency' => 'EGP',
                'tax_enabled' => true,
                'default_tax_rate' => 14.00,
                'tax_account_output_id' => $taxOutput?->id,
                'tax_account_input_id' => $taxInput?->id,
                'cash_account_id' => $cash?->id,
                'ar_account_id' => $ar?->id,
                'ap_account_id' => $ap?->id,
                'inventory_account_id' => $inventory?->id,
                'cogs_account_id' => $cogs?->id,
                'sales_revenue_account_id' => $salesRevenue?->id,
                'retained_earnings_id' => $retainedEarnings?->id,
                'wip_account_id' => $wip?->id,
                'income_summary_account_id' => $incomeSummary?->id,
                'sales_discount_account_id' => $salesDiscount?->id,
                'shipping_revenue_account_id' => $shippingRevenue?->id,
                'other_charges_account_id' => $otherCharges?->id,
                'rounding_account_id' => $rounding?->id,
                'accrued_overheads_account_id' => $accruedOverheads?->id,
                'advance_customer_account_id' => $advanceCustomer?->id,
                'advance_supplier_account_id' => $advanceSupplier?->id,
                'capitalize_freight' => true,
                'auto_post_invoices' => true,
                'auto_post_payments' => true,
                'auto_post_expenses' => true,
                'auto_post_manufacturing' => false,
                'numbering_prefix_je' => 'JE',
            ]
        );
    }
}
