<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorViolation extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'admin_id',
        'product_id',
        'violation_type',
        'description',
        'severity_points',
        'action_taken',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('VendorViolations')
            ->logAll()
            ->logOnlyDirty();
    }
}
