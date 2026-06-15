<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'old_price',
        'material',
        'upholstery',
        'dimensions',
        'weight',
        'colors',
        'stars',
        'badge',
        'badge_color',
        'stock',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'colors' => 'array',
        'stars' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    public function activeColors()
    {
        return $this->hasMany(ProductColor::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function hasDiscount(): bool
    {
        return !is_null($this->old_price) && $this->old_price > $this->price;
    }

    public function discountPercent(): int
    {
        if (!$this->hasDiscount()) return 0;
        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    /**
     * Check if the product is in stock.
     * If it has active colors, at least one color must have stock > 0.
     * If no colors, product-level stock is used.
     */
    public function isInStock(): bool
    {
        if ($this->stock > 0 && $this->activeColors()->count() === 0) {
            return true;
        }
        return $this->activeColors()->where('stock', '>', 0)->exists() || $this->stock > 0;
    }

    /**
     * Get effective total stock across all active color variants.
     * Falls back to product-level stock if no colors exist.
     */
    public function getEffectiveStock(): int
    {
        $colors = $this->activeColors;
        if ($colors->isEmpty()) {
            return $this->stock;
        }
        return $colors->sum('stock');
    }
}
