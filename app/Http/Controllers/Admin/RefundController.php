<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product', 'shippingAddress'])
            ->whereNotNull('refund_status');

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by refund status
        if ($request->has('refund_status') && $request->refund_status) {
            $query->where('refund_status', $request->refund_status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->orderBy('refund_handled_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    public function handle(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $order = Order::with(['items.product'])->find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        if ($order->refund_status !== 'pending') {
            return response()->json(['error' => 'No pending refund request for this order.'], 422);
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
                    $item->product->increment('stock', $item->quantity);
                }

                ActivityLog::orders($request, 'Approve Refund', "Admin approved refund for order #{$order->order_number}. Reason: {$order->refund_reason}.", $order);
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
            return response()->json(['success' => $msg]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process refund. Please try again.'], 500);
        }
    }

    /**
     * Admin creates a refund request for a guest order by order ID.
     */
    public function createForGuest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'reason' => 'required|string|max:500',
        ]);

        $order = Order::find($validated['order_id']);

        if (!$order->canRequestRefund()) {
            return response()->json([
                'error' => 'This order is not eligible for a refund. Only orders with "Paid Deposit" or "Full Paid" payment status can be refunded.'
            ], 422);
        }

        $order->update([
            'refund_status' => Order::REFUND_PENDING,
            'refund_reason' => $validated['reason'] . ' [Created by admin]',
        ]);

        ActivityLog::orders($request, 'Create Refund Request', "Admin created refund request for guest order #{$order->order_number}. Reason: {$validated['reason']}.", $order);

        return response()->json([
            'success' => "Refund request created for order #{$order->order_number}."
        ]);
    }

    /**
     * Search eligible orders for the "Create Refund" dropdown.
     * Returns orders that are paid_deposit/full_paid with no active refund request.
     */
    public function searchEligible(Request $request): JsonResponse
    {
        $q = $request->get('q', '');

        $orders = Order::with(['user', 'shippingAddress'])
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->where(function ($query) {
                $query->whereNull('refund_status')
                    ->orWhere('refund_status', Order::REFUND_REJECTED);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', "%{$q}%")
                        ->orWhere('id', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($uq) use ($q) {
                            $uq->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('shippingAddress', function ($sq) use ($q) {
                            $sq->where('name', 'like', "%{$q}%")
                               ->orWhere('email', 'like', "%{$q}%")
                               ->orWhere('phone', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($orders);
    }
}
