<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

function makeRequest($method, $uri, $data = [], $token = null) {
    global $kernel;
    $server = ['HTTP_ACCEPT' => 'application/json'];
    if ($token) {
        $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    
    $request = Illuminate\Http\Request::create($uri, $method, $data, [], [], $server);
    // If it's POST/PUT, set content type
    if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
        $request->headers->set('Content-Type', 'application/json');
        $request->setJson(new \Symfony\Component\HttpFoundation\InputBag($data));
    }
    
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    
    return [
        'status' => $response->getStatusCode(),
        'body' => json_decode($response->getContent(), true),
    ];
}

// 1. Create a normal user first (or use an existing admin for admin tasks)
// For admin tasks, let's create a new admin token
$admin = \App\Models\User::firstOrCreate(
    ['email' => 'admin_test@decoh.com'],
    ['name' => 'Admin Tester', 'password' => bcrypt('password123'), 'role' => 'admin']
);
$adminToken = $admin->createToken('admin')->plainTextToken;

echo "--- Testing Vendor Registration ---\n";

$vendors = [];

// Create 3 vendors
for ($i = 1; $i <= 3; $i++) {
    // Register user first
    $userRes = makeRequest('POST', '/api/auth/register', [
        'name' => "Vendor User $i",
        'email' => "vendor{$i}@example.com",
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);
    
    $token = $userRes['body']['token'] ?? null;
    if (!$token) {
        // user already exists? login
        $loginRes = makeRequest('POST', '/api/auth/login', [
            'email' => "vendor{$i}@example.com",
            'password' => 'password123'
        ]);
        $token = $loginRes['body']['token'] ?? null;
    }
    
    echo "Vendor $i token retrieved.\n";

    // Register vendor profile
    $regRes = makeRequest('POST', '/api/vendor/register', [
        'business_name' => "Quality Furniture Ltd $i",
        'business_type' => 'Manufacturer',
        'contact_phone' => "010000000$i",
        'business_address' => "123 Industrial Zone $i, Cairo",
        'bank_name' => 'NBE',
        'bank_account_name' => "Quality Furniture $i",
        'bank_account_number' => "123456789$i",
    ], $token);
    
    echo "Register Vendor $i status: {$regRes['status']}\n";
    
    // Add document
    $docRes = makeRequest('POST', '/api/vendor/documents', [
        'document_type' => 'commercial_register',
        'file_url' => 'https://example.com/doc.pdf',
    ], $token);
    echo "Upload Document status: {$docRes['status']}\n";
    
    $vendors[] = [
        'token' => $token,
        'vendor_id' => $regRes['body']['vendor']['id'] ?? ($regRes['status'] == 400 ? \App\Models\Vendor::where('business_name', "Quality Furniture Ltd $i")->first()->id : null)
    ];
}

echo "\n--- Admin Approving Vendors ---\n";
foreach ($vendors as $idx => $v) {
    if (!$v['vendor_id']) continue;
    $appRes = makeRequest('PATCH', "/api/admin/vendors/{$v['vendor_id']}/approve", [], $adminToken);
    echo "Approve Vendor {$v['vendor_id']} status: {$appRes['status']}\n";
}

echo "\n--- Vendor Product Creation ---\n";
// Vendor 1 creates a product
$v1 = $vendors[0];
$cat = \App\Models\Category::first();
if (!$cat) {
    $cat = \App\Models\Category::create(['name' => 'Sofas', 'slug' => 'sofas-test', 'is_active' => true]);
}

$prodRes = makeRequest('POST', '/api/vendor/products', [
    'name' => 'Luxury Velvet Sofa',
    'description' => 'A very nice sofa.',
    'category_id' => $cat->id,
    'price' => 10000,
    'vendor_price' => 8000,
    'materials' => 'Velvet, Wood',
    'dimensions' => '200x90x85 cm',
    'weight' => '45 kg',
    'colors_finishes' => 'Blue, Red, Green',
    'production_time_days' => 14,
    'warranty_info' => '1 Year',
    'packaging_details' => 'Boxed'
], $v1['token']);

echo "Create Product status: {$prodRes['status']}\n";
$prodId = $prodRes['body']['product']['id'] ?? null;

if ($prodId) {
    echo "Submit Product to Review status: " . makeRequest('POST', "/api/vendor/products/{$prodId}/submit", [], $v1['token'])['status'] . "\n";
    
    echo "\n--- Admin Reviewing Product ---\n";
    echo "Approve Product status: " . makeRequest('PATCH', "/api/admin/vendor-products/{$prodId}/review", ['status' => 'approved'], $adminToken)['status'] . "\n";
    
    echo "\n--- Warehouse Inspection ---\n";
    echo "Log Inspection status: " . makeRequest('POST', "/api/admin/warehouse/inspections", [
        'product_id' => $prodId,
        'expected_quantity' => 10,
        'received_quantity' => 10,
        'accepted_quantity' => 8,
        'inspector_notes' => '2 damaged in transit'
    ], $adminToken)['status'] . "\n";
}

echo "\n--- Test Complete ---\n";
