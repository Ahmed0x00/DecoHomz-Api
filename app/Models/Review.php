<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'is_approved',
        'is_rejected',
        'reviewer_name',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(function ($user, $review) {
            $user->id = null;
            $user->name = $review->reviewer_name ?? 'Anonymous';
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
