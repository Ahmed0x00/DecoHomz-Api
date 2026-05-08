<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ActivityLog;
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

        ActivityLog::categories($request, 'List Categories', "Admin viewed categories list");

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);

        ActivityLog::categories($request, 'Create Category', "Admin created new category: {$category->name}", $category);

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        ActivityLog::categories($request, 'Update Category', "Admin updated category: {$category->name}", $category);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            ActivityLog::categories($request, 'Delete Category Denied', "Admin tried to delete category '{$category->name}' which has {$category->products_count} products", $category, 'warning');
            return response()->json([
                'message' => 'Cannot delete category with associated products. Please remove or reassign products first.',
            ], 422);
        }

        ActivityLog::categories($request, 'Delete Category', "Admin deleted category: {$category->name} (#{$id})", ['type' => 'category', 'id' => $id], 'deletion');
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

        $statusText = $newStatus ? 'Active' : 'Inactive';
        ActivityLog::categories($request, 'Toggle Category Status', "Changed category '{$category->name}' status to {$statusText}", $category);

        return response()->json([
            'message' => 'Category status updated',
            'category' => $category->fresh(),
        ]);
    }
}
