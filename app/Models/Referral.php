<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use LogsActivity;

    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_HOLDING = 'holding';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_CLAWBACK = 'clawback';

    protected $fillable = [
        'affiliate_id',
        'order_id',
        'referred_user_id',
        'order_subtotal',
        'discount_amount',
        'commission_amount',
        'commission_status',
        'hold_started_at',
        'hold_expires_at',
        'approved_at',
        'paid_at',
        'payout_reference',
        'revoke_reason',
        'buyer_ip_address',
        'fraud_flags',
    ];

    protected $casts = [
        'order_subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'hold_started_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'fraud_flags' => 'array',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function canBeApproved(): bool
    {
        return $this->commission_status === self::STATUS_HOLDING 
            && $this->hold_expires_at 
            && $this->hold_expires_at->isPast();
    }

    public function revoke(string $reason): void
    {
        $this->update([
            'commission_status' => self::STATUS_REVOKED,
            'revoke_reason' => $reason
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Referrals')
            ->logAll()
            ->logOnlyDirty();
    }
}
