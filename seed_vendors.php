<?php
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Product;
use App\Models\WarehouseInspection;
use App\Models\VendorFinanceService;

// Admin setup
$admin = User::firstOrCreate(['email' => 'admin_test@decoh.com'], ['name' => 'Admin Tester', 'password' => bcrypt('password123'), 'role' => 'admin']);

// Category
$cat = Category::firstOrCreate(['slug' => 'sofas-test'], ['name' => 'Sofas', 'is_active' => true]);

for ($i = 1; $i <= 4; $i++) {
    // User
    $user = User::firstOrCreate(
        ['email' => "vendor_seed_{$i}@example.com"],
        ['name' => "Vendor Seed $i", 'password' => bcrypt('password123'), 'role' => 'vendor']
    );

    // Vendor
    $vendor = Vendor::updateOrCreate(
        ['user_id' => $user->id],
        [
            'company_name' => "Quality Furniture Ltd $i",
            'contact_name' => "Contact $i",
            'business_type' => 'Manufacturer',
            'phone' => "010000000$i",
            'email' => $user->email,
            'address' => "123 Industrial Zone $i, Cairo",
            'bank_account_number' => "123456789$i",
            'status' => 'active',
            'contract_accepted_at' => now(),
        ]
    );

    // Document
    $vendor->documents()->firstOrCreate([
        'type' => 'commercial_register',
        'file_path' => 'vendor_documents/test.pdf',
        'status' => 'verified',
        'verified_by' => $admin->id,
        'verified_at' => now(),
    ]);

    // Product
    $prod = Product::create([
        'name' => "Luxury Sofa Model $i",
        'slug' => "luxury-sofa-model-$i-" . uniqid(),
        'description' => "A very nice sofa by vendor $i.",
        'category_id' => $cat->id,
        'price' => 10000 + ($i * 1000),
        'vendor_price' => 8000 + ($i * 1000),
        'vendor_id' => $vendor->id,
        'vendor_status' => 'published',
        'is_active' => true,
    ]);

    $prod->specification()->create([
        'materials' => 'Velvet, Wood',
        'dimensions' => '200x90x85 cm',
        'weight' => '45 kg',
        'colors_finishes' => 'Blue, Red, Green',
        'production_time_days' => 14,
        'warranty_info' => '1 Year',
        'packaging_details' => 'Boxed'
    ]);

    // Warehouse Inspection
    WarehouseInspection::create([
        'product_id' => $prod->id,
        'vendor_id' => $vendor->id,
        'inspector_id' => $admin->id,
        'expected_quantity' => 10,
        'received_quantity' => 10,
        'accepted_quantity' => 8,
        'rejected_quantity' => 2,
        'inspection_result' => 'partial_pass',
        'inspected_at' => now(),
        'inspector_notes' => '2 damaged in transit'
    ]);
    
    // Add some finance ledger transactions
    $vendor->transactions()->create([
        'type' => 'credit',
        'amount' => 64000, // 8 * 8000
        'status' => 'available',
        'description' => 'Payout for 8 units sold',
        'reference_id' => $prod->id
    ]);
}
echo "Seeded vendors successfully.\n";
