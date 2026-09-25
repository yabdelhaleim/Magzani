<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $user = User::withTrashed()->updateOrCreate(
            ['email' => 'admin@makhzani.com'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@makhzani.com',
                'password' => Hash::make('admin123'),
                'phone' => '',
                'is_active' => true,
                'role' => 'admin',
            ]
        );

        if ($user->trashed()) {
            $user->restore();
        }
    }
}
