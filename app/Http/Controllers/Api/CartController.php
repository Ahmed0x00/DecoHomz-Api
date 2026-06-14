<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $this->getExistingCart($request);

        if (!$cart) {
            return response()->json([
                'cart' => [
                    'id' => null,
                    'items' => [],
                    'items_count' => 0,
                    'subtotal' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'coupon' => null,
                ],
            ]);
        }

        $cart->load(['items.product', 'coupon']);
        $itemCount = $cart->items->count();

        return response()->json([
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function addItem(StoreCartItemRequest $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        // Resolve color_slug to variant
        $colorSlug = $validated['color_slug'] ?? $validated['variant'] ?? null;
        $productColor = null;

        if ($colorSlug) {
            $productColor = ProductColor::where('product_id', $product->id)
                ->where('color_slug', $colorSlug)
                ->where('is_active', true)
                ->first();

            if (!$productColor) {
                return response()->json([
                    'message' => 'Selected color is not available for this product.',
                ], 422);
            }

            // Check per-color stock
            $requestedQty = $validated['quantity'] ?? 1;
            if ($productColor->stock < $requestedQty) {
                return response()->json([
                    'message' => "Not enough stock for color '{$productColor->name}'. Available: {$productColor->stock}",
                ], 422);
            }
        } else {
            // No color selected — check product-level stock
            if ($product->stock < ($validated['quantity'] ?? 1)) {
                return response()->json([
                    'message' => 'Not enough stock available.',
                ], 422);
            }
        }

        // Check if item already exists in cart (same product + same color)
        $existingItem = $cart->items()->where('product_id', $product->id)
            ->where('variant', $colorSlug)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + ($validated['quantity'] ?? 1);

            if ($productColor) {
                if ($productColor->stock < $newQuantity) {
                    return response()->json([
                        'message' => "Not enough stock for color '{$productColor->name}'. Available: {$productColor->stock}",
                    ], 422);
                }
            } else {
                if ($product->stock < $newQuantity) {
                    return response()->json([
                        'message' => 'Not enough stock available.',
                    ], 422);
                }
            }

            $existingItem->update(['quantity' => $newQuantity]);
            $message = 'Cart item quantity updated';
            ActivityLog::cart($request, 'Update Cart Quantity', "Updated '{$product->name}' quantity to {$newQuantity} in cart", $product);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'] ?? 1,
                'variant' => $colorSlug,
            ]);
            $message = 'Item added to cart';
            ActivityLog::cart($request, 'Add to Cart', "Added '{$product->name}' to shopping cart", $product);
        }

        $cart->recalculateDiscount();
        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => $message,
            'cart' => $this->formatCart($cart),
        ], 201);
    }

    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        $cart = $this->getExistingCart($request);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $validated = $request->validated();
        $cartItem = $cart->items()->where('id', $itemId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        if ($validated['quantity'] === 0) {
            $cartItem->delete();
            $cart->recalculateDiscount();
            return response()->json([
                'message' => 'Item removed from cart',
                'cart' => $this->formatCart($cart->fresh(['items.product', 'coupon'])),
            ]);
        }

        $product = $cartItem->product;
        $colorSlug = $validated['color_slug'] ?? $validated['variant'] ?? $cartItem->variant;
        $productColor = null;

        if ($colorSlug) {
            $productColor = ProductColor::where('product_id', $product->id)
                ->where('color_slug', $colorSlug)
                ->where('is_active', true)
                ->first();

            if (!$productColor) {
                return response()->json([
                    'message' => 'Selected color is not available for this product.',
                ], 422);
            }

            if ($productColor->stock < $validated['quantity']) {
                return response()->json([
                    'message' => "Not enough stock for color '{$productColor->name}'. Available: {$productColor->stock}",
                ], 422);
            }
        } else {
            if ($product->stock < $validated['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock available.',
                ], 422);
            }
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
            'variant' => $colorSlug,
        ]);

        ActivityLog::cart($request, 'Update Cart Item', "Updated quantity for '{$cartItem->product->name}' in cart to {$validated['quantity']}", $cartItem->product);

        $cart->recalculateDiscount();
        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => 'Cart item updated',
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $cart = $this->getExistingCart($request);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cartItem = $cart->items()->where('id', $itemId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        ActivityLog::cart($request, 'Remove from Cart', "Removed '{$cartItem->product->name}' from shopping cart", $cartItem->product);
        $cartItem->delete();
        $cart->recalculateDiscount();

        return response()->json([
            'message' => 'Item removed from cart',
            'cart' => $this->formatCart($cart->fresh(['items.product', 'coupon'])),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getExistingCart($request);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->items()->delete();
        $cart->update(['coupon_code' => null, 'discount' => 0]);

        return response()->json([
            'message' => 'Cart cleared',
            'cart' => $this->formatCart($cart->fresh(['items.product', 'coupon'])),
        ]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $cart = $this->getExistingCart($request);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json(['message' => 'This coupon has expired or reached its usage limit'], 422);
        }

        $subtotal = $cart->getSubtotalAttribute();
        $discount = $coupon->calculateDiscount($subtotal);

        if ($discount <= 0) {
            return response()->json([
                'message' => 'This coupon requires a minimum order of EGP ' . $coupon->min_order_amount,
            ], 422);
        }

        $cart->update([
            'coupon_code' => $coupon->code,
            'discount' => $discount,
        ]);

        ActivityLog::cart($request, 'Apply Coupon', "Applied coupon '{$coupon->code}' to cart. Discount: {$discount} EGP", $coupon);

        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => 'Coupon applied successfully',
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getExistingCart($request);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $oldCoupon = $cart->coupon_code;
        $cart->update(['coupon_code' => null, 'discount' => 0]);

        ActivityLog::cart($request, 'Remove Coupon', "Removed coupon '{$oldCoupon}' from cart");

        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => 'Coupon removed',
            'cart' => $this->formatCart($cart),
        ]);
    }

    /**
     * Get existing cart only (never creates one — for read operations).
     */
    protected function getExistingCart(Request $request): ?Cart
    {
        $user = $request->user() ?? $this->getUserFromToken($request);

        if ($user) {
            return Cart::where('user_id', $user->id)->with(['items.product', 'coupon'])->first();
        }

        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId) {
            return null;
        }

        return Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with(['items.product', 'coupon'])
            ->first();
    }

    /**
     * Get cart for write operations — creates one if it doesn't exist.
     */
    protected function getCart(Request $request): Cart
    {
        $user = $request->user() ?? $this->getUserFromToken($request);

        if ($user) {
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null]
            );
        }

        // For guests, require X-Session-ID header
        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId) {
            // Generate a session ID and return a transient cart (won't be saved until items are added)
            $sessionId = 'guest_' . uniqid();
            $cart = new Cart(['session_id' => $sessionId, 'user_id' => null, 'discount' => 0]);
            $cart->id = null; // ensure it won't match existing records
            return $cart;
        }

        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null],
            []
        );
    }

    /**
     * Ensure we have a saved cart for write operations.
     */
    protected function resolveCart(Request $request): Cart
    {
        $cart = $this->getCart($request);
        if (!$cart->id) {
            // Transient cart — save it now
            $cart->save();
        }
        return $cart;
    }

    protected function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token)
            return null;

        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$personalAccessToken)
            return null;

        return $personalAccessToken->tokenable;
    }

    protected function formatCart(Cart $cart): array
    {
        $items = $cart->items->map(function ($item) {
            $productColor = $item->color;
            $price = $item->price;

            $result = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $price,
                'quantity' => $item->quantity,
                'variant' => $item->variant,
                'subtotal' => $item->getTotalAttribute(),
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'image' => $item->product->primaryImage?->image,
                ],
            ];

            if ($productColor) {
                $result['color'] = [
                    'id' => $productColor->id,
                    'name' => $productColor->name,
                    'hex_code' => $productColor->hex_code,
                    'color_slug' => $productColor->color_slug,
                    'price_modifier' => (float) $productColor->price_modifier,
                ];
            } else {
                $result['color'] = null;
            }

            return $result;
        });

        $subtotal = $cart->getSubtotalAttribute();
        $discount = (float) $cart->discount;
        $total = max(0, $subtotal - $discount);

        return [
            'id' => $cart->id,
            'items' => $items,
            'items_count' => $cart->getTotalItemsAttribute(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $cart->coupon ? [
                'code' => $cart->coupon->code,
                'discount_type' => $cart->coupon->discount_type,
                'discount_value' => $cart->coupon->discount_value,
            ] : null,
        ];
    }
}
