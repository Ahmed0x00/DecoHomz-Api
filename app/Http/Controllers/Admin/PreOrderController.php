<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use App\Models\PreOrderImage;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PreOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PreOrder::with('images');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $preOrders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($preOrders);
    }

    public function show(string $id): JsonResponse
    {
        $preOrder = PreOrder::with('images')->findOrFail($id);

        return response()->json($preOrder);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,contacted,confirmed,cancelled',
        ]);

        $preOrder = PreOrder::findOrFail($id);
        $preOrder->update(['status' => $validated['status']]);

        ActivityLog::contacts($request, 'Pre-Order Status Updated', "Pre-order #{$preOrder->id} status changed to {$validated['status']}", $preOrder);

        return response()->json([
            'message' => 'Pre-order status updated.',
            'pre_order' => $preOrder->load('images'),
        ]);
    }

    public function updateNotes(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $preOrder = PreOrder::findOrFail($id);
        $preOrder->update(['admin_notes' => $validated['admin_notes'] ?? null]);

        return response()->json([
            'message' => 'Notes updated.',
            'pre_order' => $preOrder,
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $preOrder = PreOrder::with('images')->findOrFail($id);

        foreach ($preOrder->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $preOrder->delete();

        ActivityLog::contacts($request, 'Pre-Order Deleted', "Pre-order #{$id} deleted", null);

        return response()->json(['message' => 'Pre-order deleted.']);
    }
}
