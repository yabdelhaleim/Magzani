<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DefaultChartOfAccountsSeeder;
use Database\Seeders\AccountingSettingsSeeder;

class SeedDefaultChartOfAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:seed-default-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'زرع دليل الحسابات الافتراضي وإعدادات المحاسبة للشركة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 جاري زرع دليل الحسابات الافتراضي (Default Chart of Accounts)...');
        
        try {
            $seeder = new DefaultChartOfAccountsSeeder();
            $seeder->run();
            $this->info('✅ تم زرع الحسابات الافتراضية بنجاح.');

            $this->info('🔄 جاري تهيئة إعدادات المحاسبة الافتراضية (Accounting Settings)...');
            $settingsSeeder = new AccountingSettingsSeeder();
            $settingsSeeder->run();
            $this->info('✅ تم تهيئة إعدادات المحاسبة بنجاح.');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ حدث خطأ أثناء عملية الزرع: ' . $e->getMessage());
            return 1;
        }
    }
}
