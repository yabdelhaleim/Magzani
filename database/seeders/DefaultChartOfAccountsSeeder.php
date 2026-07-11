<?php

namespace Database\Seeders;

use App\Models\AccountType;
use App\Models\Account;
use Illuminate\Database\Seeder;

class DefaultChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. زرع أنواع الحسابات الأساسية
        $types = [
            [
                'id' => 1,
                'code' => 'asset',
                'name_ar' => 'أصول',
                'name_en' => 'Assets',
                'normal_balance' => 'debit',
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'code' => 'liability',
                'name_ar' => 'خصوم',
                'name_en' => 'Liabilities',
                'normal_balance' => 'credit',
                'sort_order' => 2,
            ],
            [
                'id' => 3,
                'code' => 'equity',
                'name_ar' => 'حقوق ملكية',
                'name_en' => 'Equity',
                'normal_balance' => 'credit',
                'sort_order' => 3,
            ],
            [
                'id' => 4,
                'code' => 'revenue',
                'name_ar' => 'إيرادات',
                'name_en' => 'Revenue',
                'normal_balance' => 'credit',
                'sort_order' => 4,
            ],
            [
                'id' => 5,
                'code' => 'expense',
                'name_ar' => 'مصروفات',
                'name_en' => 'Expenses',
                'normal_balance' => 'debit',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            AccountType::updateOrCreate(['id' => $type['id']], $type);
        }

        // 2. زرع شجرة الحسابات الافتراضية
        $accounts = [
            // Level 1
            ['code' => '1000', 'name_ar' => 'الأصول', 'name_en' => 'Assets', 'account_type_id' => 1, 'parent_code' => null, 'level' => 1, 'is_leaf' => false, 'is_system' => true],
            ['code' => '2000', 'name_ar' => 'الخصوم', 'name_en' => 'Liabilities', 'account_type_id' => 2, 'parent_code' => null, 'level' => 1, 'is_leaf' => false, 'is_system' => true],
            ['code' => '3000', 'name_ar' => 'حقوق الملكية', 'name_en' => 'Equity', 'account_type_id' => 3, 'parent_code' => null, 'level' => 1, 'is_leaf' => false, 'is_system' => true],
            ['code' => '4000', 'name_ar' => 'الإيرادات', 'name_en' => 'Revenue', 'account_type_id' => 4, 'parent_code' => null, 'level' => 1, 'is_leaf' => false, 'is_system' => true],
            ['code' => '5000', 'name_ar' => 'المصروفات', 'name_en' => 'Expenses', 'account_type_id' => 5, 'parent_code' => null, 'level' => 1, 'is_leaf' => false, 'is_system' => true],
            
            // Level 2 (Assets)
            ['code' => '1100', 'name_ar' => 'الأصول المتداولة', 'name_en' => 'Current Assets', 'account_type_id' => 1, 'parent_code' => '1000', 'level' => 2, 'is_leaf' => false, 'is_system' => true],
            ['code' => '1500', 'name_ar' => 'الأصول الثابتة', 'name_en' => 'Fixed Assets', 'account_type_id' => 1, 'parent_code' => '1000', 'level' => 2, 'is_leaf' => false, 'is_system' => true],
            
            // Level 3 (Current Assets)
            ['code' => '1110', 'name_ar' => 'الصندوق (نقدية)', 'name_en' => 'Cash in Hand', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '1120', 'name_ar' => 'البنوك', 'name_en' => 'Banks', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => false, 'is_system' => true],
            ['code' => '1210', 'name_ar' => 'الذمم المدينة (عملاء)', 'name_en' => 'Accounts Receivable', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '1310', 'name_ar' => 'المخزون', 'name_en' => 'Inventory', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '1320', 'name_ar' => 'ضريبة القيمة المضافة مدفوعة', 'name_en' => 'VAT Input', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '1350', 'name_ar' => 'إنتاج تحت التشغيل (WIP)', 'name_en' => 'Work in Progress', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '1400', 'name_ar' => 'دفعات مقدمة لموردين', 'name_en' => 'Advance to Suppliers', 'account_type_id' => 1, 'parent_code' => '1100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            
            // Level 4 (Banks)
            ['code' => '1121', 'name_ar' => 'البنك الرئيسي', 'name_en' => 'Main Bank', 'account_type_id' => 1, 'parent_code' => '1120', 'level' => 4, 'is_leaf' => true, 'is_system' => true],
            
            // Level 3 (Fixed Assets)
            ['code' => '1510', 'name_ar' => 'معدات وآلات', 'name_en' => 'Machinery & Equipment', 'account_type_id' => 1, 'parent_code' => '1500', 'level' => 3, 'is_leaf' => true, 'is_system' => false],
            ['code' => '1590', 'name_ar' => 'مجمع الإهلاك', 'name_en' => 'Accumulated Depreciation', 'account_type_id' => 1, 'parent_code' => '1500', 'level' => 3, 'is_leaf' => true, 'is_system' => false],
            
            // Level 2 (Liabilities)
            ['code' => '2100', 'name_ar' => 'الخصوم المتداولة', 'name_en' => 'Current Liabilities', 'account_type_id' => 2, 'parent_code' => '2000', 'level' => 2, 'is_leaf' => false, 'is_system' => true],
            
            // Level 3 (Current Liabilities)
            ['code' => '2110', 'name_ar' => 'الذمم الدائنة (موردين)', 'name_en' => 'Accounts Payable', 'account_type_id' => 2, 'parent_code' => '2100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '2120', 'name_ar' => 'دفعات مقدمة من عملاء', 'name_en' => 'Advance from Customers', 'account_type_id' => 2, 'parent_code' => '2100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '2210', 'name_ar' => 'ضريبة القيمة المضافة مستحقة', 'name_en' => 'VAT Output', 'account_type_id' => 2, 'parent_code' => '2100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '2140', 'name_ar' => 'مصاريف تشغيل مستحقة', 'name_en' => 'Accrued Production Expenses', 'account_type_id' => 2, 'parent_code' => '2100', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            
            // Level 2 (Equity)
            ['code' => '3100', 'name_ar' => 'رأس المال', 'name_en' => 'Capital', 'account_type_id' => 3, 'parent_code' => '3000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '3200', 'name_ar' => 'الأرباح المحتجزة', 'name_en' => 'Retained Earnings', 'account_type_id' => 3, 'parent_code' => '3000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '3250', 'name_ar' => 'ملخص الدخل (مؤقت)', 'name_en' => 'Income Summary', 'account_type_id' => 3, 'parent_code' => '3000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '3300', 'name_ar' => 'مسحوبات المالك', 'name_en' => 'Drawings', 'account_type_id' => 3, 'parent_code' => '3000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            
            // Level 2 (Revenue)
            ['code' => '4100', 'name_ar' => 'إيرادات المبيعات', 'name_en' => 'Sales Revenue', 'account_type_id' => 4, 'parent_code' => '4000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '4200', 'name_ar' => 'إيرادات أخرى', 'name_en' => 'Other Revenue', 'account_type_id' => 4, 'parent_code' => '4000', 'level' => 2, 'is_leaf' => true, 'is_system' => false],
            ['code' => '4300', 'name_ar' => 'إيرادات شحن', 'name_en' => 'Shipping Revenue', 'account_type_id' => 4, 'parent_code' => '4000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '4400', 'name_ar' => 'إيرادات رسوم أخرى', 'name_en' => 'Other Charges Revenue', 'account_type_id' => 4, 'parent_code' => '4000', 'level' => 2, 'is_leaf' => true, 'is_system' => false],
            ['code' => '4800', 'name_ar' => 'خصم مبيعات مسموح به', 'name_en' => 'Sales Discount Allowed', 'account_type_id' => 4, 'parent_code' => '4000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '4900', 'name_ar' => 'مرتجعات المبيعات', 'name_en' => 'Sales Returns', 'account_type_id' => 4, 'parent_code' => '4000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            
            // Level 2 (Expenses)
            ['code' => '5100', 'name_ar' => 'تكلفة البضاعة المباعة', 'name_en' => 'COGS', 'account_type_id' => 5, 'parent_code' => '5000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '5150', 'name_ar' => 'تكلفة مواد خام مستخدمة', 'name_en' => 'Raw Materials Used Cost', 'account_type_id' => 5, 'parent_code' => '5000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            // Gap 2: Standard Costing & Cost Variance — Manufacturing Cost Variance (used only when tenant enables standard_costing)
            ['code' => '5160', 'name_ar' => 'انحراف تكلفة التصنيع', 'name_en' => 'Manufacturing Cost Variance', 'account_type_id' => 5, 'parent_code' => '5000', 'level' => 2, 'is_leaf' => true, 'is_system' => true],
            ['code' => '5200', 'name_ar' => 'مصروفات تشغيلية', 'name_en' => 'Operating Expenses', 'account_type_id' => 5, 'parent_code' => '5000', 'level' => 2, 'is_leaf' => false, 'is_system' => true],
            ['code' => '5300', 'name_ar' => 'مصروفات إدارية وعمومية', 'name_en' => 'General & Admin Expenses', 'account_type_id' => 5, 'parent_code' => '5000', 'level' => 2, 'is_leaf' => true, 'is_system' => false],
            ['code' => '5400', 'name_ar' => 'مصروفات شحن', 'name_en' => 'Shipping Expenses', 'account_type_id' => 5, 'parent_code' => '5000', 'level' => 2, 'is_leaf' => true, 'is_system' => false],
            
            // Level 3 (Operating Expenses)
            ['code' => '5210', 'name_ar' => 'إيجارات', 'name_en' => 'Rents', 'account_type_id' => 5, 'parent_code' => '5200', 'level' => 3, 'is_leaf' => true, 'is_system' => false],
            ['code' => '5220', 'name_ar' => 'رواتب وأجور', 'name_en' => 'Salaries & Wages', 'account_type_id' => 5, 'parent_code' => '5200', 'level' => 3, 'is_leaf' => true, 'is_system' => false],
            ['code' => '5230', 'name_ar' => 'كهرباء ومياه', 'name_en' => 'Utilities', 'account_type_id' => 5, 'parent_code' => '5200', 'level' => 3, 'is_leaf' => true, 'is_system' => false],
            ['code' => '5240', 'name_ar' => 'مواصلات', 'name_en' => 'Transportation', 'account_type_id' => 5, 'parent_code' => '5200', 'level' => 3, 'is_leaf' => true, 'is_system' => false],
            ['code' => '5290', 'name_ar' => 'مصروفات متنوعة', 'name_en' => 'Miscellaneous Expenses', 'account_type_id' => 5, 'parent_code' => '5200', 'level' => 3, 'is_leaf' => true, 'is_system' => true],
            ['code' => '5295', 'name_ar' => 'فروقات تقريب', 'name_en' => 'Rounding Differences', 'account_type_id' => 5, 'parent_code' => '5200', 'level' => 3, 'is_leaf' => true, 'is_system' => true]
        ];

        foreach ($accounts as $accData) {
            $parentCode = $accData['parent_code'];
            unset($accData['parent_code']);

            if ($parentCode) {
                $parent = Account::where('code', $parentCode)->first();
                $accData['parent_id'] = $parent ? $parent->id : null;
            } else {
                $accData['parent_id'] = null;
            }

            Account::updateOrCreate(
                ['code' => $accData['code']],
                $accData
            );
        }
    }
}
