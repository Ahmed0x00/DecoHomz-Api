<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('home'));
Route::get('/shop', fn() => view('shop'));
Route::get('/product/{id}', function($id) {
    $product = \App\Models\Product::where('id', $id)
        ->orWhere('slug', $id)
        ->with(['category', 'primaryImage', 'images', 'activeColors'])
        ->firstOrFail();
    return view('product', ['product' => $product]);
});
Route::get('/cart', fn() => view('cart'));
Route::get('/checkout', fn() => view('checkout'));
Route::get('/orders/confirmation/{orderId}', [App\Http\Controllers\Api\OrderController::class, 'confirmation']);
Route::get('/auth', fn() => view('auth'))->name('login');
Route::get('/account', fn() => view('account'));
Route::get('/account/orders/{id}', [App\Http\Controllers\Api\OrderController::class, 'customerDetail']);
Route::post('/account/orders/{id}/refund', [App\Http\Controllers\RefundController::class, 'request']);
Route::get('/account/pre-orders/{id}', [App\Http\Controllers\Api\PreOrderController::class, 'customerDetail']);
Route::get('/contact', fn() => view('contact'));
Route::get('/about', fn() => view('about'));
Route::get('/faq', fn() => view('faq'));
Route::get('/track-order', fn() => view('track-order'));
Route::get('/categories', fn() => view('categories'));
Route::get('/deals', fn() => view('deals'));
Route::get('/new-arrivals', fn() => view('new-arrivals'));
Route::get('/privacy', fn() => view('privacy'));
Route::get('/terms', fn() => view('terms'));
Route::get('/pre-order', fn() => view('pre-order'));
Route::get('/pre-order/confirmed', fn() => view('pre-order-confirmation'));
Route::get('/vendor/apply', fn() => view('vendor-apply'));
Route::get('/vendor/portal', fn() => view('vendor.portal.index'));
Route::get('/vendor-terms', fn() => view('vendor-terms'));

// Admin routes (auth handled client-side via JS guard)
Route::prefix('admin')->middleware(['admin.token', 'admin'])->group(function () {
    // Views accessible by both admin and support (orders, refunds, contacts)
    Route::middleware(['support.access'])->group(function () {
        Route::get('/orders', fn() => view('admin.orders.index'));
        Route::get('/orders/{id}', fn($id) => view('admin.orders.show', ['id' => $id]));
        Route::post('/orders/{id}/refund', [App\Http\Controllers\RefundController::class, 'handle']);
        Route::view('/refunds', 'admin.refunds.index');
        Route::get('/contacts', fn() => view('admin.contacts.index'));
        Route::get('/pre-orders', fn() => view('admin.pre-orders.index'));
        Route::get('/pre-orders/{id}', fn($id) => view('admin.pre-orders.show', ['id' => $id]));
    });

    // Admin-only views
    Route::get('/dashboard', fn() => view('admin.dashboard'));
    Route::get('/analytics', fn() => view('admin.analytics'));
    Route::get('/products', fn() => view('admin.products.index'));
    Route::get('/products/create', fn() => view('admin.products.create'));
    Route::get('/products/{id}/edit', fn($id) => view('admin.products.edit', ['id' => $id]));
    Route::get('/categories', fn() => view('admin.categories.index'));
    Route::get('/users', fn() => view('admin.users.index'));
    Route::get('/users/create', fn() => view('admin.users.create'));
    Route::get('/users/{id}', fn($id) => view('admin.users.show', ['id' => $id]));
    Route::get('/users/{id}/edit', fn($id) => view('admin.users.edit', ['id' => $id]));
    Route::get('/reviews', fn() => view('admin.reviews.index'));
    Route::get('/reviews/create', fn() => view('admin.reviews.create'));
    Route::get('/coupons', fn() => view('admin.coupons.index'));
    Route::get('/delivery-fees', fn() => view('admin.delivery-fees.index'));
    Route::get('/deposit-rules', fn() => view('admin.deposit-rules.index'));
    Route::get('/logs', fn() => view('admin.logs.index'));
    
    // Vendor System Views
    Route::get('/vendors', fn() => view('admin.vendors.index'));
    Route::get('/vendors/{id}', fn($id) => view('admin.vendors.show', ['id' => $id]));
    Route::get('/vendor-products', fn() => view('admin.vendor-products.index'));
    Route::get('/vendor-products/{id}', fn($id) => view('admin.vendor-products.show', ['id' => $id]));
    Route::get('/warehouse', fn() => view('admin.warehouse.index'));
    Route::get('/warehouse/inspect/{id}', fn($id) => view('admin.warehouse.inspect', ['id' => $id]));
    Route::get('/vendor-finances', fn() => view('admin.vendor-finances.index'));
    
    // Affiliate Admin Views
    Route::get('/affiliates', fn() => view('admin.affiliates.index'));
    Route::get('/affiliates/{id}', fn($id) => view('admin.affiliates.show', ['id' => $id]));
    Route::get('/referrals', fn() => view('admin.referrals.index'));
    Route::get('/referrals/{id}', fn($id) => view('admin.referrals.show', ['id' => $id]));
    
    Route::get('/settings', fn() => view('admin.settings'));
});
