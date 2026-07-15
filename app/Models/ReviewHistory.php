<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewHistory extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'admin_id',
        'from_status',
        'to_status',
        'comment',
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ReviewHistories')
            ->logAll()
            ->logOnlyDirty();
    }
}
