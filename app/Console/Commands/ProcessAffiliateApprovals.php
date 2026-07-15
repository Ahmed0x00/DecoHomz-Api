<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AffiliateService;

class ProcessAffiliateApprovals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:process-approvals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes pending affiliate commissions that have passed their hold period.';

    /**
     * Execute the console command.
     */
    public function handle(AffiliateService $affiliateService)
    {
        $this->info('Starting affiliate commission approvals...');
        
        $count = $affiliateService->processApprovals();
        
        $this->info("Successfully approved {$count} referrals.");
    }
}
