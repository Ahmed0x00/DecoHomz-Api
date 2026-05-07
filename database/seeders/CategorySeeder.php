<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Living Room',
                'description' => 'Sofas, armchairs, coffee tables, and TV units for your living room.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bedroom',
                'description' => 'Beds, nightstands, dressers, and wardrobes for a restful bedroom.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dining',
                'description' => 'Dining tables, chairs, and buffets for memorable meals.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Office',
                'description' => 'Desks, bookshelves, and office chairs for productive workspaces.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Outdoor',
                'description' => 'Patio furniture, garden sets, and outdoor decor.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Decor',
                'description' => 'Vases, mirrors, wall art, and decorative accessories.',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
                'sort_order' => $category['sort_order'],
            ]);
        }
    }
}
