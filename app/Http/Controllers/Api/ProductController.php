<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()->with(['category', 'primaryImage', 'approvedReviews']);

        // Filter by category
        if ($request->has('category')) {
            $categoryId = $request->input('category');
            $query->where('category_id', $categoryId);
        }

        // Filter by search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by material
        if ($request->has('material')) {
            $query->where('material', $request->input('material'));
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $perPage = $request->input('per_page', 12);
        $products = $query->paginate($perPage);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->orWhere('slug', $id)
            ->active()
            ->with(['category', 'images', 'approvedReviews'])
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Calculate average rating
        $avgRating = $product->approvedReviews()->avg('rating');
        $reviewCount = $product->approvedReviews()->count();

        return response()->json([
            'product' => $product,
            'rating' => [
                'average' => round($avgRating, 1) ?: null,
                'count' => $reviewCount,
            ],
        ]);
    }

    public function related(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage'])
            ->limit(4)
            ->get();

        return response()->json([
            'products' => $related,
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::active()
            ->featured()
            ->with(['category', 'primaryImage'])
            ->limit(8)
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
