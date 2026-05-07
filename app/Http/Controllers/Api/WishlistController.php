<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wishlists = Wishlist::with(['product.primaryImage'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $products = $wishlists->map(function ($wishlist) {
            return $wishlist->product;
        });

        return response()->json(['products' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $userId = $request->user()->id;

        $exists = Wishlist::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Product already in wishlist'], 409);
        }

        $wishlist = Wishlist::create([
            'user_id' => $userId,
            'product_id' => $validated['product_id'],
        ]);

        return response()->json([
            'message' => 'Product added to wishlist',
            'wishlist' => $wishlist,
        ], 201);
    }

    public function destroy(Request $request, string $productId): JsonResponse
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Wishlist item not found'], 404);
        }

        return response()->json(['message' => 'Product removed from wishlist']);
    }

    public function check(Request $request, string $productId): JsonResponse
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json(['in_wishlist' => $exists]);
    }
}
