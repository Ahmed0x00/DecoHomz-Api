<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Living Room
            [
                'name' => 'Luna 3-Seater Sofa',
                'category' => 'Living Room',
                'price' => 12999,
                'old_price' => 15499,
                'material' => 'Solid Beech Wood',
                'upholstery' => 'Woven Fabric',
                'dimensions' => '220 × 90 × 85 cm',
                'weight' => '58 kg',
                'colors' => ['#C4A882', '#8B6A48', '#3D2B1A'],
                'stars' => 4,
                'badge' => 'Best Seller',
                'badge_color' => '#B8860B',
                'stock' => 15,
                'is_featured' => true,
            ],
            [
                'name' => 'Elio Coffee Table',
                'category' => 'Living Room',
                'price' => 4999,
                'old_price' => null,
                'material' => 'Solid Oak Wood',
                'upholstery' => null,
                'dimensions' => '110 × 60 × 45 cm',
                'weight' => '22 kg',
                'colors' => ['#8B6A48', '#5C3D2A'],
                'stars' => 4,
                'badge' => null,
                'badge_color' => null,
                'stock' => 25,
                'is_featured' => true,
            ],
            [
                'name' => 'Milo Lounge Chair',
                'category' => 'Living Room',
                'price' => 6449,
                'old_price' => 7999,
                'material' => 'Metal Frame',
                'upholstery' => 'Velvet Fabric',
                'dimensions' => '75 × 80 × 90 cm',
                'weight' => '18 kg',
                'colors' => ['#C4A882', '#8B6A48'],
                'stars' => 4,
                'badge' => 'Sale',
                'badge_color' => '#c0392b',
                'stock' => 8,
                'is_featured' => false,
            ],
            [
                'name' => 'Aria Accent Chair',
                'category' => 'Living Room',
                'price' => 3999,
                'old_price' => null,
                'material' => 'Metal Frame',
                'upholstery' => 'Linen Fabric',
                'dimensions' => '68 × 72 × 85 cm',
                'weight' => '14 kg',
                'colors' => ['#E8E0D4', '#888'],
                'stars' => 4,
                'badge' => 'New',
                'badge_color' => '#27ae60',
                'stock' => 20,
                'is_featured' => false,
            ],
            [
                'name' => 'Luca TV Unit',
                'category' => 'Living Room',
                'price' => 6499,
                'old_price' => null,
                'material' => 'MDF & Oak Veneer',
                'upholstery' => null,
                'dimensions' => '180 × 45 × 50 cm',
                'weight' => '35 kg',
                'colors' => ['#8B6A48', '#B89068'],
                'stars' => 5,
                'badge' => null,
                'badge_color' => null,
                'stock' => 12,
                'is_featured' => true,
            ],
            // Dining
            [
                'name' => 'Nora Dining Chair',
                'category' => 'Dining',
                'price' => 2199,
                'old_price' => null,
                'material' => 'Solid Walnut Wood',
                'upholstery' => 'Leather',
                'dimensions' => '45 × 50 × 90 cm',
                'weight' => '8 kg',
                'colors' => ['#C4A882', '#8B6A48'],
                'stars' => 5,
                'badge' => null,
                'badge_color' => null,
                'stock' => 40,
                'is_featured' => false,
            ],
            // Office
            [
                'name' => 'Eden Bookshelf',
                'category' => 'Office',
                'price' => 7999,
                'old_price' => null,
                'material' => 'Engineered Wood',
                'upholstery' => null,
                'dimensions' => '120 × 35 × 200 cm',
                'weight' => '45 kg',
                'colors' => ['#8B6A48', '#5C3D2A'],
                'stars' => 4,
                'badge' => null,
                'badge_color' => null,
                'stock' => 10,
                'is_featured' => true,
            ],
            // Bedroom
            [
                'name' => 'Oslo King Bed',
                'category' => 'Bedroom',
                'price' => 18999,
                'old_price' => null,
                'material' => 'Solid Pine Wood',
                'upholstery' => 'Linen Headboard',
                'dimensions' => '200 × 180 × 110 cm',
                'weight' => '85 kg',
                'colors' => ['#E8E0D4', '#C4A882'],
                'stars' => 5,
                'badge' => 'Best Seller',
                'badge_color' => '#B8860B',
                'stock' => 5,
                'is_featured' => true,
            ],
        ];

        foreach ($products as $productData) {
            $categorySlug = Str::slug($productData['category']);
            $category = \App\Models\Category::where('slug', $categorySlug)->first();

            $product = Product::create([
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => 'Premium quality ' . strtolower($productData['name']) . ' crafted for your home. Made with high-quality ' . strtolower($productData['material']) . ' for durability and comfort.',
                'price' => $productData['price'],
                'old_price' => $productData['old_price'],
                'material' => $productData['material'],
                'upholstery' => $productData['upholstery'] ?? null,
                'dimensions' => $productData['dimensions'] ?? null,
                'weight' => $productData['weight'] ?? null,
                'colors' => $productData['colors'],
                'stars' => $productData['stars'],
                'badge' => $productData['badge'],
                'badge_color' => $productData['badge_color'],
                'stock' => $productData['stock'],
                'is_active' => true,
                'is_featured' => $productData['is_featured'],
            ]);

            // Create a placeholder image record (actual images would be uploaded)
            ProductImage::create([
                'product_id' => $product->id,
                'image' => 'products/placeholder-' . $product->id . '.jpg',
                'alt_text' => $product->name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }
    }
}
