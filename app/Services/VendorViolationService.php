<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorViolation;
use Illuminate\Support\Facades\DB;

class VendorViolationService
{
    public function issueViolation(Vendor $vendor, array $data): VendorViolation
    {
        return DB::transaction(function () use ($vendor, $data) {
            $violation = $vendor->violations()->create($data);
            
            $this->evaluateVendorStatus($vendor);

            return $violation;
        });
    }

    public function evaluateVendorStatus(Vendor $vendor): void
    {
        $totalPoints = $vendor->violations()->sum('severity_points');

        if ($totalPoints <= 5) {
            // Already active, maybe send warning
            if ($vendor->status !== 'active' && $vendor->status !== 'pending') {
                $vendor->update([
                    'status' => 'active',
                    'suspension_ends_at' => null
                ]);
            }
        } elseif ($totalPoints > 5 && $totalPoints <= 10) {
            $vendor->update([
                'status' => 'suspended',
                'suspension_ends_at' => now()->addDays(30)
            ]);
        } else {
            $vendor->update([
                'status' => 'banned',
                'suspension_ends_at' => null
            ]);
        }
    }
}
