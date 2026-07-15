<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'order_item_id',
        'type',
        'amount',
        'description',
        'status',
        'available_at',
        'paid_at',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'available_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('VendorTransactions')
            ->logAll()
            ->logOnlyDirty();
    }
}
