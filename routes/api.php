<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\GovernorateDeliveryFeeController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\DepositRuleController;
use App\Http\Controllers\Admin\PreOrderController as AdminPreOrderController;
use App\Http\Controllers\Admin\ProductColorController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\PreOrderController;

// Public Delivery Fees
Route::get('/shipping/governorate-fees/active', [GovernorateDeliveryFeeController::class, 'active']);

// WhatsApp Webhook (outside auth & activity log — Meta needs direct access)
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

Route::middleware('activity.log')->group(function () {
    // ============================================
    // PUBLIC ROUTES
    // ============================================

    // Auth (rate limited to prevent brute force)
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    // Categories (Public Read)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Products (Public Read)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/{id}/related', [ProductController::class, 'related']);
    Route::get('/products/{id}/reviews', [ReviewController::class, 'productReviews']);

    // Cart (Public - guest or authenticated)
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{itemId}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon']);
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon']);

    // Orders — public for creation (guests and auth), protected for read/cancel
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Contact (Public)
    Route::post('/contact', [ContactController::class, 'store']);

    // Pre-Order (Public)
    Route::post('/pre-orders', [PreOrderController::class, 'store']);
    Route::get('/pre-orders', [PreOrderController::class, 'index']);

    // Public Settings
    Route::get('/settings', [SettingsController::class, 'publicSettings']);

    // ============================================
    // PROTECTED ROUTES (Requires Authentication)
    // ============================================
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('/auth/password', [AuthController::class, 'updatePassword']);

        // User Addresses
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update']);
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
        Route::patch('/addresses/{id}/set-default', [AddressController::class, 'setDefault']);

        // User Orders
        Route::get('/orders/{id}', [OrderController::class, 'show']);

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy']);
        Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check']);

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    }); // end auth:sanctum

    // ============================================
    // ADMIN ROUTES (token-based auth, outside auth:sanctum)
    // ============================================
    // Support-accessible admin routes (orders, refunds, contacts)
    Route::middleware(['admin.token', 'admin', 'support.access'])->prefix('admin')->group(function () {
        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
        Route::patch('/orders/{id}/payment-status', [AdminOrderController::class, 'updatePaymentStatus']);
        Route::patch('/orders/{id}/tracking', [AdminOrderController::class, 'updateTracking']);

        // Refunds
        Route::get('/refunds', [AdminRefundController::class, 'index']);
        Route::get('/refunds/search-eligible', [AdminRefundController::class, 'searchEligible']);
        Route::post('/refunds/{orderId}/handle', [AdminRefundController::class, 'handle']);
        Route::post('/refunds/create-for-guest', [AdminRefundController::class, 'createForGuest']);

        // Contacts
        Route::get('/contacts', [AdminContactController::class, 'index']);
        Route::get('/contacts/{id}', [AdminContactController::class, 'show']);
        Route::put('/contacts/{id}', [AdminContactController::class, 'update']);
        Route::delete('/contacts/{id}', [AdminContactController::class, 'destroy']);
        Route::patch('/contacts/{id}/replied', [AdminContactController::class, 'markReplied']);

        // Pre-Orders
        Route::get('/pre-orders', [AdminPreOrderController::class, 'index']);
        Route::get('/pre-orders/{id}', [AdminPreOrderController::class, 'show']);
        Route::patch('/pre-orders/{id}/status', [AdminPreOrderController::class, 'updateStatus']);
        Route::patch('/pre-orders/{id}/notes', [AdminPreOrderController::class, 'updateNotes']);
        Route::delete('/pre-orders/{id}', [AdminPreOrderController::class, 'destroy']);
    });

    // Admin-only routes (support.access middleware blocks support users from these paths)
    Route::middleware(['admin.token', 'admin', 'support.access'])->prefix('admin')->group(function () {
        // Categories CRUD
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::get('/categories/{id}', [AdminCategoryController::class, 'show']);
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);
        Route::patch('/categories/{id}/toggle-active', [AdminCategoryController::class, 'toggleActive']);

        // Products CRUD
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::get('/products/{id}', [AdminProductController::class, 'show']);
        Route::put('/products/{id}', [AdminProductController::class, 'update']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
        Route::patch('/products/{id}/toggle-active', [AdminProductController::class, 'toggleActive']);
        Route::patch('/products/{id}/toggle-featured', [AdminProductController::class, 'toggleFeatured']);

        // Product Images
        Route::post('/products/{productId}/images', [ProductImageController::class, 'store']);
        Route::put('/products/{productId}/images/{imageId}', [ProductImageController::class, 'update']);
        Route::delete('/products/{productId}/images/{imageId}', [ProductImageController::class, 'destroy']);
        Route::patch('/products/{productId}/images/{imageId}/set-primary', [ProductImageController::class, 'setPrimary']);

        // Product Colors
        Route::get('/products/{productId}/colors', [ProductColorController::class, 'index']);
        Route::post('/products/{productId}/colors', [ProductColorController::class, 'store']);
        Route::put('/products/{productId}/colors/{colorId}', [ProductColorController::class, 'update']);
        Route::patch('/products/{productId}/colors/{colorId}/toggle', [ProductColorController::class, 'toggleActive']);
        Route::delete('/products/{productId}/colors/{colorId}', [ProductColorController::class, 'destroy']);
        Route::post('/products/{productId}/colors/bulk', [ProductColorController::class, 'bulkUpdate']);

        // Reviews
        Route::get('/reviews', [AdminReviewController::class, 'index']);
        Route::get('/reviews/{id}', [AdminReviewController::class, 'show']);
        Route::post('/reviews', [AdminReviewController::class, 'store']);
        Route::patch('/reviews/{id}/approve', [AdminReviewController::class, 'approve']);
        Route::patch('/reviews/{id}/reject', [AdminReviewController::class, 'reject']);
        Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy']);

        // Coupons
        Route::get('/coupons', [CouponController::class, 'index']);
        Route::get('/coupons/{id}', [CouponController::class, 'show']);
        Route::post('/coupons', [CouponController::class, 'store']);
        Route::put('/coupons/{id}', [CouponController::class, 'update']);
        Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);
        Route::patch('/coupons/{id}/toggle-active', [CouponController::class, 'toggleActive']);

        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Governorate Delivery Fees
        Route::get('/governorate-fees', [GovernorateDeliveryFeeController::class, 'index']);
        Route::get('/governorate-fees/{id}', [GovernorateDeliveryFeeController::class, 'show']);
        Route::post('/governorate-fees', [GovernorateDeliveryFeeController::class, 'store']);
        Route::put('/governorate-fees/{id}', [GovernorateDeliveryFeeController::class, 'update']);
        Route::patch('/governorate-fees/{id}/toggle', [GovernorateDeliveryFeeController::class, 'toggleActive']);
        Route::delete('/governorate-fees/{id}', [GovernorateDeliveryFeeController::class, 'destroy']);
        Route::post('/governorate-fees/bulk', [GovernorateDeliveryFeeController::class, 'bulkUpdate']);

        // Deposit Rules
        Route::get('/deposit-rules', [DepositRuleController::class, 'index']);
        Route::get('/deposit-rules/{id}', [DepositRuleController::class, 'show']);
        Route::post('/deposit-rules', [DepositRuleController::class, 'store']);
        Route::put('/deposit-rules/{id}', [DepositRuleController::class, 'update']);
        Route::patch('/deposit-rules/{id}/toggle', [DepositRuleController::class, 'toggle']);
        Route::delete('/deposit-rules/{id}', [DepositRuleController::class, 'destroy']);

        // Dashboard & Settings
        Route::get('/dashboard', [DashboardController::class, 'stats']);
        Route::get('/charts/orders', [DashboardController::class, 'chartsOrders']);
        Route::get('/charts/revenue', [DashboardController::class, 'chartsRevenue']);
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::get('/settings/{key}', [SettingsController::class, 'show']);
        Route::put('/settings', [SettingsController::class, 'update']);

        // Activity Logs
        Route::get('/logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index']);
        Route::get('/logs/{id}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show']);
        Route::delete('/logs/{id}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'destroy']);
        Route::delete('/logs-clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clear']);
    });
});
