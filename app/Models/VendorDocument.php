<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'type',
        'label',
        'file_path',
        'document_number',
        'status',
        'expires_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute()
    {
        return $this->file_path ? url('/api/vendor-documents/' . $this->id . '/view') : null;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function reviewHistories()
    {
        return $this->morphMany(ReviewHistory::class, 'reviewable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('VendorDocuments')
            ->logAll()
            ->logOnlyDirty();
    }
}
