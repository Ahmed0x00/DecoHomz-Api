<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'order_number',
        'status',
        'subtotal',
        'discount',
        'delivery_fee',
        'total',
        'payment_method',
        'payment_status',
        'deposit_amount',
        'vat_amount',
        'coupon_id',
        'notes',
        'refund_status',
        'refund_reason',
        'refund_handled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID_DEPOSIT = 'paid_deposit';
    public const PAYMENT_FULL_PAID = 'full_paid';
    public const PAYMENT_REFUNDED = 'refunded';

    public const REFUND_PENDING = 'pending';
    public const REFUND_APPROVED = 'approved';
    public const REFUND_REJECTED = 'rejected';

    public function canRequestRefund(): bool
    {
        return in_array($this->payment_status, [self::PAYMENT_PAID_DEPOSIT, self::PAYMENT_FULL_PAID])
            && is_null($this->refund_status);
    }

    public static function generateOrderNumber(): string
    {
        return 'DH' . strtoupper(uniqid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }
}
