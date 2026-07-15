<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://127.0.0.1:8081/api';

echo "1. Logging in as admin...\n";
$loginRes = Http::post("$baseUrl/auth/login", [
    'email' => 'admin@decohomz.com',
    'password' => 'password',
]);
if (!$loginRes->successful()) {
    die("Login failed: " . $loginRes->body() . "\n");
}
$token = $loginRes->json('token');
$authHeaders = ['Authorization' => "Bearer $token"];
echo "Logged in. Token: $token\n\n";

echo "2. Fetching /api/admin/referrals without status...\n";
$res1 = Http::withHeaders($authHeaders)->acceptJson()->get("$baseUrl/admin/referrals");
echo "Status: " . $res1->status() . "\n";
echo "Body: " . json_encode($res1->json(), JSON_PRETTY_PRINT) . "\n\n";

echo "3. Fetching /api/admin/referrals with status empty string...\n";
$res2 = Http::withHeaders($authHeaders)->acceptJson()->get("$baseUrl/admin/referrals", ['status' => '']);
echo "Status: " . $res2->status() . "\n";
echo "Body: " . json_encode($res2->json(), JSON_PRETTY_PRINT) . "\n\n";
