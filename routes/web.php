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
Route::get('/contact', fn() => view('contact'));
Route::get('/about', fn() => view('about'));
Route::get('/faq', fn() => view('faq'));
Route::get('/categories', fn() => view('categories'));
Route::get('/deals', fn() => view('deals'));
Route::get('/new-arrivals', fn() => view('new-arrivals'));

// Admin routes (auth handled client-side via JS guard)
Route::prefix('admin')->middleware(['admin.token', 'admin'])->group(function () {
    // Views accessible by both admin and support (orders, refunds, contacts)
    Route::middleware(['support.access'])->group(function () {
        Route::get('/orders', fn() => view('admin.orders.index'));
        Route::get('/orders/{id}', fn($id) => view('admin.orders.show', ['id' => $id]));
        Route::post('/orders/{id}/refund', [App\Http\Controllers\RefundController::class, 'handle']);
        Route::view('/refunds', 'admin.refunds.index');
        Route::get('/contacts', fn() => view('admin.contacts.index'));
    });

    // Admin-only views
    Route::get('/dashboard', fn() => view('admin.dashboard'));
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
    Route::get('/settings', fn() => view('admin.settings'));
});
