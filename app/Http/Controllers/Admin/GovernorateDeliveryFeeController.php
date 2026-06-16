<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernorateDeliveryFee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;

class GovernorateDeliveryFeeController extends Controller
{
    /**
     * Display a listing of delivery fees.
     */
    public function index(Request $request)
    {
        $query = GovernorateDeliveryFee::query()->orderBy('sort_order');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('governorate_name', 'like', "%{$search}%")
                    ->orWhere('governorate_name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $fees = $query->get();
        return response()->json($fees);
    }

    /**
     * Get all active fees (for dropdowns).
     */
    public function active()
    {
        $fees = GovernorateDeliveryFee::active()
            ->orderBy('sort_order')
            ->get(['id', 'governorate_name', 'governorate_name_ar', 'delivery_fee', 'min_free_delivery_order', 'is_active']);

        return response()->json(['fees' => $fees]);
    }

    /**
     * Get a specific fee.
     */
    public function show($id)
    {
        $fee = GovernorateDeliveryFee::findOrFail($id);
        return response()->json($fee);
    }

    /**
     * Store a new governorate delivery fee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'governorate_name' => 'required|string|max:100|unique:governorate_delivery_fees,governorate_name',
            'governorate_name_ar' => 'nullable|string|max:100',
            'delivery_fee' => 'required|numeric|min:0',
            'min_free_delivery_order' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $fee = GovernorateDeliveryFee::create($validated);

        ActivityLog::deliveryFees($request, 'Create Delivery Fee', ActivityLog::userName($request) . " created delivery fee for {$fee->governorate_name}: {$fee->delivery_fee} EGP", $fee);

        return response()->json($fee, 201);
    }

    /**
     * Update a governorate delivery fee.
     */
    public function update(Request $request, $id)
    {
        $fee = GovernorateDeliveryFee::findOrFail($id);

        $validated = $request->validate([
            'governorate_name' => ['nullable', 'string', 'max:100', Rule::unique('governorate_delivery_fees')->ignore($fee->id)],
            'governorate_name_ar' => 'nullable|string|max:100',
            'delivery_fee' => 'required|numeric|min:0',
            'min_free_delivery_order' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $fee->update($validated);

        ActivityLog::deliveryFees($request, 'Update Delivery Fee', ActivityLog::userName($request) . " updated delivery fee for {$fee->governorate_name}: {$fee->delivery_fee} EGP", $fee);

        return response()->json($fee);
    }

    /**
     * Delete a governorate delivery fee.
     */
    public function destroy(Request $request, $id)
    {
        $fee = GovernorateDeliveryFee::findOrFail($id);
        $name = $fee->governorate_name;
        $fee->delete();

        ActivityLog::deliveryFees($request, 'Delete Delivery Fee', ActivityLog::userName($request) . " deleted delivery fee for {$name}", ['type' => 'governorate_delivery_fee', 'id' => $id], 'deletion');

        return response()->json(['message' => 'Delivery fee deleted']);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Request $request, $id)
    {
        $fee = GovernorateDeliveryFee::findOrFail($id);
        $fee->is_active = !$fee->is_active;
        $fee->save();

        $status = $fee->is_active ? 'activated' : 'deactivated';
        ActivityLog::deliveryFees($request, 'Toggle Delivery Fee', ActivityLog::userName($request) . " {$status} delivery fee for {$fee->governorate_name}", $fee);

        return response()->json($fee);
    }

    /**
     * Bulk update fees.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'fees' => 'required|array',
            'fees.*.id' => 'required|exists:governorate_delivery_fees,id',
            'fees.*.delivery_fee' => 'required|numeric|min:0',
            'fees.*.min_free_delivery_order' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['fees'] as $feeData) {
            GovernorateDeliveryFee::where('id', $feeData['id'])->update([
                'delivery_fee' => $feeData['delivery_fee'],
                'min_free_delivery_order' => $feeData['min_free_delivery_order'] ?? 0,
            ]);
        }

        ActivityLog::deliveryFees($request, 'Bulk Update Delivery Fees', ActivityLog::userName($request) . " bulk updated " . count($validated['fees']) . " delivery fees");

        return response()->json(['message' => 'Fees updated successfully']);
    }
}
