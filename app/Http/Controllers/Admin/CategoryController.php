<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OptimizeUploadedImage;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Services\CloudflareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::orderBy('sort_order')
            ->withCount('products')
            ->get();

        ActivityLog::categories($request, 'List Categories', ActivityLog::userName($request) . " viewed categories list");

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
            OptimizeUploadedImage::dispatch($validated['image']);
        }

        $category = Category::create($validated);

        ActivityLog::categories($request, 'Create Category', ActivityLog::userName($request) . " created new category: {$category->name}", $category);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::withCount('products')->findOrFail($id);

        return response()->json([
            'category' => $category,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            // Delete old image and purge from Cloudflare
            if ($category->image) {
                $oldImagePath = $category->image;
                Storage::disk('public')->delete($oldImagePath);
                (new CloudflareService())->purgeStoragePaths([$oldImagePath]);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
            OptimizeUploadedImage::dispatch($validated['image']);
        }

        $category->update($validated);

        ActivityLog::categories($request, 'Update Category', ActivityLog::userName($request) . " updated category: {$category->name}", $category);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $category = Category::withCount('products')->findOrFail($id);

        $productIds = $category->products()->pluck('ids')->toArray();

        // Clean up product images before deleting products
        $productImages = \App\Models\ProductImage::whereIn('product_id', $productIds)->get();
        foreach ($productImages as $image) {
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
                (new CloudflareService())->purgeStoragePaths([$image->image]);
            }
        }

        // Delete related products
        $category->products()->delete();

        // Clean up category image from disk and Cloudflare
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
            (new CloudflareService())->purgeStoragePaths([$category->image]);
        }

        ActivityLog::categories($request, 'Delete Category', ActivityLog::userName($request) . " deleted category: {$category->name} (#{$id}) with {$category->products_count} products", ['type' => 'category', 'id' => $id], 'deletion');
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    public function toggleActive(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $newStatus = !$category->is_active;
        $category->update(['is_active' => $newStatus]);

        if (!$newStatus) {
            $category->products()->update(['is_active' => false]);
        }

        $statusText = $newStatus ? 'Active' : 'Inactive';
        ActivityLog::categories($request, 'Toggle Category Status', ActivityLog::userName($request) . " changed category '{$category->name}' status to {$statusText}", $category);

        return response()->json([
            'message' => 'Category status updated',
            'category' => $category->fresh(),
        ]);
    }
}
