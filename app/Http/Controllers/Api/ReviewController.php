<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with(['product.primaryImage', 'user'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['reviews' => $reviews]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $userId = $request->user()->id;

        $exists = Review::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already reviewed this product'], 409);
        }

        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $validated['product_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully. It will be visible after approval.',
            'review' => $review,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $review = Review::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review->update($validated);
        $review->update(['is_approved' => false]);

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $review = Review::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function productReviews(string $productId): JsonResponse
    {
        $reviews = Review::with(['user:id,name'])
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $avgRating = $reviews->avg('rating');
        $stats = [
            'average' => round($avgRating, 1),
            'count' => $reviews->count(),
            '1' => $reviews->where('rating', 1)->count(),
            '2' => $reviews->where('rating', 2)->count(),
            '3' => $reviews->where('rating', 3)->count(),
            '4' => $reviews->where('rating', 4)->count(),
            '5' => $reviews->where('rating', 5)->count(),
        ];

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
}
