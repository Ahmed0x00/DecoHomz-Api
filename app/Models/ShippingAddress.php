<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'governorate',
        'postal_code',
        'country',
        'is_default',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function fullAddress(): string
    {
        return $this->address . ', ' . $this->city . ', ' . $this->governorate;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ShippingAddresses')
            ->logAll()
            ->logOnlyDirty();
    }
}
