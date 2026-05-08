<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportAccessMiddleware
{
    /**
     * Only allow support role users to access orders, refunds, and contacts.
     * Admins pass through unchanged.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for GET Blade view requests — JS auth guard handles these
        if ($request->isMethod('GET') && !$request->is('api/*')) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Admins bypass this restriction entirely
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Support users can only access orders, refunds, contacts
        if ($user->isSupport()) {
            $path = $request->path(); // e.g. "admin/orders", "api/admin/refunds"
            $allowed = [
                'admin/orders',
                'admin/orders/',
                'admin/refunds',
                'admin/refunds/',
                'admin/contacts',
                'admin/contacts/',
            ];

            $allowedApi = [
                'api/admin/orders',
                'api/admin/orders/',
                'api/admin/refunds',
                'api/admin/refunds/',
                'api/admin/refunds/search-eligible',
                'api/admin/refunds/create-for-guest',
            ];

            $isAllowed = false;
            foreach ($allowed as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $isAllowed = true;
                    break;
                }
            }
            foreach ($allowedApi as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Access denied. Support role cannot access this resource.'], 403);
                }
                abort(403);
            }

            return $next($request);
        }

        abort(403);
    }
}
