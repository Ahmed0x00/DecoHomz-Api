<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::find(81); // the order from previous run
$order->load('items.product');
echo "Items count: " . $order->items->count() . "\n";
$eligibleSubtotal = 0;
$includeVendorProducts = \App\Models\Setting::getValue('affiliate_include_vendor', '0') === '1';
echo "Include Vendor: " . ($includeVendorProducts ? 'yes' : 'no') . "\n";
foreach ($order->items as $item) {
    echo "Item {$item->id}: product {$item->product_id}, vendor {$item->product->vendor_id}\n";
    if (!$includeVendorProducts && $item->product && $item->product->vendor_id !== null) {
        continue;
    }
    $eligibleSubtotal += ($item->price * $item->quantity);
}
echo "Eligible Subtotal: {$eligibleSubtotal}\n";
$affiliateService = app(\App\Services\AffiliateService::class);
echo "Commission: " . $affiliateService->calculateCommission($eligibleSubtotal) . "\n";

