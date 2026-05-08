<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    // Map URL segments to section names
    private const SECTION_MAP = [
        'auth' => 'Auth',
        'users' => 'Users',
        'products' => 'Products',
        'categories' => 'Categories',
        'orders' => 'Orders',
        'reviews' => 'Reviews',
        'coupons' => 'Coupons',
        'cart' => 'Cart',
        'addresses' => 'Addresses',
        'wishlist' => 'Wishlist',
        'contacts' => 'Contacts',
        'settings' => 'Settings',
        'logs' => 'General',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $oldData = null;
        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $oldData = $this->getOldData($request);
        }

        $response = $next($request);

        // Only auto-log if controller did NOT log directly (no explicit log method call)
        if ($request->attributes->get('log_from_controller')) {
            return $response;
        }

        if ($this->shouldLog($request)) {
            $this->autoLog($request, $response, $oldData);
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        // Don't log requests to the logs endpoint itself
        if (str_contains($request->path(), '/logs')) return false;

        // Log all non-GET admin modifications
        if (str_contains($request->path(), 'admin') && $request->method() !== 'GET') return true;

        // Log specific read operations
        if ($request->method() === 'GET') {
            $path = $request->path();
            if (preg_match('/api\/products\/\d+$/', $path)) return true;
            if (preg_match('/api\/categories\/\d+$/', $path)) return true;
            if (str_contains($path, 'cart') && !str_contains($path, 'admin')) return true;
        }

        return false;
    }

    protected function autoLog(Request $request, Response $response, $oldData): void
    {
        $user = Auth::user();
        $method = $request->method();
        $path = $request->path();
        $status = $response->getStatusCode();

        $section = $this->inferSection($path);

        if ($status >= 400) {
            $result = 'failure';
        } elseif ($method === 'DELETE') {
            $result = 'deletion';
        } elseif ($method === 'GET') {
            $result = 'read';
        } else {
            $result = $status >= 200 && $status < 300 ? 'success' : 'failure';
        }

        $resourceInfo = $this->deriveResource($path);
        $resourceType = $resourceInfo['type'];
        $resourceId = $resourceInfo['id'];

        $action = $this->deriveActionName($method, $path, $resourceType);
        $description = $this->buildDescription($user, $method, $resourceType, $resourceId, $status);

        $payload = null;
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $payload = $this->filterPayload($request->all());
        }

        ActivityLog::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'section' => $section,
            'result' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'payload' => $payload,
            'response_data' => $this->decodeResponse($response),
            'old_values' => $oldData,
        ]);
    }

    protected function inferSection(string $path): string
    {
        foreach (self::SECTION_MAP as $segment => $section) {
            if (str_contains($path, $segment)) {
                return $section;
            }
        }
        return 'General';
    }

    protected function getOldData(Request $request): ?array
    {
        $info = $this->deriveResource($request->path());
        if (!$info['id'] || !$info['type']) return null;

        try {
            $tableMap = [
                'users' => 'users',
                'products' => 'products',
                'categories' => 'categories',
                'orders' => 'orders',
                'coupons' => 'coupons',
                'reviews' => 'reviews',
            ];
            $table = $tableMap[$info['type']] ?? $info['type'];
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                return (array) DB::table($table)->where('id', $info['id'])->first();
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    protected function deriveResource(string $path): array
    {
        $parts = explode('/', $path);
        $offset = $parts[0] === 'api' ? 1 : 0;

        if (isset($parts[$offset]) && $parts[$offset] === 'admin') {
            $type = $parts[$offset + 1] ?? null;
            $id = $parts[$offset + 2] ?? null;
        } else {
            $type = $parts[$offset] ?? null;
            $id = $parts[$offset + 1] ?? null;
        }

        if ($id && !is_numeric($id)) $id = null;

        return ['type' => $type, 'id' => $id];
    }

    protected function deriveActionName(string $method, string $path, ?string $type): string
    {
        if ($method === 'GET') {
            return 'View ' . ($type ? ucfirst($type) : 'Page');
        }
        $map = ['POST' => 'Create', 'PUT' => 'Update', 'PATCH' => 'Update', 'DELETE' => 'Delete'];
        return ($map[$method] ?? $method) . ' ' . ($type ? ucfirst($type) : 'Resource');
    }

    protected function buildDescription($user, string $method, ?string $type, ?string $resId, int $status): string
    {
        $userName = $user ? $user->name : 'Guest';
        $resourceLabel = $type ? str_replace(
            ['users', 'products', 'categories', 'orders', 'reviews', 'coupons'],
            ['user', 'product', 'category', 'order', 'review', 'coupon'],
            $type
        ) : 'resource';
        $id = $resId ? " #{$resId}" : '';

        if ($method === 'GET') {
            return "{$userName} viewed {$resourceLabel}{$id}";
        }

        $verb = ['POST' => 'created', 'PUT' => 'updated', 'PATCH' => 'updated', 'DELETE' => 'deleted'][$method] ?? 'modified';
        $desc = "{$userName} {$verb} {$resourceLabel}{$id}";
        if ($status >= 400) $desc .= " (Failed: {$status})";

        return $desc;
    }

    protected function decodeResponse(Response $response): array
    {
        $content = $response->getContent();
        return json_decode($content, true) ?: ['raw' => substr($content, 0, 200)];
    }

    protected function filterPayload(array $payload): array
    {
        $sensitive = ['password', 'password_confirmation', 'token', 'card_number', 'api_token', 'cvv', 'current_password'];
        foreach ($payload as $key => $value) {
            if (in_array($key, $sensitive)) {
                $payload[$key] = '********';
            } elseif (is_array($value)) {
                $payload[$key] = $this->filterPayload($value);
            }
        }
        return $payload;
    }
}
