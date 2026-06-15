<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductColor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Customer: request a refund for an order.
     * Accessible by auth token (same pattern as order placement).
     */
    public function request(Request $request, string $orderId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = auth()->user() ?: (request()->bearerToken() ? auth('sanctum')->user() : null);
        $sessionId = $request->header('X-Session-ID');

        $order = Order::find($orderId);

        if (!$order) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Order not found.'], 404);
            }
            return redirect()->back()->with('error', 'Order not found.');
        }

        // Authorize: user owns the order OR guest with matching session
        $isOwner = $user && (int)$order->user_id === (int)$user->id;
        $isGuest = !$user && $sessionId && $order->shippingAddress
            && $order->shippingAddress->session_id === $sessionId;

        if (!$isOwner && !$isGuest) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Access denied.'], 403);
            }
            return redirect()->back()->with('error', 'Access denied.');
        }

        if (!$order->canRequestRefund()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This order is not eligible for a refund.'], 422);
            }
            return redirect()->back()->with('error', 'This order is not eligible for a refund.');
        }

        $order->update([
            'refund_status' => Order::REFUND_PENDING,
            'refund_reason' => $validated['reason'],
        ]);

        $logUser = $user ? "User {$user->id}" : "Guest";
        ActivityLog::orders($request, 'Request Refund', "{$logUser} requested a refund for order #{$order->order_number}. Reason: {$validated['reason']}", $order);

        if ($request->expectsJson()) {
            return response()->json(['success' => 'Refund request submitted.']);
        }
        return redirect()->back()->with('success', 'Refund request submitted. We will review it shortly.');
    }

    /**
     * Admin: handle a refund request (approve or reject).
     */
    public function handle(Request $request, string $orderId)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $order = Order::with(['items.product'])->find($orderId);

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        if ($order->refund_status !== Order::REFUND_PENDING) {
            return redirect()->back()->with('error', 'No pending refund request for this order.');
        }

        $action = $validated['action'];

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $order->update([
                    'payment_status' => Order::PAYMENT_REFUNDED,
                    'refund_status' => Order::REFUND_APPROVED,
                    'refund_handled_at' => now(),
                ]);

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

                ActivityLog::orders($request, 'Approve Refund', "Admin approved refund for order #{$order->order_number}. Refund reason: {$order->refund_reason}.", $order);
                $msg = 'Refund approved. Payment marked as refunded and stock restored.';
            } else {
                $order->update([
                    'refund_status' => Order::REFUND_REJECTED,
                    'refund_handled_at' => now(),
                ]);

                ActivityLog::orders($request, 'Reject Refund', "Admin rejected refund for order #{$order->order_number}. Reason given: {$order->refund_reason}.", $order);
                $msg = 'Refund request rejected.';
            }

            DB::commit();
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process refund. Please try again.');
        }
    }
}
