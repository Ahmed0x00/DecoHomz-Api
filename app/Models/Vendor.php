<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_name',
        'phone',
        'email',
        'address',
        'workshop_address',
        'bank_account_number',
        'e_wallet_number',
        'status',
        'suspension_ends_at',
        'contract_accepted_at',
        'admin_notes',
    ];

    protected $casts = [
        'suspension_ends_at' => 'datetime',
        'contract_accepted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function violations()
    {
        return $this->hasMany(VendorViolation::class);
    }

    public function transactions()
    {
        return $this->hasMany(VendorTransaction::class);
    }

    public function inspections()
    {
        return $this->hasMany(WarehouseInspection::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}
