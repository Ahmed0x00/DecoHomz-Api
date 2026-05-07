<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'DECO10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_order_amount' => 500,
            'max_uses' => 100,
            'used_count' => 0,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'FLAT200',
            'discount_type' => 'fixed',
            'discount_value' => 200,
            'min_order_amount' => 1000,
            'max_uses' => 50,
            'used_count' => 0,
            'is_active' => true,
        ]);
    }
}
