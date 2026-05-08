<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@decohomz.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'phone' => '01012345678',
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@decohomz.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'phone' => '01098765432',
                'role' => 'user',
            ]
        );
    }
}
