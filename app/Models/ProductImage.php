<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_color_id',
        'image',
        'thumbnail',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }

    public function getUrlAttribute()
    {
        return '/storage/' . $this->image;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail ? '/storage/' . $this->thumbnail : $this->url;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ProductImages')
            ->logAll()
            ->logOnlyDirty();
    }
}
