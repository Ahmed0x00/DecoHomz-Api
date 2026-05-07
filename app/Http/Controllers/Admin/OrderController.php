<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product', 'shippingAddress']);

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

        return response()->json(['order' => $order]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);

        // If cancelling, restore stock
        if ($validated['status'] === 'cancelled' && !in_array($order->status, ['cancelled'])) {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order->fresh(['user', 'items.product', 'shippingAddress']),
        ]);
    }

    public function updatePaymentStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $validated['payment_status']]);

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

        return response()->json([
            'message' => 'Tracking information updated',
            'order' => $order->fresh(['user', 'items.product', 'shippingAddress']),
        ]);
    }
}
