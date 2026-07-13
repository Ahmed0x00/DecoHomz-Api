<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Jobs\OptimizeUploadedImage;

class VendorProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        
        $query = $vendor->products()->with([
            'specification',
            'images',
            'latestReviewHistory' => function ($query) {
                $query->with('admin:id,name');
            },
        ]);

        if ($request->has('status')) {
            $query->where('vendor_status', $request->status);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function show(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->with([
            'specification',
            'images',
            'category',
            'latestReviewHistory' => function ($query) {
                $query->with('admin:id,name');
            },
        ])->findOrFail($id);
        
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'vendor_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'colors_json' => 'nullable|string',
            'specifications_json' => 'nullable|string',
            
            // Specification fields
            'materials' => 'nullable|string',
            'dimensions_length' => 'nullable|numeric|min:0',
            'dimensions_width' => 'nullable|numeric|min:0',
            'dimensions_height' => 'nullable|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'available_colors' => 'nullable|array',
            'finishes' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'production_time_days' => 'nullable|integer|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'care_instructions' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            
            // Allow immediate submit
            'submit' => 'boolean',
        ]);

        $product = $vendor->products()->create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . uniqid(),
            'description' => $validated['description'],
            'vendor_price' => $validated['vendor_price'],
            'price' => $validated['vendor_price'], // Initial retail price matches vendor price
            'vendor_status' => empty($validated['submit']) ? 'draft' : 'submitted',
            'is_active' => false,
            'stock' => $validated['stock'] ?? 0,
        ]);

        if ($request->filled('specifications_json')) {
            $product->update(['specifications' => json_decode($request->input('specifications_json'), true)]);
        }

        if (!empty($validated['colors_json'])) {
            $colors = json_decode($validated['colors_json'], true);
            if (is_array($colors)) {
                foreach ($colors as $index => $colorData) {
                    if (!empty($colorData['name']) && !empty($colorData['hex_code'])) {
                        $product->colors()->create([
                            'name' => $colorData['name'],
                            'hex_code' => $colorData['hex_code'],
                            'color_slug' => Str::slug($colorData['name']),
                            'price_modifier' => $colorData['price_modifier'] ?? 0,
                            'stock' => $colorData['stock'] ?? 0,
                            'is_active' => true,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        }

        $product->specification()->create([
            'materials' => $validated['materials'] ?? null,
            'dimensions_length' => $validated['dimensions_length'] ?? null,
            'dimensions_width' => $validated['dimensions_width'] ?? null,
            'dimensions_height' => $validated['dimensions_height'] ?? null,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'available_colors' => $validated['available_colors'] ?? null,
            'finishes' => $validated['finishes'] ?? null,
            'packaging_details' => $validated['packaging_details'] ?? null,
            'production_time_days' => $validated['production_time_days'] ?? null,
            'warranty_months' => $validated['warranty_months'] ?? null,
            'care_instructions' => $validated['care_instructions'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
        ]);

        return response()->json(['message' => 'Product created successfully.', 'product' => $product->load('specification')], 201);
    }

    public function update(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->findOrFail($id);

        if (!in_array($product->vendor_status, ['draft', 'rejected', 'changes_requested'])) {
            // Check if editing is allowed (critical fields edit on published products)
            if ($product->vendor_status === 'published') {
                app(\App\Services\VendorProductService::class)->handleCriticalEdit($product);
            } else {
                return response()->json(['message' => 'Cannot edit a product that is currently under review.'], 403);
            }
        }

        $validated = $request->validate([
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'description' => 'string',
            'vendor_price' => 'numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'colors_json' => 'nullable|string',
            'specifications_json' => 'nullable|string',
            
            // Specification fields
            'materials' => 'nullable|string',
            'dimensions_length' => 'nullable|numeric|min:0',
            'dimensions_width' => 'nullable|numeric|min:0',
            'dimensions_height' => 'nullable|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'available_colors' => 'nullable|array',
            'finishes' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'production_time_days' => 'nullable|integer|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'care_instructions' => 'nullable|string',
            'additional_notes' => 'nullable|string',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        }

        if (isset($validated['stock'])) {
            $product->stock = $validated['stock'];
        }

        if ($request->filled('specifications_json')) {
            $validated['specifications'] = json_decode($request->input('specifications_json'), true);
        }

        $product->update($validated);

        if ($request->has('colors_json')) {
            // Delete old colors
            $product->colors()->delete();
            $colors = json_decode($validated['colors_json'], true);
            if (is_array($colors)) {
                foreach ($colors as $index => $colorData) {
                    if (!empty($colorData['name']) && !empty($colorData['hex_code'])) {
                        $product->colors()->create([
                            'name' => $colorData['name'],
                            'hex_code' => $colorData['hex_code'],
                            'color_slug' => Str::slug($colorData['name']),
                            'price_modifier' => $colorData['price_modifier'] ?? 0,
                            'stock' => $colorData['stock'] ?? 0,
                            'is_active' => true,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        }

        $specData = collect($validated)->except(['category_id', 'name', 'description', 'vendor_price', 'stock', 'colors_json', 'specifications_json'])->toArray();
        if (!empty($specData)) {
            $product->specification()->updateOrCreate(['product_id' => $product->id], $specData);
        }

        return response()->json(['message' => 'Product updated successfully.', 'product' => $product->fresh('specification')]);
    }

    public function submit($id, Request $request)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->findOrFail($id);

        $submitted = app(\App\Services\VendorProductService::class)->submitForReview($product);

        if (!$submitted) {
            return response()->json([
                'message' => 'This product cannot be submitted from its current status.'
            ], 422);
        }

        return response()->json([
            'message' => 'Product submitted for review.',
            'product' => $product->fresh(['specification', 'images', 'latestReviewHistory'])
        ]);
    }

    public function uploadImage(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'product_color_id' => 'nullable|exists:product_colors,id',
            'color_name' => 'nullable|string|max:255',
            'is_primary' => 'boolean'
        ]);

        $file = $request->file('image');
        $filename = uniqid() . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('products', $filename, 'public');

        if ($request->is_primary) {
            $product->images()->update(['is_primary' => false]);
        }

        $colorId = $request->product_color_id;
        if (!$colorId && $request->filled('color_name')) {
            $colorModel = $product->colors()->where('name', $request->color_name)->first();
            if ($colorModel) {
                $colorId = $colorModel->id;
            }
        }

        $image = $product->images()->create([
            'image' => $path,
            'is_primary' => $request->is_primary ?? false,
            'product_color_id' => $colorId,
            'sort_order' => (int)$product->images()->max('sort_order') + 1,
        ]);

        // Dispatch background job for optimization
        OptimizeUploadedImage::dispatch($image);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'image' => $image
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->findOrFail($id);
        
        // Cannot delete approved products, they can only be deactivated by admin
        if ($product->status === 'approved' || $product->status === 'active') {
            return response()->json(['message' => 'Cannot delete an active/approved product. Contact admin to deactivate.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function deleteImage(Request $request, $productId, $imageId)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        // Delete from storage
        $filePath = $image->image;
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        $image->delete();

        return response()->json(['message' => 'Image deleted successfully.']);
    }

    public function setPrimaryImage(Request $request, $productId, $imageId)
    {
        $vendor = $request->user()->vendor;
        $product = $vendor->products()->findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        // Reset all images to not primary
        $product->images()->update(['is_primary' => false]);
        
        // Set this one to primary
        $image->update(['is_primary' => true]);

        return response()->json(['message' => 'Primary image updated successfully.']);
    }
}
