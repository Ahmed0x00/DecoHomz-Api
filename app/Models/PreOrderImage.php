<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrderImage extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'pre_order_id',
        'image',
    ];

    protected $appends = ['url'];

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('PreOrderImages')
            ->logAll()
            ->logOnlyDirty();
    }
}
