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
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip server-side auth for GET Blade view requests — JS guard handles it
        if ($request->isMethod('GET') && !$request->is('api/*')) {
            return $next($request);
        }

        // Enforce auth for API routes and non-GET requests
        if (!$request->user() || (!$request->user()->isAdmin() && !$request->user()->isSupport())) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }
            abort(403);
        }

        return $next($request);
    }
}
