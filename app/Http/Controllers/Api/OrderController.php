<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\ActivityLog;
use App\Models\DepositRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return null;
        $user = auth('sanctum')->user();
        return $user;
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'shippingAddress'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        ActivityLog::orders($request, 'List My Orders', "User viewed their order history");

        return response()->json($orders);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['items.product', 'shippingAddress', 'coupon'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['order' => $order]);
    }

    /**
     * Order confirmation page (web view) — accessible by order ID alone.
     * Works for both guests (via session) and authenticated users.
     */
    public function confirmation(Request $request, string $orderId)
    {
        $order = Order::with(['items.product', 'shippingAddress', 'coupon'])->find($orderId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        return view('orders.confirmation', ['order' => $order]);
    }

    /**
     * Customer order detail page (web view) — clean read-only view.
     * Accessible by order ID alone (no auth required), like the confirmation page.
     * Customers get the link via email after ordering.
     */
    public function customerDetail(Request $request, string $id)
    {
        $order = Order::with(['items.product', 'shippingAddress', 'coupon'])->find($id);

        if (!$order) {
            abort(404, 'Order not found');
        }

        return view('account.orders.show', ['order' => $order]);
    }

    /**
     * Place an order.
     * Works for both authenticated users (via auth token) and guests (via X-Session-ID header).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address_id' => 'required_without:shipping_address|nullable|exists:shipping_addresses,id',
            'shipping_address' => 'required_without:shipping_address_id|nullable|array',
            'shipping_address.first_name' => 'required_with:shipping_address|string|max:50',
            'shipping_address.last_name' => 'required_with:shipping_address|string|max:50',
            'shipping_address.email' => 'required_with:shipping_address|email|max:255',
            'shipping_address.phone' => 'required_with:shipping_address|string|max:20',
            'shipping_address.address_line_1' => 'required_with:shipping_address|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required_with:shipping_address|string|max:100',
            'shipping_address.state' => 'required_with:shipping_address|string|max:100',
            'shipping_address.postal_code' => 'required_with:shipping_address|string|max:20',
            'shipping_address.country' => 'required_with:shipping_address|string|max:100',
            'shipping_address.is_default' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,card,wallet',
        ]);

        $user = $this->getUserFromToken($request);
        $sessionId = $request->header('X-Session-ID');

        // Find cart: user cart OR session cart
        $cartQuery = Cart::query();
        if ($user) {
            $cartQuery->where('user_id', $user->id);
        } elseif ($sessionId) {
            $cartQuery->where('session_id', $sessionId)->whereNull('user_id');
        } else {
            return response()->json(['message' => 'No cart found. Please add items to your cart.'], 422);
        }

        $cart = $cartQuery->with(['items.product', 'coupon'])->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty. Please add items before checkout.'], 422);
        }

        // Check stock for all items
        foreach ($cart->items as $item) {
            $product = $item->product;
            if ($product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Not enough stock for {$product->name}. Available: {$product->stock}",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Create or use shipping address
            $shippingAddressId = $validated['shipping_address_id'] ?? null;
            if (isset($validated['shipping_address'])) {
                $shippingData = $validated['shipping_address'];
                // For guests, user_id is null
                $shippingData['user_id'] = $user ? $user->id : null;

                // Populate the legacy 'address' field for backwards compatibility
                $shippingData['address'] = trim(
                    ($shippingData['address_line_1'] ?? '') . ', ' .
                    ($shippingData['city'] ?? '') . ', ' .
                    ($shippingData['state'] ?? '') . ', ' .
                    ($shippingData['country'] ?? '')
                );
                // governorate is required but frontend doesn't have it — use city as fallback
                if (empty($shippingData['governorate'])) {
                    $shippingData['governorate'] = $shippingData['city'] ?? $shippingData['state'] ?? 'Unknown';
                }

                if ($user && isset($shippingData['is_default']) && $shippingData['is_default']) {
                    ShippingAddress::where('user_id', $user->id)->update(['is_default' => false]);
                }

                // Create the shipping address
                $shippingAddress = new ShippingAddress($shippingData);
                $shippingAddress->save();
                $shippingAddressId = $shippingAddress->id;
            }

            // Calculate totals
            $subtotal = $cart->getSubtotalAttribute();
            $discount = (float) $cart->discount;

            // Determine governorate from shipping address
            $governorate = null;
            if (isset($validated['shipping_address']['governorate'])) {
                $governorate = $validated['shipping_address']['governorate'];
            } elseif (isset($validated['shipping_address']['state'])) {
                $governorate = $validated['shipping_address']['state'];
            } elseif ($shippingAddressId) {
                $shippingAddress = ShippingAddress::find($shippingAddressId);
                $governorate = $shippingAddress?->governorate ?? $shippingAddress?->state;
            }

            // Look up delivery fee by governorate
            $deliveryFee = 0;
            if ($governorate) {
                $feeRecord = \App\Models\GovernorateDeliveryFee::active()
                    ->where('governorate_name', $governorate)
                    ->first();
                if ($feeRecord) {
                    $deliveryFee = $feeRecord->getFeeForSubtotal($subtotal);
                }
            }
            $total = max(0, $subtotal - $discount + $deliveryFee);

            // Calculate VAT first (14% on the pre-deposit base total)
            $vatRate = 0.14;
            $vatAmount = round($total * $vatRate, 2);
            // Grand total = base total + VAT
            $total = max(0, $total + $vatAmount);

            // Calculate deposit from the grand total (subtotal + delivery - discount + VAT)
            $depositAmount = 0;
            $depositRule = DepositRule::getActiveRule();
            if ($depositRule) {
                $depositAmount = round($depositRule->calculateDeposit((float) $total), 2);
            }

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), -6)) . date('ymd');

            // Get coupon_id if a coupon was applied
            $couponId = $cart->coupon ? $cart->coupon->id : null;

            // Create order
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'order_number' => $orderNumber,
                'shipping_address_id' => $shippingAddressId,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => $total,
                'coupon_id' => $couponId,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'unpaid',
                'deposit_amount' => $depositAmount,
                'vat_amount' => $vatAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Increment coupon usage
            if ($cart->coupon) {
                $cart->coupon->increment('used_count');
            }

            // Update shipping address with order_id
            if (isset($shippingAddress) && $shippingAddress) {
                $shippingAddress->order_id = $order->id;
                $shippingAddress->save();
            }

            // Create order items and reduce stock
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'variant' => $item->variant,
                ]);

                // Reduce stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();
            $cart->update(['coupon_code' => null, 'discount' => 0]);

            $logUser = $user ? "User {$user->id}" : "Guest (session: {$sessionId})";
            ActivityLog::orders($request, 'Place Order', "{$logUser} placed order: #{$orderNumber} (Total: {$total} EGP)", $order);

            DB::commit();

            $order->load(['items.product', 'shippingAddress', 'coupon']);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 500)]);
            return response()->json(['message' => 'Failed to place order. Please try again.'], 500);
        }
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Cannot cancel order with status: ' . $order->status,
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);

            ActivityLog::orders($request, 'Cancel Order', "User cancelled their order: #{$order->order_number}", $order);

            DB::commit();

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->fresh(['items.product', 'shippingAddress']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cancel order'], 500);
        }
    }
}
