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
        $query = Product::active()->with(['category', 'primaryImage', 'approvedReviews', 'colors']);

        // Filter by category (supports ID or slug)
        if ($request->has('category')) {
            $cat = $request->input('category');
            $query->whereHas('category', function ($q) use ($cat) {
                if (is_numeric($cat)) {
                    $q->where('id', $cat);
                } else {
                    $q->where('slug', $cat)->orWhere('name', $cat);
                }
            });
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

        // Filter by color (product color variants or legacy JSON colors field)
        if ($request->filled('color')) {
            $color = $request->input('color');
            $query->where(function ($q) use ($color) {
                $q->whereHas('colors', function ($cq) use ($color) {
                    $cq->where('is_active', true)
                        ->where('name', 'like', "%{$color}%");
                })->orWhere('colors', 'like', '%"' . $color . '"%');
            });
        }

        // Filter by stock availability
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
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

        // Sorting (supports legacy and current frontend values)
        $sort = $request->input('sort', 'newest');
        $sortMap = [
            'price_asc' => 'price-low',
            'price_desc' => 'price-high',
            'price-low' => 'price-low',
            'price-high' => 'price-high',
            'newest' => 'newest',
            'name' => 'name',
            'featured' => 'featured',
        ];
        $sort = $sortMap[$sort] ?? 'newest';

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
            case 'featured':
                $query->orderByDesc('is_featured')->latest();
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
            ->with(['category', 'primaryImage', 'images', 'colors', 'approvedReviews'])
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
        $product = Product::where('id', $id)->orWhere('slug', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $related = Product::active()
            ->where('id', '!=', $id)
            ->where('category_id', $product->category_id)
            ->with(['category', 'primaryImage', 'colors'])
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
            ->with(['category', 'primaryImage', 'colors'])
            ->limit(8)
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
