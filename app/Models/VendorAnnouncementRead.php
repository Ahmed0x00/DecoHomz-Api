<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAnnouncementRead extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'vendor_announcement_id'];
}
