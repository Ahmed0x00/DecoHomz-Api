<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class BackfillActivityLogSections extends Command
{
    protected $signature = 'logs:backfill-sections';
    protected $description = 'Backfill section for existing activity logs that have section=General based on action keywords';

    private const SECTION_MAP = [
        'login' => 'Auth',
        'logout' => 'Auth',
        'register' => 'Auth',
        'registration' => 'Auth',
        'password' => 'Auth',
        'profile' => 'Auth',
        'auth' => 'Auth',
        'order' => 'Orders',
        'orders' => 'Orders',
        'place order' => 'Orders',
        'cancel order' => 'Orders',
        'user' => 'Users',
        'users' => 'Users',
        'product' => 'Products',
        'products' => 'Products',
        'category' => 'Categories',
        'categories' => 'Categories',
        'review' => 'Reviews',
        'reviews' => 'Reviews',
        'approve review' => 'Reviews',
        'reject review' => 'Reviews',
        'coupon' => 'Coupons',
        'coupons' => 'Coupons',
        'cart' => 'Cart',
        'address' => 'Addresses',
        'addresses' => 'Addresses',
        'wishlist' => 'Wishlist',
        'wishlists' => 'Wishlist',
        'contact' => 'Contacts',
        'contacts' => 'Contacts',
        'setting' => 'Settings',
        'settings' => 'Settings',
        'dashboard' => 'General',
        'chart' => 'General',
        'statistic' => 'General',
        'log' => 'General',
        'activity' => 'General',
    ];

    private const RESULT_MAP = [
        'success' => 'success',
        'created' => 'success',
        'approved' => 'success',
        'login' => 'success',
        'register' => 'success',
        'logout' => 'success',
        'update' => 'success',
        'toggle' => 'success',
        'apply' => 'success',
        'list' => 'read',
        'view' => 'read',
        'show' => 'read',
        'search' => 'read',
        'delete' => 'deletion',
        'deleted' => 'deletion',
        'failed' => 'failure',
        'denied' => 'failure',
        'incorrect' => 'failure',
        'cancel' => 'warning',
    ];

    public function handle(): int
    {
        // Fix all logs that have section=General but should have a proper section
        $updated = 0;

        ActivityLog::where('section', 'General')
            ->chunkById(200, function ($logs) use (&$updated) {
                foreach ($logs as $log) {
                    $section = $this->inferSection($log);
                    $result = $this->inferResult($log);
                    $log->updateQuietly([
                        'section' => $section,
                        'result' => $result,
                    ]);
                    $updated++;
                }
            });

        $this->info("Backfilled {$updated} logs with correct section and result values.");
        return 0;
    }

    private function inferSection(ActivityLog $log): string
    {
        $text = strtolower(($log->action ?? '') . ' ' . ($log->description ?? ''));

        // Sort by key length descending to match longer phrases first
        $keywords = array_keys(self::SECTION_MAP);
        usort($keywords, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return self::SECTION_MAP[$keyword];
            }
        }

        return 'General';
    }

    private function inferResult(ActivityLog $log): string
    {
        $action = strtolower($log->action ?? '');
        $desc = strtolower($log->description ?? '');
        $text = $action . ' ' . $desc;

        foreach (self::RESULT_MAP as $keyword => $result) {
            if (str_contains($text, $keyword)) {
                return $result;
            }
        }

        return $log->result ?? 'info';
    }
}
