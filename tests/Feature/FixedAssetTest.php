<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\FixedAsset;
use App\Models\FixedAssetDepreciation;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Services\Accounting\FixedAssetService;
use App\Services\Accounting\JournalEntryService;
use App\Models\AccountingSetting;
use Tests\TestCase;

class FixedAssetTest extends TestCase
{
    protected $tenant;
    protected $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::query()->delete();

        Plan::create([
            'slug' => 'advanced-accounting',
            'name' => 'باقة الحسابات المتقدمة',
            'price' => 99.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced'],
            'is_active' => true,
        ]);
    }

    protected function createTestTenant()
    {
        $this->tenantId = 't-acc-' . uniqid();
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => 'advanced-accounting',
            'is_suspended' => false,
        ]);

        $tenant->domains()->create([
            'domain' => $this->tenantId . '.localhost',
        ]);

        return $tenant;
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // Ignore
            }
        }
        parent::tearDown();
    }

    public function test_fixed_asset_depreciation_and_disposal_flow(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        // Seed data
        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        // Create Fiscal Year & Period
        $fiscalYear = FiscalYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
            'is_current' => true,
        ]);

        $fiscalPeriod = FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);

        $settings = AccountingSetting::firstOrFail();
        $assetService = app(FixedAssetService::class);

        // Fetch Accounts
        $machineryAccount = Account::where('code', '1510')->firstOrFail();
        $accumDepAccount = Account::where('code', '1590')->firstOrFail();
        
        // Let's use 5220 as Depreciation expense ( Salaries & Wages as placeholder or create one )
        $depExpenseAccount = Account::where('code', '5220')->firstOrFail();

        // 1. Register Fixed Asset
        $asset = $assetService->register([
            'name'                                => 'آلة كبس الخشب CNC',
            'code'                                => 'AST-CNC-001',
            'purchase_date'                       => '2026-07-01',
            'purchase_cost'                       => 12000.00,
            'scrap_value'                         => 2000.00,
            'useful_life'                         => 5, // 5 years = 60 months
            'depreciation_method'                 => 'straight_line',
            'asset_account_id'                    => $machineryAccount->id,
            'accumulated_depreciation_account_id' => $accumDepAccount->id,
            'depreciation_expense_account_id'     => $depExpenseAccount->id,
        ]);

        $this->assertDatabaseHas('fixed_assets', [
            'code' => 'AST-CNC-001',
            'status' => 'active',
        ]);

        // 2. Calculate Monthly Depreciation: (12000 - 2000) / 60 months = 166.67
        $monthlyAmount = $assetService->calculateMonthlyDepreciation($asset);
        $this->assertEquals(166.67, $monthlyAmount);

        // 3. Post Depreciation Run for July 2026
        $results = $assetService->postDepreciationRun('2026-07-31');
        $this->assertCount(1, $results);
        $this->assertEquals(166.67, (float) $results[0]->amount);

        // Verify Depreciation record exists
        $this->assertDatabaseHas('fixed_asset_depreciations', [
            'fixed_asset_id' => $asset->id,
            'amount' => 166.67,
        ]);

        // Verify Journal Entry posted
        $je = JournalEntry::findOrFail($results[0]->journal_entry_id);
        $this->assertEquals(166.67, (float) $je->total_debit);
        $this->assertEquals(166.67, (float) $je->total_credit);

        // 4. Dispose/Sell Fixed Asset for 11000.00
        // Book value = 12000 - 166.67 = 11833.33
        // Disposal value = 11000.00
        // Gain/Loss = 11000.00 - 11833.33 = -833.33 (Loss)
        $cashAccount = Account::where('code', '1110')->firstOrFail();
        $disposalEntry = $assetService->dispose($asset, 11000.00, '2026-07-31', $cashAccount->id);

        $this->assertDatabaseHas('fixed_assets', [
            'id' => $asset->id,
            'status' => 'disposed',
            'disposal_value' => 11000.00,
            'disposal_gain_loss' => -833.33,
        ]);

        // Verify Disposal Journal Entry lines:
        // DR Cash (1110): 11000.00
        // DR AccumDep (1590): 166.67
        // DR Loss (5290): 833.33
        // CR Asset (1510): 12000.00
        $this->assertEquals(12000.00, (float) $disposalEntry->total_debit);
        $this->assertEquals(12000.00, (float) $disposalEntry->total_credit);
    }
}
