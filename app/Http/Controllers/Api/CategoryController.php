<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('sort_order')
            ->withCount('products')
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::where('id', $id)
            ->orWhere('slug', $id)
            ->active()
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'category' => $category->loadCount('products'),
        ]);
    }
}
