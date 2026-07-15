<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Affiliate;
use App\Models\Product;

$baseUrl = 'http://127.0.0.1:8081/api';

echo "1. Getting Affiliate Code...\n";
$affiliateUser = User::where('email', 'dd@g.c')->first();
if (!$affiliateUser) {
    die("Affiliate user not found.\n");
}
$affiliate = Affiliate::where('user_id', $affiliateUser->id)->first();
if (!$affiliate) {
    die("Affiliate record not found for dd@g.c.\n");
}
$code = $affiliate->referral_code;
echo "Found Code: $code\n\n";

echo "2. Logging in as buyer (ahmed@decohomz.com)...\n";
$loginRes = Http::post("$baseUrl/auth/login", [
    'email' => 'ahmed@decohomz.com',
    'password' => 'password',
]);
if (!$loginRes->successful()) {
    die("Login failed: " . $loginRes->body() . "\n");
}
$token = $loginRes->json('token');
$authHeaders = ['Authorization' => "Bearer $token"];
echo "Logged in. Token: $token\n\n";

echo "3. Finding a product...\n";
$product = Product::where('is_active', true)->where('stock', '>', 5)->first();
if (!$product) die("No product found.\n");
echo "Found Product: {$product->name} (Price: {$product->price})\n\n";

echo "4. Adding to cart...\n";
$cartRes = Http::withHeaders($authHeaders)->post("$baseUrl/cart/items", [
    'product_id' => $product->id,
    'quantity' => 1,
]);
if (!$cartRes->successful()) {
    die("Add to cart failed: " . $cartRes->body() . "\n");
}
$cartId = $cartRes->json('cart.id');
echo "Added to cart (ID: $cartId). Cart Total: " . $cartRes->json('cart.total') . "\n\n";

echo "5. Applying Affiliate Code...\n";
$applyRes = Http::withHeaders($authHeaders)->post("$baseUrl/cart/affiliate", [
    'code' => $code,
]);
if (!$applyRes->successful()) {
    die("Apply code failed: " . $applyRes->body() . "\n");
}
echo "Code applied successfully!\n";
echo "Cart Affiliate Discount: " . $applyRes->json('cart.affiliate.discount') . "\n";
echo "Cart Subtotal: " . $applyRes->json('cart.subtotal') . "\n";
echo "Cart Total: " . $applyRes->json('cart.total') . "\n\n";

echo "6. Checking out...\n";
$checkoutPayload = [
    'shipping_address' => [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'ahmed@decohomz.com',
        'phone' => '01000000000',
        'address_line_1' => '123 Test St',
        'city' => 'Cairo',
        'state' => 'Cairo',
        'governorate' => 'Cairo',
        'postal_code' => '12345',
        'country' => 'Egypt',
    ],
    'payment_method' => 'cod',
];
echo "POST /api/orders\nPayload: " . json_encode($checkoutPayload) . "\n";
$orderRes = Http::withHeaders($authHeaders)->acceptJson()->post("$baseUrl/orders", $checkoutPayload);

echo "Response Status: " . $orderRes->status() . "\n";
if (!$orderRes->successful()) {
    $err = $orderRes->json() ?: substr($orderRes->body(), 0, 500);
    die("Checkout failed: " . json_encode($err) . "\n");
}
$order = $orderRes->json('order');
if (!$order) {
    $err = $orderRes->json() ?: substr($orderRes->body(), 0, 500);
    die("Order was null! Full response: " . json_encode($err) . "\n");
}
echo "Response Body: " . json_encode(['message' => 'Order placed successfully', 'order_id' => $order['id'], 'total' => $order['total']]) . "\n\n";

echo "--- ORDER VERIFICATION ---\n";
echo "Order Created! ID: {$order['id']} Number: {$order['order_number']}\n";
echo "Order Subtotal: {$order['subtotal']}\n";
echo "Order Affiliate Discount: {$order['affiliate_discount']}\n";
echo "Order Total (incl. VAT): {$order['total']}\n";
$refId = $order['referral_id'] ?? null;
echo "Order Referral ID: " . var_export($refId, true) . "\n\n";

if ($refId) {
    echo "7. Checking Referral Record...\n";
    $ref = \App\Models\Referral::find($refId);
    echo "Commission Amount: {$ref->commission_amount}\n";
    echo "Commission Status: {$ref->commission_status}\n";
} else {
    echo "REFERRAL WAS NOT CREATED!\n";
}

// Clean up cart for next run
Http::withHeaders($authHeaders)->delete("$baseUrl/cart/items");
