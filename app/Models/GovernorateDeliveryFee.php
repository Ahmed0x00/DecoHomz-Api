<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovernorateDeliveryFee extends Model
{
    use LogsActivity;

    protected $fillable = [
        'governorate_name',
        'governorate_name_ar',
        'delivery_fee',
        'min_free_delivery_order',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'min_free_delivery_order' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Resolve a governorate fee record from a user-provided name.
     * Supports English, Arabic, and partial matches.
     */
    public static function resolveByName(?string $name): ?self
    {
        if (!$name || trim($name) === '') {
            return null;
        }

        $normalized = self::normalizeGovernorateName($name);
        $fees = static::active()->get();

        foreach ($fees as $fee) {
            if (self::normalizeGovernorateName($fee->governorate_name) === $normalized) {
                return $fee;
            }
            if ($fee->governorate_name_ar && self::normalizeGovernorateName($fee->governorate_name_ar) === $normalized) {
                return $fee;
            }
        }

        foreach ($fees as $fee) {
            $en = self::normalizeGovernorateName($fee->governorate_name);
            $ar = $fee->governorate_name_ar ? self::normalizeGovernorateName($fee->governorate_name_ar) : '';

            if (($en && (str_contains($en, $normalized) || str_contains($normalized, $en))) ||
                ($ar && (str_contains($ar, $normalized) || str_contains($normalized, $ar)))) {
                return $fee;
            }
        }

        return null;
    }

    private static function normalizeGovernorateName(string $name): string
    {
        $name = trim(mb_strtolower($name));
        $name = preg_replace('/\s*governorate\s*/i', '', $name) ?? $name;
        $name = preg_replace('/^محافظة\s*/u', '', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return trim($name);
    }

    /**
     * Get fee for a given order subtotal.
     * Returns 0 if order subtotal >= min_free_delivery_order.
     */
    public function getFeeForSubtotal(float $subtotal): float
    {
        if ($this->min_free_delivery_order > 0 && $subtotal >= $this->min_free_delivery_order) {
            return 0;
        }
        return (float) $this->delivery_fee;
    }

    /**
     * Scope active only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get display name (Arabic if available, else English).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->governorate_name_ar ?: $this->governorate_name;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('GovernorateDeliveryFees')
            ->logAll()
            ->logOnlyDirty();
    }
}
