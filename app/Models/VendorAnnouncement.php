<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorAnnouncement extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = ['title', 'message', 'type', 'action_url'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('VendorAnnouncements')
            ->logAll()
            ->logOnlyDirty();
    }
}
