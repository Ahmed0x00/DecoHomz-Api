<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ActivityLog;
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

    public function approve(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->update(['is_approved' => true, 'is_rejected' => false]);

        $product = $review->product;
        ActivityLog::reviews($request, 'Approve Review', ActivityLog::userName($request) . " approved review #{$review->id} for product '{$product?->name}' (rating: {$review->rating})", $review);

        return response()->json([
            'message' => 'Review approved',
            'review' => $review->fresh(),
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->update(['is_approved' => false, 'is_rejected' => true]);

        $product = $review->product;
        ActivityLog::reviews($request, 'Reject Review', ActivityLog::userName($request) . " rejected review #{$review->id} for product '{$product?->name}' (rating: {$review->rating})", $review);

        return response()->json([
            'message' => 'Review rejected',
            'review' => $review->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $product = $review->product;
        ActivityLog::reviews($request, 'Delete Review', ActivityLog::userName($request) . " deleted review #{$review->id} for product '{$product?->name}' (rating: {$review->rating})", $review, 'deletion');
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|integer|exists:products,id',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'integer|exists:products,id',
            'reviewer_name' => 'nullable|string|max:255',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'reviews' => 'sometimes|array',
            'reviews.*.reviewer_name' => 'nullable|string|max:255',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
            'reviews.*.comment' => 'nullable|string|max:2000',
            'reviews.*.created_at' => 'nullable|date',
            'created_at' => 'nullable|date',
        ]);

        $productIds = [];
        if (!empty($validated['product_ids'])) {
            $productIds = $validated['product_ids'];
        } elseif (!empty($validated['product_id'])) {
            $productIds = [$validated['product_id']];
        }

        if (empty($productIds)) {
            return response()->json(['message' => 'Product is required.'], 422);
        }

        $reviews = [];
        
        $reviewDataList = [];
        if (!empty($validated['reviews'])) {
            $reviewDataList = $validated['reviews'];
        } else {
            $reviewDataList[] = [
                'reviewer_name' => $validated['reviewer_name'] ?? null,
                'rating' => $validated['rating'] ?? 5,
                'comment' => $validated['comment'] ?? null,
            ];
        }

        foreach ($productIds as $pId) {
            foreach ($reviewDataList as $rData) {
                // Check if specific review has a date, otherwise check root payload, otherwise random
                $reviewDateStr = !empty($rData['created_at']) ? $rData['created_at'] : (!empty($validated['created_at']) ? $validated['created_at'] : null);
                
                $reviewDate = !empty($reviewDateStr) 
                    ? new \DateTime($reviewDateStr) 
                    : fake()->dateTimeBetween('-30 days', 'now');

                $review = new Review([
                    'user_id' => null,
                    'product_id' => $pId,
                    'reviewer_name' => !empty($rData['reviewer_name']) ? $rData['reviewer_name'] : fake()->name(),
                    'rating' => $rData['rating'] ?? 5,
                    'comment' => $rData['comment'] ?? null,
                    'is_approved' => true,
                    'is_rejected' => false,
                ]);

                $review->timestamps = false;
                $review->created_at = $reviewDate;
                $review->updated_at = $reviewDate;
                $review->save();
                
                $reviews[] = $review;
            }
        }

        ActivityLog::reviews($request, 'Create Fake Review', ActivityLog::userName($request) . " created " . count($reviews) . " fake review(s) for " . count($productIds) . " product(s)");

        return response()->json([
            'message' => count($reviews) > 1 ? 'Fake reviews added successfully' : 'Fake review added successfully',
            'reviews' => $reviews,
        ], 201);
    }
}
