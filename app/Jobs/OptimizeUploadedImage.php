<?php

namespace App\Jobs;

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
     * @param string $storagePath  Relative path within the 'public' disk (e.g. "products/abc.jpg")
     * @param int    $maxWidth     Maximum width in pixels
     * @param int    $quality      Compression quality (1-100)
     */
    public function __construct(
        public string $storagePath,
        public int $maxWidth = 1920,
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

            // Update database records to point to the new WebP file if extension changed
            if ($newStoragePath !== $this->storagePath) {
                // Delete old unoptimized file
                if ($disk->exists($this->storagePath)) {
                    $disk->delete($this->storagePath);
                }

                // Update relevant database models to point to the new .webp file
                \App\Models\ProductImage::where('image', $this->storagePath)->update(['image' => $newStoragePath]);
                \App\Models\Category::where('image', $this->storagePath)->update(['image' => $newStoragePath]);

                Log::info("[ImageOptimizer] Optimized & Converted to WebP: {$this->storagePath} → {$newStoragePath} ({$fileSize} → {$newSize} bytes, saved {$saved} bytes / {$pct}%)");
                
                // Update local variable for chown
                $fullPath = $newFullPath;
            } else {
                Log::info("[ImageOptimizer] Optimized: {$this->storagePath} ({$fileSize} → {$newSize} bytes, saved {$saved} bytes / {$pct}%)");
            }

            // Fix ownership (in case queue runs as different user)
            if (function_exists('chown')) {
                @chown($fullPath, 'decohomz');
                @chgrp($fullPath, 'decohomz');
            }
            @chmod($fullPath, 0644);

        } catch (\Throwable $e) {
            Log::error("[ImageOptimizer] Failed to optimize {$this->storagePath}: {$e->getMessage()}");
            throw $e; // Re-throw so the queue retries
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
