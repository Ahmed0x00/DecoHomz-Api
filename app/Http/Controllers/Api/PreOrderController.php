<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use App\Models\PreOrderImage;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class PreOrderController extends Controller
{
    private function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken() ?? $request->cookie('dh_token');
        if (!$token) return null;

        if ($request->bearerToken()) {
            return auth('sanctum')->user();
        }

        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken ? $accessToken->tokenable : null;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['pre_orders' => []]);
        }

        $email = $user->email ?? null;
        $phone = $user->phone ?? null;

        $preOrders = PreOrder::with('images')
            ->where(function ($q) use ($user, $email, $phone) {
                $q->where('user_id', $user->id);
                if ($email) {
                    $q->orWhere('email', $email);
                }
                if ($phone) {
                    $q->orWhere('phone', $phone);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['pre_orders' => $preOrders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:30',
            'notes' => 'nullable|string|max:5000',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $user = $this->getUserFromToken($request);

        $preOrder = PreOrder::create([
            'user_id' => $user?->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('pre-orders', 'public');
                PreOrderImage::create([
                    'pre_order_id' => $preOrder->id,
                    'image' => $path,
                ]);
            }
        }

        ActivityLog::contacts($request, 'Pre-Order Submitted', "New pre-order submitted by {$validated['name']} ({$validated['phone']})", $preOrder);

        return response()->json([
            'message' => 'Your pre-order request has been submitted successfully. We will contact you soon.',
            'pre_order' => $preOrder->load('images'),
        ], 201);
    }

    public function customerDetail(Request $request, string $id)
    {
        $preOrder = PreOrder::with('images')->find($id);

        if (!$preOrder) {
            abort(404, 'Pre-order not found');
        }

        $user = $this->getUserFromToken($request);
        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');

        $isOwner = $user && $preOrder->user_id && (int) $preOrder->user_id === (int) $user->id;
        $isEmailMatch = $user && $preOrder->email && strtolower($preOrder->email) === strtolower($user->email ?? '');
        $isPhoneMatch = $user && $preOrder->phone && $preOrder->phone === ($user->phone ?? '');

        if (!$isOwner && !$isEmailMatch && !$isPhoneMatch) {
            abort(403, 'Access denied');
        }

        return view('account.pre-orders.show', compact('preOrder'));
    }
}
