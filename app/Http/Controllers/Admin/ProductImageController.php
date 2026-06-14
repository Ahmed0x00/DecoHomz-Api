<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OptimizeUploadedImage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, string $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $file = $request->file('image');
        $path = $file->store('products', 'public');

        // If this is set as primary, unset other primary images
        if ($request->boolean('is_primary')) {
            $product->images()->update(['is_primary' => false]);
        }

        // If this is the first image, make it primary by default
        $isPrimary = $request->boolean('is_primary') || ($product->images()->count() === 0);

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image' => $path,
            'alt_text' => $request->input('alt_text', $product->name),
            'sort_order' => $product->images()->max('sort_order') + 1,
            'is_primary' => $isPrimary,
        ]);

        // Dispatch async optimization job
        OptimizeUploadedImage::dispatch($path);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image' => $image,
        ], 201);
    }

    public function update(Request $request, string $productId, string $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);

        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $image->product->images()->update(['is_primary' => false]);
        }

        $image->update($validated);

        return response()->json([
            'message' => 'Image updated successfully',
            'image' => $image,
        ]);
    }

    public function destroy(string $productId, string $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);

        // Delete the file
        if (Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // If deleted image was primary, make another one primary
        if ($wasPrimary) {
            $firstRemaining = ProductImage::where('product_id', $productId)->first();
            if ($firstRemaining) {
                $firstRemaining->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'message' => 'Image deleted successfully',
        ]);
    }

    public function setPrimary(string $productId, string $imageId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json([
            'message' => 'Primary image set successfully',
            'image' => $image,
        ]);
    }
}
