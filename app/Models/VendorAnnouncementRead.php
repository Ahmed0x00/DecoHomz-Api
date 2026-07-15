<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAnnouncementRead extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = ['vendor_id', 'vendor_announcement_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('VendorAnnouncementReads')
            ->logAll()
            ->logOnlyDirty();
    }
}
