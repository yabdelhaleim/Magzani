<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection('mysql')->table('tenants')->where('id', 'test')->delete();
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `tenanttest` ");
        } catch (\Exception $e) {
            // تجاهل
        }

        Plan::query()->delete();
        Plan::create([
            'slug' => 'basic',
            'name' => 'الباقة الأساسية',
            'price' => 19.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'pos', 'manufacturing'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'id' => 'test',
            'plan_id' => 'basic',
        ]);
        $this->tenant->domains()->create([
            'domain' => 'test.localhost',
        ]);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // تجاهل
            }
        }
        parent::tearDown();
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        tenancy()->end();

        // محاكاة طلب كزائر للوحة التحكم الرئيسية للمستأجر
        $response = $this->get('http://test.localhost/');

        // لوحة التحكم محمية بالجلسة — الزائر يُحوَّل لتسجيل الدخول
        $response->assertRedirect('http://test.localhost/login');
    }
}
