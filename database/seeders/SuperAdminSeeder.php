<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Provision the central platform operator from deployment-only environment values.
     */
    public function run(): void
    {
        if (function_exists('tenant') && tenant()) {
            $this->command?->warn('SuperAdminSeeder skipped: tenant context is active.');

            return;
        }

        $email = trim((string) config('super_admin.email'));
        $password = (string) config('super_admin.password');

        if ($email === '' || $password === '') {
            $this->command?->info('SuperAdminSeeder skipped: SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD are required.');

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('super_admin.name', 'Platform Administrator'),
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command?->info("Super admin provisioned: {$user->email}");
    }
}
