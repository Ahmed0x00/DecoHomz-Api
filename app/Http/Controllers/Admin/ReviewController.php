<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['product.primaryImage', 'user']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('approved')) {
            $approved = $request->boolean('approved');
            $query->where('is_approved', $approved);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($reviews);
    }

    public function show(string $id): JsonResponse
    {
        $review = Review::with(['product.primaryImage', 'user'])->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json(['review' => $review]);
    }

    public function approve(string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->update(['is_approved' => true, 'is_rejected' => false]);

        return response()->json([
            'message' => 'Review approved',
            'review' => $review->fresh(),
        ]);
    }

    public function reject(string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->update(['is_approved' => false, 'is_rejected' => true]);

        return response()->json([
            'message' => 'Review rejected',
            'review' => $review->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
