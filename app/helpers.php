<?php

if (! function_exists('asset_v')) {
    /**
     * Get the relative path to a public asset with an auto-updating cache-busting version query string.
     *
     * @param string $path
     * @return string
     */
    function asset_v(string $path): string
    {
        $cleanPath = ltrim($path, '/');
        $fullPath = public_path($cleanPath);
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        return '/' . $cleanPath . '?v=' . $version;
    }
}
