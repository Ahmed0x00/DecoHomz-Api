<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function addItem(StoreCartItemRequest $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        // Check stock
        if ($product->stock < ($validated['quantity'] ?? 1)) {
            return response()->json([
                'message' => 'Not enough stock available.',
            ], 422);
        }

        // Check if item already exists in cart
        $existingItem = $cart->items()->where('product_id', $product->id)
            ->where('variant', $validated['variant'] ?? null)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + ($validated['quantity'] ?? 1);
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'message' => 'Not enough stock available.',
                ], 422);
            }
            $existingItem->update(['quantity' => $newQuantity]);
            $message = 'Cart item quantity updated';
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'] ?? 1,
                'variant' => $validated['variant'] ?? null,
            ]);
            $message = 'Item added to cart';
        }

        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => $message,
            'cart' => $this->formatCart($cart),
        ], 201);
    }

    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        $cart = $this->getCart($request);
        $validated = $request->validated();

        $cartItem = $cart->items()->where('id', $itemId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        if ($validated['quantity'] === 0) {
            $cartItem->delete();
            return response()->json([
                'message' => 'Item removed from cart',
                'cart' => $this->formatCart($cart->fresh(['items.product', 'coupon'])),
            ]);
        }

        // Check stock
        $product = $cartItem->product;
        if ($product->stock < $validated['quantity']) {
            return response()->json([
                'message' => 'Not enough stock available.',
            ], 422);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
            'variant' => $validated['variant'] ?? $cartItem->variant,
        ]);

        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => 'Cart item updated',
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $cart = $this->getCart($request);

        $cartItem = $cart->items()->where('id', $itemId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart',
            'cart' => $this->formatCart($cart->fresh(['items.product', 'coupon'])),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
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

        $cart = $this->getCart($request);

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

        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => 'Coupon applied successfully',
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart->update(['coupon_code' => null, 'discount' => 0]);

        $cart->load(['items.product', 'coupon']);

        return response()->json([
            'message' => 'Coupon removed',
            'cart' => $this->formatCart($cart),
        ]);
    }

    protected function getCart(Request $request): Cart
    {
        $user = $request->user() ?? $this->getUserFromToken($request);
        
        // For authenticated users, use user_id
        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null]
            );
        } else {
            // For guests, use X-Session-ID header or generate a UUID stored in header
            $sessionId = $request->header('X-Session-ID');
            if (!$sessionId) {
                $sessionId = 'guest_' . uniqid();
            }
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'user_id' => null],
                []
            );
        }

        return $cart;
    }
    
    protected function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return null;
        
        // Try to find user from token
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$personalAccessToken) return null;
        
        return $personalAccessToken->tokenable;
    }

    protected function formatCart(Cart $cart): array
    {
        $items = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
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
