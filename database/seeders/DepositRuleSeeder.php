<?php

namespace Database\Seeders;

use App\Models\DepositRule;
use Illuminate\Database\Seeder;

class DepositRuleSeeder extends Seeder
{
    public function run(): void
    {
        DepositRule::create([
            'percentage' => 10.00,
            'minimum_amount' => 0,
            'is_active' => true,
        ]);
    }
}
