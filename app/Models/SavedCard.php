<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedCard extends Model
{
    protected $fillable = [
        'user_id',
        'card_token',
        'masked_pan',
        'brand',
        'expiry_month',
        'expiry_year',
    ];

    protected $hidden = [
        'card_token', // Never expose the actual token to frontend
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get display label like "•••• 1111 (Visa)"
     */
    public function getDisplayLabelAttribute(): string
    {
        $label = $this->masked_pan;
        if ($this->brand) {
            $label .= " ({$this->brand})";
        }
        return $label;
    }
}
