<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Vendor;

class LiftVendorSuspensions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Vendor::where('status', 'suspended')
            ->whereNotNull('suspension_ends_at')
            ->where('suspension_ends_at', '<=', now())
            ->update([
                'status' => 'active',
                'suspension_ends_at' => null
            ]);
    }
}
