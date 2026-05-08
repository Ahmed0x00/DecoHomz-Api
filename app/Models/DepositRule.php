<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'percentage',
        'minimum_amount',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the active deposit rule (or the first one if none active).
     */
    public static function getActiveRule(): ?self
    {
        return self::where('is_active', true)->first() ?? self::first();
    }

    /**
     * Calculate deposit amount for a given total.
     */
    public function calculateDeposit(float $total): float
    {
        $deposit = ($total * $this->percentage) / 100;
        return max($this->minimum_amount, $deposit);
    }
}
