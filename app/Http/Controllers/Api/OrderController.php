<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ShippingAddress;
use App\Models\ActivityLog;
use App\Models\DepositRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken();
        
        // Only fallback to cookie for web views, NOT for API endpoints
        if (!$token && !$request->expectsJson() && !$request->is('api/*')) {
            $token = $request->cookie('dh_token');
        }

        if (!$token) return null;

        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        return $personalAccessToken ? $personalAccessToken->tokenable : null;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->getUserFromToken($request);
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');

        $query = Order::query()->with(['items.product', 'shippingAddress'])->orderBy('created_at', 'desc');

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId)->whereNull('user_id');
        } else {
            return response()->json(['data' => []]);
        }

        $orders = $query->paginate(10);

        ActivityLog::orders($request, 'List My Orders', ActivityLog::userName($request) . " viewed their order history");

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

        $order->items->each(function ($item) {
            $item->append('color');
        });

        return response()->json(['order' => $order]);
    }

    /**
     * Order confirmation page (web view) — accessible by order owner only.
     * Works for both guests (via session) and authenticated users.
     */
    public function confirmation(Request $request, string $orderId)
    {
        $order = Order::with(['items.product', 'shippingAddress', 'coupon'])->find($orderId);

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Verify ownership: authenticated user or guest session
        $user = $this->getUserFromToken($request) ?? auth()->user();
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');
        $isOwner = $user && $order->user_id && (int)$order->user_id === (int)$user->id;
        $isGuest = !$order->user_id && $sessionId && (
            $order->session_id === $sessionId ||
            ($order->shippingAddress && $order->shippingAddress->session_id === $sessionId)
        );

        if (!$isOwner && !$isGuest) {
            abort(403, 'Access denied');
        }

        return view('orders.confirmation', ['order' => $order]);
    }

    /**
     * Track a guest order using Order Number and Email OR Phone.
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'contact' => 'required|string'
        ]);

        $orderNumber = trim($validated['order_number']);
        $contact = trim($validated['contact']);

        $order = Order::where('order_number', $orderNumber)->with(['shippingAddress', 'user'])->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Verify the contact (either email or phone) matches the shipping address
        $matches = false;
        
        $inputPhone = preg_replace('/[^0-9]/', '', $contact);
        $inputEmail = strtolower(trim($contact));

        if ($order->shippingAddress) {
            if (strtolower(trim($order->shippingAddress->email)) === $inputEmail) {
                $matches = true;
            } else {
                $dbPhone = preg_replace('/[^0-9]/', '', $order->shippingAddress->phone);
                if (strlen($dbPhone) >= 8 && strlen($inputPhone) >= 8) {
                    if (str_ends_with($dbPhone, $inputPhone) || str_ends_with($inputPhone, $dbPhone)) {
                        $matches = true;
                    }
                }
            }
        }

        if (!$matches && $order->user) {
            if (strtolower(trim($order->user->email)) === $inputEmail) {
                $matches = true;
            } else {
                $dbPhone = preg_replace('/[^0-9]/', '', $order->user->phone);
                if (strlen($dbPhone) >= 8 && strlen($inputPhone) >= 8) {
                    if (str_ends_with($dbPhone, $inputPhone) || str_ends_with($inputPhone, $dbPhone)) {
                        $matches = true;
                    }
                }
            }
        }

        if (!$matches) {
            return response()->json(['message' => 'The order number and email/phone do not match our records.'], 403);
        }

        if ($order->user_id) {
            return response()->json(['message' => 'This order belongs to a registered account. Please sign in to view it.'], 403);
        }

        // Match successful! Adopt the order to the new session
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id') ?? \Illuminate\Support\Str::uuid()->toString();

        // Update the order's session ID so it appears in this device's "My Orders" tab
        $order->session_id = $sessionId;
        $order->save();

        return response()->json([
            'message' => 'Order verified.',
            'session_id' => $sessionId,
            'redirect_url' => '/account/orders/' . $order->id
        ]);
    }

    /**
     * Customer order detail page (web view) — clean read-only view.
     * Requires authentication — user must own the order.
     */
    public function customerDetail(Request $request, string $id)
    {
        $order = Order::with(['items.product', 'shippingAddress', 'coupon'])->find($id);

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Verify ownership
        $user = $this->getUserFromToken($request) ?? auth()->user();
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');
        $isOwner = $user && $order->user_id && (int)$order->user_id === (int)$user->id;
        $isGuest = !$order->user_id && $sessionId && (
            $order->session_id === $sessionId ||
            ($order->shippingAddress && $order->shippingAddress->session_id === $sessionId)
        );

        \Illuminate\Support\Facades\Log::info('Order customerDetail Access Attempt', [
            'order_id' => $id,
            'order_user_id' => $order->user_id,
            'order_session_id' => $order->session_id,
            'cookie_dh_token' => $request->cookie('dh_token'),
            'cookie_session_id' => $request->cookie('session_id'),
            'resolved_user_id' => $user ? $user->id : null,
            'is_owner' => $isOwner,
            'is_guest' => $isGuest,
        ]);

        if (!$isOwner && !$isGuest) {
            abort(403, 'Access denied');
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
            'shipping_address.governorate' => 'required_with:shipping_address|string|max:100',
            'shipping_address.postal_code' => 'required_with:shipping_address|string|max:20',
            'shipping_address.country' => 'required_with:shipping_address|string|max:100',
            'shipping_address.is_default' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,card,fawry',
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
            $productColor = $item->color;

            if ($productColor) {
                // Check per-color stock
                if ($productColor->stock < $item->quantity) {
                    return response()->json([
                        'message' => "Not enough stock for {$product->name} (color: {$productColor->name}). Available: {$productColor->stock}",
                    ], 422);
                }
            } else {
                // Check product-level stock
                if ($product->stock < $item->quantity) {
                    return response()->json([
                        'message' => "Not enough stock for {$product->name}. Available: {$product->stock}",
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            // Create or use shipping address
            $shippingAddressId = $validated['shipping_address_id'] ?? null;
            if ($shippingAddressId && $user) {
                $ownedAddress = ShippingAddress::where('id', $shippingAddressId)
                    ->where('user_id', $user->id)
                    ->first();
                if (!$ownedAddress) {
                    return response()->json(['message' => 'Invalid shipping address.'], 422);
                }
            }
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
            $affiliateDiscount = (float) $cart->affiliate_discount;

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
                $feeRecord = \App\Models\GovernorateDeliveryFee::resolveByName($governorate);
                if ($feeRecord) {
                    $deliveryFee = $feeRecord->getFeeForSubtotal($subtotal);
                }
            }
            $total = max(0, $subtotal - $discount - $affiliateDiscount + $deliveryFee);

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
                'session_id' => $sessionId,
                'order_number' => $orderNumber,
                'shipping_address_id' => $shippingAddressId,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'affiliate_discount' => $affiliateDiscount,
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

            // Referral creation is moved to after OrderItems are created

            // Update shipping address with order_id
            if (isset($shippingAddress) && $shippingAddress) {
                $shippingAddress->order_id = $order->id;
                $shippingAddress->save();
            }

            // Create order items and reduce stock
            foreach ($cart->items as $item) {
                $productColor = $item->color;
                $itemPrice = $item->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $itemPrice,
                    'quantity' => $item->quantity,
                    'variant' => $item->variant,
                ]);

                // Reduce stock
                if ($productColor) {
                    $productColor->decrement('stock', $item->quantity);
                }
                $item->product->decrement('stock', $item->quantity);
            }

            // Create referral if applicable (must be done after OrderItems are created)
            if ($cart->affiliate_code) {
                // Ensure the items relationship is loaded so createReferral can calculate commission
                $order->load('items.product');
                
                $affiliateService = app(\App\Services\AffiliateService::class);
                $referral = $affiliateService->createReferral($order, $cart->affiliate_code, $user, $request->ip());
                if ($referral) {
                    $order->update(['referral_id' => $referral->id]);
                }
            }

            // Clear cart
            $cart->items()->delete();
            $cart->update(['coupon_code' => null, 'discount' => 0, 'affiliate_code' => null, 'affiliate_discount' => 0]);

            $logUser = ActivityLog::userLabel($user, $sessionId);
            $itemCount = $order->items()->count();
            ActivityLog::orders($request, 'Place Order', "{$logUser} placed order: #{$orderNumber} (Total: {$total} EGP, items: {$itemCount}, payment: {$validated['payment_method']})", $order);

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
        $order = Order::with(['items.product', 'shippingAddress', 'coupon'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Verify ownership: authenticated user or guest session
        $user = $this->getUserFromToken($request);
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');
        $isOwner = $user && $order->user_id && (int)$order->user_id === (int)$user->id;
        $isGuest = !$order->user_id && $sessionId && (
            $order->session_id === $sessionId ||
            ($order->shippingAddress && $order->shippingAddress->session_id === $sessionId)
        );

        if (!$isOwner && !$isGuest) {
            return response()->json(['message' => 'Order not found or access denied'], 404);
        }

        if (!$order->canCancel()) {
            return response()->json([
                'message' => 'Cannot cancel order with status: ' . $order->status,
            ], 422);
        }

        if (in_array($order->payment_status, [Order::PAYMENT_PAID_DEPOSIT, Order::PAYMENT_FULL_PAID])) {
            return response()->json([
                'message' => 'Payment has already been processed for this order. Please use the \'Request Refund\' option instead.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $productColor = ProductColor::where('product_id', $item->product_id)
                        ->where('color_slug', $item->variant)
                        ->first();
                    if ($productColor) {
                        $productColor->increment('stock', $item->quantity);
                    }
                }
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Restore coupon usage
            if ($order->coupon_id && $order->coupon) {
                // Ensure used_count doesn't go below zero
                if ($order->coupon->used_count > 0) {
                    $order->coupon->decrement('used_count');
                }
            }

            // Revoke referral commission
            if ($order->referral_id) {
                $referral = \App\Models\Referral::find($order->referral_id);
                if ($referral) {
                    app(\App\Services\AffiliateService::class)->revokeCommission($referral, 'Order cancelled by user');
                }
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);

            $logUser = ActivityLog::userLabel($user, $sessionId);
            ActivityLog::orders($request, 'Cancel Order', "{$logUser} cancelled order: #{$order->order_number}", $order);

            DB::commit();

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->fresh(['items.product', 'shippingAddress']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order cancellation failed: ' . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 500)]);
            return response()->json(['message' => 'Failed to cancel order'], 500);
        }
    }
}
