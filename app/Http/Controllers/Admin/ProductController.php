<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
            'color' => 'nullable|string|max:255',  // legacy single-color field
            'colors_json' => 'nullable|string',     // new structured colors (JSON array)
            'stars' => 'nullable|integer|min:1|max:5',
            'badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:20',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'primary_image_index' => 'nullable|integer|min:0',
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

        // Handle structured colors
        if (!empty($validated['colors_json'])) {
            $colors = json_decode($validated['colors_json'], true);
            if (is_array($colors)) {
                foreach ($colors as $index => $colorData) {
                    if (!empty($colorData['name']) && !empty($colorData['hex_code'])) {
                        $product->colors()->create([
                            'name' => $colorData['name'],
                            'hex_code' => $colorData['hex_code'],
                            'color_slug' => \Illuminate\Support\Str::slug($colorData['name']),
                            'price_modifier' => $colorData['price_modifier'] ?? 0,
                            'stock' => $colorData['stock'] ?? 0,
                            'is_active' => true,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        }

        ActivityLog::products($request, 'Create Product', "Created new product: {$product->name} (Price: {$product->price} EGP)", $product);

        // Handle images
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $primaryIndex = $request->input('primary_image_index', 0);

            foreach ($images as $index => $file) {
                $path = $file->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'is_primary' => $index == $primaryIndex,
                    'sort_order' => $index,
                    'alt_text' => $product->name
                ]);
            }
        }

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
            'data' => $product->load(['category', 'images', 'reviews.user']),
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'primary_image_id' => 'nullable|integer',
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

        ActivityLog::products($request, 'Update Product', "Updated product: {$product->name}", $product);

        // Handle image removals
        if ($request->has('remove_images')) {
            $toRemove = ProductImage::whereIn('id', $request->remove_images)
                ->where('product_id', $product->id)
                ->get();
            foreach ($toRemove as $img) {
                Storage::disk('public')->delete($img->image);
                $img->delete();
            }
        }

        // Handle new images
        if ($request->hasFile('images')) {
            $existingCount = $product->images()->count();
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'is_primary' => false,
                    'sort_order' => $existingCount + $index,
                    'alt_text' => $product->name
                ]);
            }
        }

        // Handle primary image update
        if ($request->has('primary_image_id')) {
            $product->images()->update(['is_primary' => false]);
            ProductImage::where('id', $request->primary_image_id)
                ->where('product_id', $product->id)
                ->update(['is_primary' => true]);
        } elseif ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
            $product->images()->first()->update(['is_primary' => true]);
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh()->load(['category', 'images']),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Check for related orders
        if ($product->orderItems()->exists()) {
            // Soft delete approach - just mark as inactive
            $product->update(['is_active' => false]);
            ActivityLog::products($request, 'Deactivate Product', "Product #{$id} ({$product->name}) has orders; marked as inactive instead of deleting.", $product, 'warning');
            return response()->json([
                'message' => 'Product has existing orders. Marked as inactive instead of deletion.',
            ]);
        }

        ActivityLog::products($request, 'Delete Product', "Permanently deleted product: {$product->name} (#{$id})", ['type' => 'product', 'id' => $id], 'deletion');
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    public function toggleActive(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'Active' : 'Inactive';
        ActivityLog::products($request, 'Toggle Product Status', "Changed product '{$product->name}' status to {$statusText}", $product);

        return response()->json([
            'message' => 'Product status updated',
            'product' => $product->fresh(),
        ]);
    }

    public function toggleFeatured(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $newFeatured = !$product->is_featured;
        $product->update(['is_featured' => $newFeatured]);

        $featText = $newFeatured ? 'Featured' : 'Regular';
        ActivityLog::products($request, 'Toggle Product Featured', "Changed product '{$product->name}' to {$featText}", $product);

        return response()->json([
            'message' => 'Product featured status updated',
            'product' => $product->fresh(),
        ]);
    }
}
