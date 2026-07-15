<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Run Daily Vendor Operations
Schedule::job(new \App\Jobs\ProcessVendorPayouts)->daily();
Schedule::job(new \App\Jobs\CheckVendorDocumentExpiry)->daily();
Schedule::job(new \App\Jobs\LiftVendorSuspensions)->daily();

// Process Affiliate Approvals
Schedule::command('affiliate:process-approvals')->dailyAt('01:00');

// Clean Activity Logs (older than 365 days)
Schedule::command('activitylog:clean')->daily();
