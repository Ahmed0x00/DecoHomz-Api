<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'hex_code',
        'color_slug',
        'price_modifier',
        'stock',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product that owns this color.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get final price for this color variant.
     * Base product price + color's price modifier.
     */
    public function getPriceAttribute(): float
    {
        if (!$this->product) {
            return (float) $this->price_modifier;
        }
        return (float) $this->product->price + (float) $this->price_modifier;
    }

    /**
     * Scope active colors only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this color is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Generate a URL-safe slug from the color name.
     */
    public static function generateSlug(string $name, int $productId): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        // Ensure uniqueness per product
        $exists = static::where('product_id', $productId)->where('color_slug', $slug)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }
        return $slug;
    }
}
