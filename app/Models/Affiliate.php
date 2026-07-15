<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
        'is_active',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'total_referrals',
        'total_earnings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_referrals' => 'integer',
        'total_earnings' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($affiliate) {
            if (empty($affiliate->referral_code)) {
                $affiliate->referral_code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'DH-' . strtoupper(Str::random(6));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Affiliates')
            ->logAll()
            ->logOnlyDirty();
    }
}
