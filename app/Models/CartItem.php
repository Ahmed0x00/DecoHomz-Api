<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'variant',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getColorAttribute()
    {
        if (!$this->variant) {
            return null;
        }
        return \App\Models\ProductColor::where('product_id', $this->product_id)
            ->where('color_slug', $this->variant)
            ->where('is_active', true)
            ->first();
    }

    public function getPriceAttribute(): float
    {
        $color = $this->color;
        if ($color) {
            return (float) $this->product->price + (float) $color->price_modifier;
        }
        return $this->product->price ?? 0;
    }

    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
