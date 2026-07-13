<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;

class TestVendorApiCommand extends Command
{
    protected $signature = 'test:vendor-api';
    protected $description = 'Tests the vendor API flow internally';

    public function handle()
    {
        $this->info('Starting Vendor API Internal Test...');

        // Admin User setup
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@decoh.com'],
            ['name' => 'Admin Tester', 'password' => bcrypt('password123'), 'role' => 'admin']
        );
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $vendors = [];
        for ($i = 1; $i <= 3; $i++) {
            $this->info("Creating Vendor $i...");
            
            // Register User
            $userRes = $this->makeRequest('POST', '/api/auth/register', [
                'name' => "Vendor User $i",
                'email' => "vendor{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ]);
            
            $token = $userRes['body']['token'] ?? null;
            if (!$token) {
                $loginRes = $this->makeRequest('POST', '/api/auth/login', [
                    'email' => "vendornew{$i}@example.com",
                    'password' => 'password123'
                ]);
                $token = $loginRes['body']['token'] ?? null;
            }

            // Register Vendor Profile
            $regRes = $this->makeRequest('POST', '/api/vendor/register', [
                'company_name' => "Quality Furniture Ltd $i",
                'contact_name' => "Contact $i",
                'business_type' => 'Manufacturer',
                'phone' => "010000000$i",
                'email' => "vendor{$i}@example.com",
                'address' => "123 Industrial Zone $i, Cairo",
                'bank_account_number' => "123456789$i",
            ], $token);

            $vendorId = $regRes['body']['vendor']['id'] ?? (\App\Models\Vendor::where('company_name', "Quality Furniture Ltd $i")->first()->id ?? null);
            
            if (!$vendorId) {
                $this->error("Failed to register vendor $i. Response: " . json_encode($regRes));
            }

            if ($vendorId) {
                // Add Document
                $this->makeRequest('POST', '/api/vendor/documents', [
                    'type' => 'commercial_register',
                    'file' => new \Illuminate\Http\UploadedFile(
                        __DIR__.'/TestVendorApiCommand.php', 
                        'test.pdf', 
                        'application/pdf', 
                        null, 
                        true
                    )
                ], $token);

                $vendors[] = [
                    'token' => $token,
                    'vendor_id' => $vendorId
                ];
                $this->info("Vendor $i created (ID: $vendorId).");
            }
        }

        // Admin Approves Vendors
        $this->info("\nAdmin Approving Vendors...");
        foreach ($vendors as $v) {
            $this->makeRequest('PATCH', "/api/admin/vendors/{$v['vendor_id']}/approve", [], $adminToken);
            $this->info("Approved vendor {$v['vendor_id']}.");
        }

        // Vendor creates products
        $this->info("\nVendors creating products...");
        $cat = Category::firstOrCreate(['slug' => 'sofas-test'], ['name' => 'Sofas', 'is_active' => true]);
        
        foreach ($vendors as $idx => $v) {
            $num = $idx + 1;
            $prodRes = $this->makeRequest('POST', '/api/vendor/products', [
                'name' => "Luxury Sofa Model $num",
                'description' => "A very nice sofa by vendor $num.",
                'category_id' => $cat->id,
                'price' => 10000 + ($num * 1000),
                'vendor_price' => 8000 + ($num * 1000),
                'materials' => 'Velvet, Wood',
                'dimensions' => '200x90x85 cm',
                'weight' => '45 kg',
                'colors_finishes' => 'Blue, Red, Green',
                'production_time_days' => 14,
                'warranty_info' => '1 Year',
                'packaging_details' => 'Boxed'
            ], $v['token']);
            
            $prodId = $prodRes['body']['product']['id'] ?? null;
            if ($prodId) {
                $this->info("Product $prodId created by Vendor $num.");
                
                // Submit to review
                $this->makeRequest('POST', "/api/vendor/products/{$prodId}/submit", [], $v['token']);
                
                // Admin reviews product
                $this->makeRequest('PATCH', "/api/admin/vendor-products/{$prodId}/review", ['status' => 'approved'], $adminToken);
                $this->info("Admin approved product $prodId.");
                
                // Warehouse logs inspection
                $this->makeRequest('POST', "/api/admin/warehouse/inspections", [
                    'product_id' => $prodId,
                    'expected_quantity' => 10,
                    'received_quantity' => 10,
                    'accepted_quantity' => 8,
                    'inspector_notes' => '2 damaged in transit'
                ], $adminToken);
                $this->info("Warehouse inspected product $prodId. Status should now be published.");
            }
        }
        
        $this->info('API Test Completed successfully!');
    }

    private function makeRequest($method, $uri, $data = [], $token = null)
    {
        $url = 'http://127.0.0.1:8000' . $uri;
        
        $req = \Illuminate\Support\Facades\Http::acceptJson();
        if ($token) {
            $req->withToken($token);
        }

        if ($uri === '/api/vendor/documents' && $method === 'POST') {
            $res = clone $req;
            $res = $res->attach('file', file_get_contents($data['file']->getPathname()), 'test.pdf')
                       ->post($url, ['type' => $data['type']]);
        } else {
            $res = $req->$method($url, $data);
        }
        
        return [
            'status' => $res->status(),
            'body' => $res->json(),
        ];
    }
}
