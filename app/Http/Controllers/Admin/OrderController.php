<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductColor;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product', 'shippingAddress']);

        if ($request->has('search')) {
            ActivityLog::orders($request, 'Search Orders', ActivityLog::userName($request) . " searched for orders with query: {$request->search}");
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($orders);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::with(['user', 'items.product', 'shippingAddress', 'coupon'])->findOrFail($id);

        return response()->json([
            'data' => $order->load(['user', 'items.product', 'shippingAddress', 'coupon']),
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;

        // If cancelling, restore stock
        if ($validated['status'] === 'cancelled' && !in_array($order->status, ['cancelled'])) {
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $productColor = ProductColor::where('product_id', $item->product_id)
                        ->where('color_slug', $item->variant)->first();
                    if ($productColor) {
                        $productColor->increment('stock', $item->quantity);
                    }
                }
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $validated['status']]);

        ActivityLog::orders($request, 'Update Order Status', ActivityLog::userName($request) . " changed order #{$order->order_number} status from {$oldStatus} to {$validated['status']}", $order);

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order->fresh(['user', 'items.product', 'shippingAddress']),
        ]);
    }

    public function updatePaymentStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,paid_deposit,full_paid',
        ]);

        $order = Order::findOrFail($id);
        $oldPayStatus = $order->payment_status;
        $order->update(['payment_status' => $validated['payment_status']]);

        ActivityLog::orders($request, 'Update Payment Status', ActivityLog::userName($request) . " changed order #{$order->order_number} payment status from {$oldPayStatus} to {$validated['payment_status']}", $order);

        return response()->json([
            'message' => 'Payment status updated',
            'order' => $order->fresh(['user', 'items.product', 'shippingAddress']),
        ]);
    }

    public function updateTracking(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
        ]);

        $order = Order::findOrFail($id);
        $order->update($validated);

        ActivityLog::orders($request, 'Update Tracking', ActivityLog::userName($request) . " updated tracking info for order #{$order->order_number} (Tracking #: {$validated['tracking_number']})", $order);

        return response()->json([
            'message' => 'Tracking information updated',
            'order' => $order->fresh(['user', 'items.product', 'shippingAddress']),
        ]);
    }
}
