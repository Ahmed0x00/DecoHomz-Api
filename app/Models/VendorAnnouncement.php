<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'message', 'type', 'action_url'];
}
