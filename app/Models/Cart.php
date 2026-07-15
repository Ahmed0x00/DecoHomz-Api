<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_code',
        'affiliate_code',
        'discount',
        'affiliate_discount',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'affiliate_discount' => 'decimal:2',
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

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_code', 'referral_code');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - $this->discount - $this->affiliate_discount);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }
    public function recalculateDiscount(): void
    {
        if ($this->coupon_code) {
            $coupon = $this->coupon;
            if (!$coupon || !$coupon->isValid()) {
                $this->update(['coupon_code' => null, 'discount' => 0]);
            } else {
                $subtotal = $this->getSubtotalAttribute();
                $discount = $coupon->calculateDiscount($subtotal);
                $this->update(['discount' => $discount]);
            }
        } else {
            $this->update(['discount' => 0]);
        }

        if ($this->affiliate_code) {
            $affiliateService = app(\App\Services\AffiliateService::class);
            
            $eligibleSubtotal = 0;
            $includeVendorProducts = \App\Models\Setting::getValue('affiliate_include_vendor', '0') === '1';

            foreach ($this->items as $item) {
                if (!$includeVendorProducts && $item->product && $item->product->vendor_id !== null) {
                    continue;
                }
                $eligibleSubtotal += ($item->price * $item->quantity);
            }

            if ($eligibleSubtotal > 0) {
                $discount = $affiliateService->calculateDiscount($eligibleSubtotal);
                $this->update(['affiliate_discount' => $discount]);
            } else {
                $this->update(['affiliate_code' => null, 'affiliate_discount' => 0]);
            }
        } else {
            $this->update(['affiliate_discount' => 0]);
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Cart')
            ->logAll()
            ->logOnlyDirty();
    }
}
