<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WarehouseInspection;
use App\Models\Product;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseInspection::with(['vendor', 'product', 'inspector']);

        if ($request->has('result')) {
            $query->where('inspection_result', $request->result);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show($id)
    {
        $inspection = WarehouseInspection::with(['vendor', 'product', 'inspector'])->findOrFail($id);
        return response()->json($inspection);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'expected_quantity' => 'required|integer|min:1',
            'received_quantity' => 'required|integer|min:0',
            'accepted_quantity' => 'required|integer|min:0',
            'inspector_notes' => 'nullable|string',
        ]);

        $product = Product::whereNotNull('vendor_id')->findOrFail($validated['product_id']);

        if ($product->vendor_status !== 'approved') {
            return response()->json(['message' => 'Only products approved for warehouse inspection can be inspected.'], 422);
        }

        if ($validated['accepted_quantity'] > $validated['received_quantity']) {
            return response()->json(['message' => 'Accepted quantity cannot exceed received quantity.'], 422);
        }
        
        $rejected_quantity = $validated['received_quantity'] - $validated['accepted_quantity'];
        
        $result = 'passed';
        if ($validated['accepted_quantity'] == 0) {
            $result = 'failed';
        } elseif ($validated['accepted_quantity'] < $validated['expected_quantity']) {
            $result = 'partial_pass';
        }

        $inspection = WarehouseInspection::create([
            'vendor_id' => $product->vendor_id,
            'product_id' => $product->id,
            'inspector_id' => $request->user()->id,
            'expected_quantity' => $validated['expected_quantity'],
            'received_quantity' => $validated['received_quantity'],
            'accepted_quantity' => $validated['accepted_quantity'],
            'rejected_quantity' => $rejected_quantity,
            'inspection_result' => $result,
            'inspector_notes' => $validated['inspector_notes'] ?? null,
            'inspected_at' => now(),
        ]);

        // Post inspection logic
        if ($validated['accepted_quantity'] > 0) {
            $product->update([
                'vendor_status' => 'published',
                'is_active' => true,
                'stock' => $validated['accepted_quantity']
            ]);
        } else {
            $product->update([
                'vendor_status' => 'rejected',
                'is_active' => false,
            ]);

            // Issue violation automatically
            app(\App\Services\VendorViolationService::class)->issueViolation($product->vendor, [
                'admin_id' => $request->user()->id,
                'product_id' => $product->id,
                'violation_type' => 'quality_failure',
                'description' => 'Product failed warehouse inspection completely.',
                'severity_points' => 3,
                'action_taken' => 'warning',
            ]);
        }

        return response()->json(['message' => 'Inspection logged successfully.', 'inspection' => $inspection], 201);
    }
}
