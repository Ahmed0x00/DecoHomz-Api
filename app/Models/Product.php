<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Products')
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'category_id',
        'vendor_id',
        'vendor_status',
        'vendor_price',
        'name',
        'slug',
        'description',
        'price',
        'old_price',
        'material',
        'upholstery',
        'dimensions',
        'weight',
        'specifications',
        'colors',
        'stars',
        'badge',
        'badge_color',
        'stock',
        'is_active',
        'is_featured',
        'fake_sold_count',
        'min_viewing_count',
        'max_viewing_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'vendor_price' => 'decimal:2',
        'specifications' => 'array',
        'colors' => 'array',
        'stars' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'fake_sold_count' => 'integer',
        'min_viewing_count' => 'integer',
        'max_viewing_count' => 'integer',
    ];

    protected $appends = [
        'sold_count',
        'viewing_count',
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function specification()
    {
        return $this->hasOne(ProductSpecification::class);
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

    public function reviewHistories()
    {
        return $this->morphMany(ReviewHistory::class, 'reviewable')->latest();
    }

    public function latestReviewHistory()
    {
        return $this->morphOne(ReviewHistory::class, 'reviewable')->latestOfMany();
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
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('vendor_id')
                  ->orWhere('vendor_status', 'published');
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeDecoHomzOwned($query)
    {
        return $query->whereNull('vendor_id');
    }

    public function scopeVendorPublished($query)
    {
        return $query->whereNotNull('vendor_id')
            ->where('vendor_status', 'published')
            ->where('is_active', true);
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

    public function getSoldCountAttribute(): int
    {
        if (!is_null($this->fake_sold_count)) {
            return $this->fake_sold_count;
        }

        $cacheKey = "product_real_sold_count:{$this->id}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () {
            return (int) $this->orderItems()
                ->whereHas('order', function ($q) {
                    $q->where('status', '!=', Order::STATUS_CANCELLED);
                })
                ->sum('quantity');
        });
    }

    public function getViewingCountAttribute(): int
    {
        $min = $this->min_viewing_count ?? 12;
        $max = $this->max_viewing_count ?? max($min, 45);

        if ($min > $max) {
            $temp = $min;
            $min = $max;
            $max = $temp;
        }

        return rand($min, $max);
    }
}
