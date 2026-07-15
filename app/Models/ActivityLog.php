<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ActivityLog
{
    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        // This won't work perfectly without Model, but we can leave it for now
        // ActivityLog relationships are mostly handled by Activity now
        return $this->belongsTo(User::class);
    }

    public static function create(array $attributes)
    {
        $properties = [
            'legacy_action' => $attributes['action'] ?? 'Action',
            'legacy_result' => $attributes['result'] ?? 'info',
            'ip' => $attributes['ip_address'] ?? null,
            'user_agent' => $attributes['user_agent'] ?? null,
            'attributes' => $attributes['payload'] ?? null, // Map payload to attributes so UI shows it
            'old' => $attributes['old_values'] ?? null,
            'response_data' => $attributes['response_data'] ?? null,
            'user_identifier' => $attributes['user_identifier'] ?? null,
            'metadata' => $attributes['metadata'] ?? null,
        ];

        $subjectType = null;
        if (!empty($attributes['resource_type'])) {
            $type = ucfirst(strtolower(class_basename($attributes['resource_type'])));
            $fullClass = "App\\Models\\" . $type;
            if (class_exists($fullClass)) {
                $subjectType = $fullClass;
            } else {
                $subjectType = $attributes['resource_type'];
            }
        }

        $event = 'info';
        $actionLower = strtolower($attributes['action'] ?? '');
        if (str_contains($actionLower, 'creat')) $event = 'created';
        elseif (str_contains($actionLower, 'updat')) $event = 'updated';
        elseif (str_contains($actionLower, 'delet')) $event = 'deleted';
        elseif (str_contains($actionLower, 'login')) $event = 'login';
        elseif (str_contains($actionLower, 'logout')) $event = 'logout';

        $logger = activity($attributes['section'] ?? 'General')
            ->event($event)
            ->withProperties($properties);

        if (!empty($attributes['user_id'])) {
            $logger->causedBy(\App\Models\User::find($attributes['user_id']));
        }

        if ($subjectType && !empty($attributes['resource_id'])) {
            // Force subject_type and subject_id manually since model might not exist
            $activityModel = \Spatie\Activitylog\Models\Activity::create([
                'log_name' => $attributes['section'] ?? 'General',
                'description' => $attributes['description'] ?? '',
                'subject_type' => $subjectType,
                'subject_id' => $attributes['resource_id'],
                'event' => $event,
                'causer_type' => !empty($attributes['user_id']) ? 'App\\Models\\User' : null,
                'causer_id' => $attributes['user_id'] ?? null,
                'properties' => $properties,
            ]);
            return $activityModel;
        } else {
            return $logger->log($attributes['description'] ?? '');
        }
    }



    // ─────────────────────────────────────────────────────────
    // Section-based logging methods — LOG DIRECTLY, no middleware
    // ─────────────────────────────────────────────────────────

    public static function auth(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Auth', $result, $resource);
    }

    public static function orders(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Orders', $result, $resource);
    }

    public static function users(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Users', $result, $resource);
    }

    public static function products(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Products', $result, $resource);
    }

    public static function categories(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Categories', $result, $resource);
    }

    public static function reviews(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Reviews', $result, $resource);
    }

    public static function coupons(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Coupons', $result, $resource);
    }

    public static function cart(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Cart', $result, $resource);
    }

    public static function addresses(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Addresses', $result, $resource);
    }

    public static function wishlist(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Wishlist', $result, $resource);
    }

    public static function contacts(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Contacts', $result, $resource);
    }

    public static function settings(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Settings', $result, $resource);
    }

    public static function general(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'General', $result, $resource);
    }

    public static function deliveryFees(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'DeliveryFees', $result, $resource);
    }

    public static function productColors(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'ProductColors', $result, $resource);
    }

    public static function vendors(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Vendors', $result, $resource);
    }

    public static function warehouse(Request $request, string $action, string $description, $resource = null, string $result = 'success'): \Spatie\Activitylog\Models\Activity
    {
        return self::log($request, $action, $description, 'Warehouse', $result, $resource);
    }

    // ─────────────────────────────────────────────────────────
    // Backward-compat severity aliases — log with General section
    // ─────────────────────────────────────────────────────────

    public static function success(Request $request, string $action, string $description, $resource = null): \Spatie\Activitylog\Models\Activity
    {
        return self::general($request, $action, $description, $resource, 'success');
    }

    public static function warning(Request $request, string $action, string $description, $resource = null): \Spatie\Activitylog\Models\Activity
    {
        return self::general($request, $action, $description, $resource, 'warning');
    }

    public static function critical(Request $request, string $action, string $description, $resource = null): \Spatie\Activitylog\Models\Activity
    {
        return self::general($request, $action, $description, $resource, 'failure');
    }

    public static function info(Request $request, string $action, string $description, $resource = null): \Spatie\Activitylog\Models\Activity
    {
        return self::general($request, $action, $description, $resource, 'read');
    }

    // ─────────────────────────────────────────────────────────
    // User identification helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Return a readable user label for log descriptions.
     * Examples: "Ahmed (ahmed@email.com) [admin]", "Guest (session: abc123)"
     */
    public static function userName(Request $request): string
    {
        $user = $request->user();
        if ($user) {
            $role = $user->role ?? 'user';
            return "{$user->name} ({$user->email}) [{$role}]";
        }

        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');
        if ($sessionId) {
            return "Guest (session: {$sessionId})";
        }

        return 'Unknown';
    }

    /**
     * Return the user label for a resolved User model (used after auth).
     */
    public static function userLabel($user, ?string $sessionId = null): string
    {
        if ($user) {
            $role = $user->role ?? 'user';
            return "{$user->name} ({$user->email}) [{$role}]";
        }

        if ($sessionId) {
            return "Guest (session: {$sessionId})";
        }

        return 'Unknown';
    }

    // ─────────────────────────────────────────────────────────
    // Core log creator
    // ─────────────────────────────────────────────────────────

    private static function log(Request $request, string $action, string $description, string $section, string $result, $resource, array $metadata = []): \Spatie\Activitylog\Models\Activity
    {
        $user = $request->user();

        // Tell middleware to skip its own auto-log for this request
        $request->attributes->set('log_from_controller', true);

        $resourceType = null;
        $resourceId = null;

        if ($resource) {
            if (is_array($resource)) {
                $resourceType = $resource['type'] ?? null;
                $resourceId = $resource['id'] ?? null;
            } elseif (is_object($resource) && isset($resource->id)) {
                $resourceType = get_class($resource);
                $resourceId = $resource->id;
            }
        }

        $payload = null;
        if ($request && in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $payload = self::filterPayload($request->all());
        }

        $activity = self::create([
            'user_id' => $user ? $user->id : null,
            'user_identifier' => self::buildUserIdentifier($user, $request),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'section' => $section,
            'result' => $result,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->header('User-Agent'),
            'payload' => $payload,
            'metadata' => $metadata,
        ]);

        if ($request) {
            $activity->http_method = $request->method();
            $activity->url = '/' . ltrim($request->path(), '/');
            $activity->save();
        }

        return $activity;
    }

    /**
     * Build a human-readable user identifier for display.
     */
    private static function buildUserIdentifier($user, ?Request $request = null): string
    {
        if ($user) {
            $role = $user->role ?? 'user';
            return "{$user->name} ({$user->email}) [{$role}]";
        }

        if ($request) {
            $sessionId = $request->header('X-Session-ID') ?? $request->cookie('session_id');
            if ($sessionId) {
                return "Guest (session: {$sessionId})";
            }
        }

        return 'Unknown';
    }

    private static function filterPayload(array $payload): array
    {
        $sensitive = ['password', 'password_confirmation', 'token', 'card_number', 'api_token', 'cvv', 'current_password'];
        foreach ($payload as $key => $value) {
            if (in_array($key, $sensitive)) {
                $payload[$key] = '********';
            } elseif (is_array($value)) {
                $payload[$key] = self::filterPayload($value);
            }
        }
        return $payload;
    }
}
