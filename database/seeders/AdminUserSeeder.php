<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@decohomz.com',
            'password' => Hash::make('password'),
            'phone' => '01012345678',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@decohomz.com',
            'password' => Hash::make('password'),
            'phone' => '01098765432',
            'role' => 'user',
        ]);
    }
}
