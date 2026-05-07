<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('home'));
Route::get('/shop', fn() => view('shop'));
Route::get('/product/{id}', fn($id) => view('product', ['id' => $id]));
Route::get('/cart', fn() => view('cart'));
Route::get('/checkout', fn() => view('checkout'));
Route::get('/orders/confirmation/{orderId}', fn($orderId) => view('orders.confirmation', ['orderId' => $orderId]));
Route::get('/auth', fn() => view('auth'))->name('login');
Route::get('/account', fn() => view('account'));
Route::get('/contact', fn() => view('contact'));
Route::get('/about', fn() => view('about'));
Route::get('/faq', fn() => view('faq'));
Route::get('/categories', fn() => view('categories'));
Route::get('/deals', fn() => view('deals'));
Route::get('/new-arrivals', fn() => view('new-arrivals'));

// Admin routes (auth handled client-side via JS guard)
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'));
    Route::get('/products', fn() => view('admin.products.index'));
    Route::get('/products/{id}/edit', fn($id) => view('admin.products.edit', ['id' => $id]));
    Route::get('/orders', fn() => view('admin.orders.index'));
    Route::get('/orders/{id}', fn($id) => view('admin.orders.show', ['id' => $id]));
    Route::get('/users', fn() => view('admin.users.index'));
    Route::get('/reviews', fn() => view('admin.reviews.index'));
    Route::get('/coupons', fn() => view('admin.coupons.index'));
    Route::get('/contacts', fn() => view('admin.contacts.index'));
    Route::get('/settings', fn() => view('admin.settings'));
});
