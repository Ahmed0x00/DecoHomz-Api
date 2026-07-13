<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class VendorProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::whereNotNull('vendor_id')->with(['vendor', 'category']);

        if ($request->has('vendor_status')) {
            $query->where('vendor_status', $request->vendor_status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show($id)
    {
        $product = Product::whereNotNull('vendor_id')
            ->with(['vendor', 'category', 'images', 'specification'])
            ->findOrFail($id);

        return response()->json($product);
    }

    public function review(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,changes_requested,rejected',
            'comment' => 'required_if:status,changes_requested,rejected|nullable|string',
        ]);

        $product = Product::whereNotNull('vendor_id')->findOrFail($id);
        $oldStatus = $product->vendor_status;

        $product->update([
            'vendor_status' => $validated['status'],
            'is_active' => false,
        ]);

        // Just creating a record in review_histories directly since we don't have polymorphic relation on Product model yet
        // Let's add it via DB
        \DB::table('review_histories')->insert([
            'reviewable_type' => \App\Models\Product::class,
            'reviewable_id' => $product->id,
            'admin_id' => $request->user()->id,
            'from_status' => $oldStatus,
            'to_status' => $validated['status'],
            'comment' => $validated['comment'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Product review status updated.', 'product' => $product]);
    }

    public function unpublish(Request $request, $id)
    {
        $product = Product::whereNotNull('vendor_id')->findOrFail($id);
        
        $product->update([
            'vendor_status' => 'under_review',
            'is_active' => false
        ]);

        return response()->json(['message' => 'Product unpublished and returned to review queue.']);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
