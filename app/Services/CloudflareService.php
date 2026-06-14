<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected ?string $apiToken;
    protected ?string $zoneId;

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
        $this->zoneId = config('services.cloudflare.zone_id');
    }

    /**
     * Get the base site URL for building storage URLs.
     * Falls back to APP_URL if CLOUDFLARE_SITE_URL is not set.
     */
    protected static function siteUrl(): string
    {
        return rtrim(config('services.cloudflare.site_url') ?: config('app.url'), '/');
    }

    /**
     * Check if Cloudflare cache purging is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->zoneId);
    }

    /**
     * Purge specific URLs from Cloudflare's cache.
     *
     * @param array<string> $urls  Fully qualified URLs to purge (e.g., "https://domain.com/storage/products/abc.jpg")
     */
    public function purgeUrls(array $urls): bool
    {
        if (!$this->isConfigured()) {
            Log::debug('[Cloudflare] Cache purge skipped — not configured (missing CLOUDFLARE_API_TOKEN or CLOUDFLARE_ZONE_ID).');
            return false;
        }

        if (empty($urls)) {
            return true;
        }

        // Cloudflare allows max 30 URLs per purge request
        $chunks = array_chunk($urls, 30);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->post("https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/purge_cache", [
                        'files' => $chunk,
                    ]);

                if ($response->successful()) {
                    Log::info('[Cloudflare] Cache purged for ' . count($chunk) . ' URL(s): ' . implode(', ', $chunk));
                } else {
                    Log::warning('[Cloudflare] Cache purge failed: ' . $response->body(), [
                        'urls' => $chunk,
                        'status' => $response->status(),
                    ]);
                    return false;
                }
            } catch (\Throwable $e) {
                Log::warning('[Cloudflare] Cache purge request failed: ' . $e->getMessage(), [
                    'urls' => $chunk,
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Build the full public URL for a storage path.
     *
     * @param string $storagePath  Relative path within the 'public' disk (e.g., "products/abc.jpg")
     * @return string  Fully qualified URL
     */
    public static function storageUrl(string $storagePath): string
    {
        return self::siteUrl() . '/storage/' . ltrim($storagePath, '/');
    }

    /**
     * Purge storage paths from Cloudflare cache (convenience method).
     *
     * @param array<string> $storagePaths  Relative paths within the 'public' disk
     */
    public function purgeStoragePaths(array $storagePaths): bool
    {
        $urls = array_map(fn($path) => self::storageUrl($path), $storagePaths);
        return $this->purgeUrls($urls);
    }
}
