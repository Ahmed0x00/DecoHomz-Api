<?php

namespace App\Jobs;

use App\Services\CloudflareService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class OptimizeUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @param string $storagePath    Relative path within the 'public' disk (e.g. "products/abc.jpg")
     * @param int    $maxWidth       Maximum width in pixels for the main image
     * @param int    $thumbWidth     Maximum width in pixels for the thumbnail
     * @param int    $quality        Compression quality (1-100)
     */
    public function __construct(
        public string $storagePath,
        public int $maxWidth = 1920,
        public int $thumbWidth = 400,
        public int $quality = 80,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('public');

        // Verify file exists
        if (!$disk->exists($this->storagePath)) {
            Log::warning("[ImageOptimizer] File not found, skipping: {$this->storagePath}");
            return;
        }

        $fullPath = $disk->path($this->storagePath);
        $fileSize = filesize($fullPath);

        // Bypass local optimization if GD and Imagick are missing
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            Log::info("[ImageOptimizer] Bypassing local optimization because GD/Imagick is not installed.");
            $newStoragePath = preg_replace('/\.[^.]+$/', '.webp', $this->storagePath);
            if ($newStoragePath !== $this->storagePath) {
                if ($disk->exists($this->storagePath)) {
                    $disk->copy($this->storagePath, $newStoragePath);
                    $disk->delete($this->storagePath);
                }
                \App\Models\ProductImage::where('image', $this->storagePath)->update([
                    'image' => $newStoragePath,
                    'thumbnail' => $newStoragePath,
                ]);
                \App\Models\Category::where('image', $this->storagePath)->update(['image' => $newStoragePath]);
            }
            return;
        }

        // Skip files under 300KB — not worth optimizing
        if ($fileSize <= 307200) {
            Log::info("[ImageOptimizer] Skipping (under 300KB): {$this->storagePath} ({$fileSize} bytes)");
            return;
        }

        try {
            // Read image with Intervention
            $image = Image::decodePath($fullPath);

            // Resize to max width, maintaining aspect ratio (only downscale)
            $width = $image->width();
            if ($width > $this->maxWidth) {
                $image->scaleDown(width: $this->maxWidth);
            }

            // Create a new storage path with .webp extension
            $newStoragePath = preg_replace('/\.[^.]+$/', '.webp', $this->storagePath);
            $newFullPath = $disk->path($newStoragePath);

            // Encode and save to new path (Intervention detects .webp extension automatically)
            $image->save($newFullPath, quality: $this->quality);

            $newSize = filesize($newFullPath);
            $saved = $fileSize - $newSize;
            $pct = $fileSize > 0 ? round(($saved / $fileSize) * 100) : 0;

            // Generate thumbnail
            $thumbStoragePath = $this->generateThumbnail($disk, $newFullPath, $newStoragePath);

            // Track URLs to purge from Cloudflare
            $urlsToPurge = [];

            // Update database records to point to the new WebP file if extension changed
            if ($newStoragePath !== $this->storagePath) {
                // Delete old unoptimized file
                if ($disk->exists($this->storagePath)) {
                    $urlsToPurge[] = $this->storagePath;
                    $disk->delete($this->storagePath);
                }

                // Update relevant database models to point to the new .webp file
                \App\Models\ProductImage::where('image', $this->storagePath)->update([
                    'image' => $newStoragePath,
                    'thumbnail' => $thumbStoragePath,
                ]);
                \App\Models\Category::where('image', $this->storagePath)->update(['image' => $newStoragePath]);

                Log::info("[ImageOptimizer] Optimized & Converted to WebP: {$this->storagePath} → {$newStoragePath} ({$fileSize} → {$newSize} bytes, saved {$saved} bytes / {$pct}%)" .
                    ($thumbStoragePath ? " | Thumbnail: {$thumbStoragePath}" : ''));

                // Update local variable for chmod
                $fullPath = $newFullPath;
            } else {
                // Same extension (already .webp) — just update thumbnail
                if ($thumbStoragePath) {
                    \App\Models\ProductImage::where('image', $this->storagePath)->update([
                        'thumbnail' => $thumbStoragePath,
                    ]);
                }

                Log::info("[ImageOptimizer] Optimized: {$this->storagePath} ({$fileSize} → {$newSize} bytes, saved {$saved} bytes / {$pct}%)");
            }

            // Fix file permissions
            @chmod($fullPath, 0644);
            if ($thumbStoragePath) {
                @chmod($disk->path($thumbStoragePath), 0644);
            }

            // Purge old URLs from Cloudflare cache
            if (!empty($urlsToPurge)) {
                $this->purgeCloudflareCache($urlsToPurge);
            }

        } catch (\Throwable $e) {
            Log::error("[ImageOptimizer] Failed to optimize {$this->storagePath}: {$e->getMessage()}");
            throw $e; // Re-throw so the queue retries
        }
    }

    /**
     * Generate a thumbnail version of the optimized image.
     *
     * @param \Illuminate\Filesystem\FilesystemAdapter $disk
     * @param string $sourcePath   Absolute path to the optimized image
     * @param string $storagePath  Relative storage path of the optimized image
     * @return string|null         Relative storage path of the thumbnail, or null on failure
     */
    protected function generateThumbnail($disk, string $sourcePath, string $storagePath): ?string
    {
        try {
            $thumbImage = Image::decodePath($sourcePath);

            // Only create thumbnail if image is wider than thumb width
            if ($thumbImage->width() <= $this->thumbWidth) {
                Log::info("[ImageOptimizer] Skipping thumbnail (image already ≤ {$this->thumbWidth}px wide): {$storagePath}");
                return null;
            }

            $thumbImage->scaleDown(width: $this->thumbWidth);

            // Create thumbnail path: products/abc123.webp → products/abc123_thumb.webp
            $thumbStoragePath = preg_replace('/(\.[^.]+)$/', '_thumb$1', $storagePath);
            $thumbFullPath = $disk->path($thumbStoragePath);

            $thumbImage->save($thumbFullPath, quality: $this->quality);

            Log::info("[ImageOptimizer] Thumbnail generated: {$thumbStoragePath} (" . filesize($thumbFullPath) . " bytes)");

            return $thumbStoragePath;
        } catch (\Throwable $e) {
            Log::warning("[ImageOptimizer] Thumbnail generation failed for {$storagePath}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Purge old image URLs from Cloudflare cache.
     *
     * @param array<string> $storagePaths  Relative paths within the 'public' disk
     */
    protected function purgeCloudflareCache(array $storagePaths): void
    {
        try {
            $cf = new CloudflareService();
            $cf->purgeStoragePaths($storagePaths);
        } catch (\Throwable $e) {
            // Cache purge is best-effort — don't fail the job
            Log::warning("[ImageOptimizer] Cloudflare cache purge failed: {$e->getMessage()}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error("[ImageOptimizer] Job permanently failed for {$this->storagePath}: " . $exception?->getMessage());
    }
}
