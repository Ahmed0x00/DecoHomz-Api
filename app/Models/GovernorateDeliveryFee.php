<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovernorateDeliveryFee extends Model
{
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
}
