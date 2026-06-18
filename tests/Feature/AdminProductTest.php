<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected string $token;
    protected Category $category1;
    protected Category $category2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user manually to bypass fillable restriction on 'role'
        $this->admin = new User([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->role = 'admin';
        $this->admin->save();

        $this->token = $this->admin->createToken('test-token')->plainTextToken;

        // Create categories
        $this->category1 = Category::create([
            'name' => 'Chairs',
            'slug' => 'chairs',
            'is_active' => true,
        ]);

        $this->category2 = Category::create([
            'name' => 'Tables',
            'slug' => 'tables',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_retrieve_products_with_stats_and_pagination()
    {
        // Create 20 products
        for ($i = 1; $i <= 20; $i++) {
            Product::create([
                'category_id' => $this->category1->id,
                'name' => "Product $i",
                'price' => 100 + $i,
                'is_active' => true,
                'is_featured' => false,
                'stock' => 10,
            ]);
        }

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/admin/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'stats' => [
                    'total',
                    'active',
                    'featured',
                    'out_of_stock',
                ],
            ]);

        $this->assertEquals(20, $response->json('pagination.total'));
        $this->assertEquals(20, $response->json('stats.total'));
        $this->assertEquals(20, $response->json('stats.active'));
        $this->assertEquals(0, $response->json('stats.featured'));
        $this->assertEquals(0, $response->json('stats.out_of_stock'));
    }

    public function test_admin_can_filter_products_by_category()
    {
        // 5 products in category 1
        for ($i = 1; $i <= 5; $i++) {
            Product::create([
                'category_id' => $this->category1->id,
                'name' => "Cat1 Product $i",
                'price' => 100,
                'is_active' => true,
            ]);
        }

        // 3 products in category 2
        for ($i = 1; $i <= 3; $i++) {
            Product::create([
                'category_id' => $this->category2->id,
                'name' => "Cat2 Product $i",
                'price' => 200,
                'is_active' => true,
            ]);
        }

        // Filter by category_id
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/admin/products?category_id=' . $this->category1->id);

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('products'));
        $this->assertEquals(8, $response->json('stats.total')); // Stats remain database-wide
    }

    public function test_admin_can_filter_products_by_stock_status()
    {
        // 3 in stock products
        for ($i = 1; $i <= 3; $i++) {
            Product::create([
                'category_id' => $this->category1->id,
                'name' => "In Stock Product $i",
                'price' => 100,
                'stock' => 10,
            ]);
        }

        // 2 out of stock products
        for ($i = 1; $i <= 2; $i++) {
            Product::create([
                'category_id' => $this->category1->id,
                'name' => "Out of Stock Product $i",
                'price' => 100,
                'stock' => 0,
            ]);
        }

        // Filter by stock=0 (out of stock)
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/admin/products?stock=0');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('products'));

        // Filter by stock_min=1 (in stock)
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/admin/products?stock_min=1');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('products'));

        // Stats should display correct counts
        $this->assertEquals(5, $response->json('stats.total'));
        $this->assertEquals(2, $response->json('stats.out_of_stock'));
    }
}
