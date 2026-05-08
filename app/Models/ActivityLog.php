<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'section',
        'result',
        'ip_address',
        'user_agent',
        'payload',
        'response_data',
        'old_values',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_data' => 'array',
        'old_values' => 'array',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────────────────────
    // Section-based logging methods — LOG DIRECTLY, no middleware
    // ─────────────────────────────────────────────────────────

    public static function auth(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Auth', $result, $resource);
    }

    public static function orders(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Orders', $result, $resource);
    }

    public static function users(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Users', $result, $resource);
    }

    public static function products(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Products', $result, $resource);
    }

    public static function categories(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Categories', $result, $resource);
    }

    public static function reviews(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Reviews', $result, $resource);
    }

    public static function coupons(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Coupons', $result, $resource);
    }

    public static function cart(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Cart', $result, $resource);
    }

    public static function addresses(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Addresses', $result, $resource);
    }

    public static function wishlist(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Wishlist', $result, $resource);
    }

    public static function contacts(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Contacts', $result, $resource);
    }

    public static function settings(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'Settings', $result, $resource);
    }

    public static function general(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'General', $result, $resource);
    }

    public static function deliveryFees(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'DeliveryFees', $result, $resource);
    }

    public static function productColors(Request $request, string $action, string $description, $resource = null, string $result = 'success'): self
    {
        return self::log($request, $action, $description, 'ProductColors', $result, $resource);
    }

    // ─────────────────────────────────────────────────────────
    // Backward-compat severity aliases — log with General section
    // ─────────────────────────────────────────────────────────

    public static function success(Request $request, string $action, string $description, $resource = null): self
    {
        return self::general($request, $action, $description, $resource, 'success');
    }

    public static function warning(Request $request, string $action, string $description, $resource = null): self
    {
        return self::general($request, $action, $description, $resource, 'warning');
    }

    public static function critical(Request $request, string $action, string $description, $resource = null): self
    {
        return self::general($request, $action, $description, $resource, 'failure');
    }

    public static function info(Request $request, string $action, string $description, $resource = null): self
    {
        return self::general($request, $action, $description, $resource, 'read');
    }

    // ─────────────────────────────────────────────────────────
    // Core log creator
    // ─────────────────────────────────────────────────────────

    private static function log(Request $request, string $action, string $description, string $section, string $result, $resource): self
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
                $resourceType = strtolower(class_basename($resource));
                $resourceId = $resource->id;
            }
        }

        $payload = null;
        if ($request && in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $payload = self::filterPayload($request->all());
        }

        return self::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'section' => $section,
            'result' => $result,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->header('User-Agent'),
            'payload' => $payload,
        ]);
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
