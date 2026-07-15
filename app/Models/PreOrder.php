<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'notes',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function images()
    {
        return $this->hasMany(PreOrderImage::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('PreOrders')
            ->logAll()
            ->logOnlyDirty();
    }
}
