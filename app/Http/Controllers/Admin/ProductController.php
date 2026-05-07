<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'primaryImage']);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by featured
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Filter by stock status
        if ($request->has('low_stock')) {
            $query->where('stock', '<=', 5);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 15);
        $products = $query->latest()->paginate($perPage);

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:100',
            'upholstery' => 'nullable|string|max:100',
            'dimensions' => 'nullable|string|max:50',
            'weight' => 'nullable|string|max:30',
            'colors' => 'nullable|array',
            'colors.*' => 'string',
            'stars' => 'nullable|integer|min:1|max:5',
            'badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:20',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['stars'] = $validated['stars'] ?? 5;
        $validated['stock'] = $validated['stock'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_featured'] = $validated['is_featured'] ?? false;

        $product = Product::create($validated);

        // Handle colors array
        if (isset($validated['colors'])) {
            $product->update(['colors' => $validated['colors']]);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load(['category', 'images']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with(['category', 'images', 'reviews.user'])->findOrFail($id);

        return response()->json([
            'product' => $product,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:100',
            'upholstery' => 'nullable|string|max:100',
            'dimensions' => 'nullable|string|max:50',
            'weight' => 'nullable|string|max:30',
            'colors' => 'nullable|array',
            'colors.*' => 'string',
            'stars' => 'nullable|integer|min:1|max:5',
            'badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:20',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh()->load(['category', 'images']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Check for related orders
        if ($product->orderItems()->exists()) {
            // Soft delete approach - just mark as inactive
            $product->update(['is_active' => false]);
            return response()->json([
                'message' => 'Product has existing orders. Marked as inactive instead of deletion.',
            ]);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    public function toggleActive(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'message' => 'Product status updated',
            'product' => $product->fresh(),
        ]);
    }

    public function toggleFeatured(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);

        return response()->json([
            'message' => 'Product featured status updated',
            'product' => $product->fresh(),
        ]);
    }
}
