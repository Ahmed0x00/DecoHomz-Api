<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = ShippingAddress::where('user_id', $request->user()->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $userId = $user->id;

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            ShippingAddress::where('user_id', $userId)->update(['is_default' => false]);
        }

        $validated['user_id'] = $userId;
        $validated['email'] = $user->email; // Auto-fill from user
        $validated['address'] = $validated['address_line_1']; // Required field
        $validated['governorate'] = $validated['state']; // Required field (old schema)
        $address = ShippingAddress::create($validated);

        ActivityLog::addresses($request, 'Create Address', ActivityLog::userName($request) . " created address: {$address->first_name} {$address->last_name}, {$address->city}, {$address->state}", $address);

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $address = ShippingAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:50',
            'last_name' => 'sometimes|required|string|max:50',
            'phone' => 'sometimes|required|string|max:20',
            'address_line_1' => 'sometimes|required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            ShippingAddress::where('user_id', $request->user()->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        if (isset($validated['state'])) {
            $validated['governorate'] = $validated['state'];
        }

        $address->update($validated);

        ActivityLog::addresses($request, 'Update Address', ActivityLog::userName($request) . " updated address #{$address->id}: {$address->first_name} {$address->last_name}, {$address->city}", $address);

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $address = ShippingAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $address->delete();

        ActivityLog::addresses($request, 'Delete Address', ActivityLog::userName($request) . " deleted address #{$id}: {$address->first_name} {$address->last_name}, {$address->city}", null, 'deletion');

        return response()->json(['message' => 'Address deleted successfully']);
    }

    public function setDefault(Request $request, string $id): JsonResponse
    {
        $address = ShippingAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        ShippingAddress::where('user_id', $request->user()->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        ActivityLog::addresses($request, 'Set Default Address', ActivityLog::userName($request) . " set address #{$address->id} ({$address->first_name} {$address->last_name}, {$address->city}) as default", $address);

        return response()->json([
            'message' => 'Default address updated',
            'address' => $address->fresh(),
        ]);
    }
}
