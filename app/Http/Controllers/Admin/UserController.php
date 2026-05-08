<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('search')) {
            ActivityLog::users($request, 'Search Users', "Admin searched for users with query: {$request->search}");
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('verified')) {
            $verified = $request->boolean('verified');
            if ($verified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($users);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with(['addresses', 'orders'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:user,admin,support',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? 'user',
        ]);

        ActivityLog::users($request, 'Create User', "Admin created new user: {$user->name} ({$user->email})", $user);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|required|in:user,admin',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        ActivityLog::users($request, 'Update User', "Admin updated user: {$user->name} (#{$id})", $user);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->role === 'admin') {
            ActivityLog::users($request, 'Delete User Denied', "Admin tried to delete another admin: {$user->name}", $user, 'warning');
            return response()->json(['message' => 'Cannot delete an admin user'], 403);
        }

        ActivityLog::users($request, 'Delete User', "Admin deleted user: {$user->name} (#{$id})", ['type' => 'user', 'id' => $id], 'deletion');
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
