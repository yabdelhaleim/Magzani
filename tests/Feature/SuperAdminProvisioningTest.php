<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'super_admin.name' => 'Platform Operator',
            'super_admin.email' => 'platform@example.com',
            'super_admin.password' => 'super-secret-password',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_seeder_provisions_an_active_super_admin_with_hashed_password(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $user = User::where('email', 'platform@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('super_admin', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('super-secret-password', $user->password));
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $firstId = User::where('email', 'platform@example.com')->value('id');

        $this->seed(SuperAdminSeeder::class);

        $this->assertSame($firstId, User::where('email', 'platform@example.com')->value('id'));
        $this->assertSame(1, User::where('email', 'platform@example.com')->count());
    }

    public function test_super_admin_login_redirects_to_central_dashboard(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $response = $this->post('http://localhost/login', [
            'email' => 'platform@example.com',
            'password' => 'super-secret-password',
        ]);

        $response->assertRedirect(route('super-admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'platform@example.com')->first());
    }
}
