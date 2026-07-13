<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->isVendor()) {
            return response()->json(['message' => 'Access denied. Vendor privileges required.'], 403);
        }

        $vendor = $user->vendor;
        if (!$vendor || !$vendor->isActive()) {
            return response()->json(['message' => 'Access denied. Your vendor account is not active.'], 403);
        }

        return $next($request);
    }
}
