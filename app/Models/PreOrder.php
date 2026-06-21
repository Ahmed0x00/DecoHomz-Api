<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
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
}
