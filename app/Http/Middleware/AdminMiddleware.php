<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle admin access control.
     * GET requests to Blade views — rely on JS auth guard in the template.
     * API routes and form submissions — enforce server-side auth.
     *
     * This middleware enforces that only admin users can access protected routes.
     * Support users are allowed through ONLY when the 'support.access' middleware
     * is also applied to the route group — that middleware handles the path-based
     * filtering for support users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip server-side auth for GET Blade view requests — JS guard handles it
        if ($request->isMethod('GET') && !$request->is('api/*')) {
            return $next($request);
        }

        $user = $request->user();

        // No user at all — reject
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }
            abort(403);
        }

        // Admin users pass through unconditionally
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Support users pass through — SupportAccessMiddleware will restrict their paths
        if ($user->isSupport()) {
            return $next($request);
        }

        // Regular users — reject
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }
        abort(403);
    }
}
