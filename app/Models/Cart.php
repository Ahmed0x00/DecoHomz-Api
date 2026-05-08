<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_code',
        'discount',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - $this->discount);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }
    public function recalculateDiscount(): void
    {
        if (!$this->coupon_code) {
            $this->update(['discount' => 0]);
            return;
        }

        $coupon = $this->coupon;
        if (!$coupon || !$coupon->isValid()) {
            $this->update(['coupon_code' => null, 'discount' => 0]);
            return;
        }

        $subtotal = $this->getSubtotalAttribute();
        $discount = $coupon->calculateDiscount($subtotal);

        $this->update(['discount' => $discount]);
    }
}
