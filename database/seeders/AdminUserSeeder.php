<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD', Str::random(32));

        $user = User::firstOrCreate(
            ['email' => 'admin@decohomz.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
                'phone' => '01012345678',
            ]
        );
        $user->role = 'admin';
        $user->save();

        if (!app()->environment('production')) {
            $testUser = User::firstOrCreate(
                ['email' => 'user@decohomz.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make(env('TEST_USER_PASSWORD', 'TestUser@2026!')),
                    'phone' => '01098765432',
                ]
            );
            $testUser->role = 'user';
            $testUser->save();
        }

        if (isset($adminPassword) && !env('ADMIN_PASSWORD')) {
            $this->command->warn("Generated admin password: {$adminPassword}");
            $this->command->warn("Set ADMIN_PASSWORD in .env to use a specific password.");
        }
    }
}
