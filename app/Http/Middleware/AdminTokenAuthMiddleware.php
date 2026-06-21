<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenAuthMiddleware
{
    /**
     * Authenticate admin users via:
     * 1. GET /admin/* Blade views  — skip server auth, rely on JS guard in the template
     * 2. API /api/admin/*         — require Bearer token
     * 3. POST/PUT/DELETE /admin/* — require session cookie OR Bearer token
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. GET requests to Blade view routes — let the JS auth guard handle it
        if ($request->is('admin/dashboard') ||
            $request->is('admin/products*') ||
            $request->is('admin/categories*') ||
            $request->is('admin/users*') ||
            $request->is('admin/reviews*') ||
            $request->is('admin/coupons*') ||
            $request->is('admin/delivery-fees*') ||
            $request->is('admin/deposit-rules*') ||
            $request->is('admin/logs*') ||
            $request->is('admin/settings*') ||
            $request->is('admin/orders*') ||
            $request->is('admin/refunds*') ||
            $request->is('admin/contacts*') ||
            $request->is('admin/pre-orders*')
        ) {
            // For GET requests, just pass through — JS guard in Blade template will handle auth
            if ($request->isMethod('GET')) {
                return $next($request);
            }
            // For non-GET (form submissions), require Bearer token
            return $this->requireBearerOrSession($request, $next);
        }

        // 2. API routes — require Bearer token
        if ($request->is('api/admin/*')) {
            return $this->requireBearerOrSession($request, $next);
        }

        // 3. Fallback — require some auth
        return $this->requireBearerOrSession($request, $next);
    }

    private function requireBearerOrSession(Request $request, Closure $next): Response
    {
        // Try session cookie first
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user && ($user->isAdmin() || $user->isSupport())) {
                $request->setUserResolver(fn() => $user);
                return $next($request);
            }
        }

        // Try Bearer token
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
                if ($user && ($user->isAdmin() || $user->isSupport())) {
                    $request->setUserResolver(fn() => $user);
                    return $next($request);
                }
            }
        }

        return $this->unauthorized($request);
    }

    private function unauthorized(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        return redirect()->route('login');
    }
}
