<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::query();

        if ($request->has('search')) {
            ActivityLog::coupons($request, 'Search Coupons', "Admin searched for coupons: {$request->search}");
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('type')) {
            $query->where('discount_type', $request->type);
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($coupons);
    }

    public function show(string $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        return response()->json(['coupon' => $coupon]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'uses_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_order_amount' => $validated['min_order'] ?? $validated['min_order_amount'] ?? null,
            'max_uses' => $validated['uses_limit'] ?? $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'used_count' => 0,
        ]);

        ActivityLog::coupons($request, 'Create Coupon', "Admin created new coupon: {$coupon->code} ({$coupon->discount_value} {$coupon->discount_type})", $coupon);

        return response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:coupons,code,' . $id,
            'discount_type' => 'sometimes|required|in:percentage,fixed',
            'discount_value' => 'sometimes|required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'uses_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        // Map frontend field names to DB column names
        if (isset($validated['min_order'])) {
            $validated['min_order_amount'] = $validated['min_order'];
            unset($validated['min_order']);
        }
        if (isset($validated['uses_limit'])) {
            $validated['max_uses'] = $validated['uses_limit'];
            unset($validated['uses_limit']);
        }
        unset($validated['starts_at']); // not stored in DB

        $coupon->update($validated);

        ActivityLog::coupons($request, 'Update Coupon', "Admin updated coupon: {$coupon->code}", $coupon);

        return response()->json([
            'message' => 'Coupon updated successfully',
            'coupon' => $coupon->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        ActivityLog::coupons($request, 'Delete Coupon', "Admin deleted coupon: {$coupon->code} (#{$id})", ['type' => 'coupon', 'id' => $id], 'deletion');
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    public function toggleActive(Request $request, string $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $newStatus = !$coupon->is_active;
        $coupon->update(['is_active' => $newStatus]);
        
        $statusText = $newStatus ? 'Activated' : 'Deactivated';
        ActivityLog::coupons($request, 'Toggle Coupon Status', "Admin {$statusText} coupon: {$coupon->code}", $coupon);

        return response()->json([
            'message' => 'Coupon ' . ($coupon->is_active ? 'activated' : 'deactivated'),
            'coupon' => $coupon->fresh(),
        ]);
    }
}
