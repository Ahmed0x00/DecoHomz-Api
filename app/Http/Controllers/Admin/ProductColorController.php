<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductColor;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductColorController extends Controller
{
    /**
     * List colors for a product.
     */
    public function index(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $colors = $product->colors()->get();

        return response()->json([
            'product' => $product->only(['id', 'name']),
            'colors' => $colors,
        ]);
    }

    /**
     * Store a new color for a product.
     */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'hex_code' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'price_modifier' => 'nullable|numeric|min:-99999|max:99999',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($validated['name']);

        // Ensure uniqueness within product
        $exists = ProductColor::where('product_id', $productId)->where('color_slug', $slug)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }

        $color = ProductColor::create([
            'product_id' => $productId,
            'name' => $validated['name'],
            'hex_code' => strtoupper($validated['hex_code']),
            'color_slug' => $slug,
            'price_modifier' => $validated['price_modifier'] ?? 0,
            'stock' => $validated['stock'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        ActivityLog::productColors($request, 'Add Product Color', ActivityLog::userName($request) . " added color '{$color->name}' ({$color->hex_code}) to product '{$product->name}'", $color);

        return response()->json($color, 201);
    }

    /**
     * Update a color.
     */
    public function update(Request $request, $productId, $colorId)
    {
        $color = ProductColor::where('product_id', $productId)->findOrFail($colorId);
        $product = $color->product;

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'hex_code' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'price_modifier' => 'nullable|numeric|min:-99999|max:99999',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['name'])) {
            $validated['color_slug'] = Str::slug($validated['name']);
            // Re-check uniqueness
            $exists = ProductColor::where('product_id', $productId)
                ->where('color_slug', $validated['color_slug'])
                ->where('id', '!=', $colorId)
                ->exists();
            if ($exists) {
                $validated['color_slug'] = $validated['color_slug'] . '-' . time();
            }
        }

        if (isset($validated['hex_code'])) {
            $validated['hex_code'] = strtoupper($validated['hex_code']);
        }

        $color->update($validated);

        ActivityLog::productColors($request, 'Update Product Color', ActivityLog::userName($request) . " updated color '{$color->name}' on product '{$product->name}'", $color);

        return response()->json($color);
    }

    /**
     * Delete a color.
     */
    public function destroy(Request $request, $productId, $colorId)
    {
        $color = ProductColor::where('product_id', $productId)->findOrFail($colorId);
        $product = $color->product;
        $colorName = $color->name;

        $color->delete();

        ActivityLog::productColors($request, 'Delete Product Color', ActivityLog::userName($request) . " deleted color '{$colorName}' from product '{$product->name}'", ['type' => 'product_color', 'id' => $colorId], 'deletion');

        return response()->json(['message' => 'Color deleted']);
    }

    /**
     * Toggle color active status.
     */
    public function toggleActive(Request $request, $productId, $colorId)
    {
        $color = ProductColor::where('product_id', $productId)->findOrFail($colorId);
        $color->is_active = !$color->is_active;
        $color->save();

        ActivityLog::productColors($request, 'Toggle Product Color', ActivityLog::userName($request) . " " . ($color->is_active ? 'activated' : 'deactivated') . " color '{$color->name}' on product '{$color->product->name}'", $color);

        return response()->json($color);
    }

    /**
     * Bulk update colors for a product.
     */
    public function bulkUpdate(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'colors' => 'required|array',
            'colors.*.id' => 'required|exists:product_colors,id',
            'colors.*.name' => 'sometimes|required|string|max:100',
            'colors.*.hex_code' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'colors.*.price_modifier' => 'nullable|numeric|min:-99999|max:99999',
            'colors.*.stock' => 'nullable|integer|min:0',
            'colors.*.is_active' => 'boolean',
            'colors.*.sort_order' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['colors'] as $colorData) {
            $update = array_filter([
                'name' => $colorData['name'] ?? null,
                'hex_code' => isset($colorData['hex_code']) ? strtoupper($colorData['hex_code']) : null,
                'price_modifier' => $colorData['price_modifier'] ?? null,
                'stock' => $colorData['stock'] ?? null,
                'is_active' => $colorData['is_active'] ?? null,
                'sort_order' => $colorData['sort_order'] ?? null,
            ], fn($v) => $v !== null);

            if (!empty($update)) {
                ProductColor::where('id', $colorData['id'])->where('product_id', $productId)->update($update);
            }
        }

        ActivityLog::productColors($request, 'Bulk Update Colors', ActivityLog::userName($request) . " bulk updated " . count($validated['colors']) . " colors for product '{$product->name}'", $product);

        return response()->json(['message' => 'Colors updated']);
    }
}
