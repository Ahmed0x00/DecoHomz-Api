<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'shippingAddress'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address_id' => 'required_without:shipping_address|exists:shipping_addresses,id',
            'shipping_address' => 'required_without:shipping_address_id|array',
            'shipping_address.first_name' => 'required_with:shipping_address|string|max:50',
            'shipping_address.last_name' => 'required_with:shipping_address|string|max:50',
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

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->with(['items.product', 'coupon'])->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 422);
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
                $shippingData['user_id'] = $user->id;
                if (isset($shippingData['is_default']) && $shippingData['is_default']) {
                    ShippingAddress::where('user_id', $user->id)->update(['is_default' => false]);
                }
                $shippingAddress = ShippingAddress::create($shippingData);
                $shippingAddressId = $shippingAddress->id;
            }

            // Calculate totals
            $subtotal = $cart->getSubtotalAttribute();
            $discount = (float) $cart->discount;
            $shippingCost = 0; // Free shipping for now
            $total = $subtotal - $discount + $shippingCost;

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), -6)) . date('ymd');

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'shipping_address_id' => $shippingAddressId,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'coupon_code' => $cart->coupon_code,
                'coupon_discount' => $discount,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items and reduce stock
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'variant' => $item->variant,
                    'total' => $item->getTotalAttribute(),
                ]);

                // Reduce stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();
            $cart->update(['coupon_code' => null, 'discount' => 0]);

            DB::commit();

            $order->load(['items.product', 'shippingAddress', 'coupon']);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to place order. Please try again.', 'error' => $e->getMessage()], 500);
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
