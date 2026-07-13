<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\VendorDocument;

class CheckVendorDocumentExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find verified documents expiring in exactly 30 days
        $expiringDocs = VendorDocument::where('status', 'verified')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '=', now()->addDays(30)->toDateString())
            ->get();

        foreach ($expiringDocs as $doc) {
            // Here we would typically send an email/notification to the vendor
            // For now, we'll just log it or rely on the UI highlighting it
            \Log::info("Vendor Document Expiring Soon: Vendor {$doc->vendor_id}, Document {$doc->id}");
        }

        // Expire documents that have passed their date
        VendorDocument::where('status', 'verified')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }
}
