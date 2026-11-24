<?php

namespace App\Support;

class StorageUrl
{
    /**
     * Format a stored asset path into a clean, relative /storage/... URL
     * or retain an external absolute URL (https://...).
     *
     * @param string|null $path
     * @return string|null
     */
    public static function format(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path));

        if ($path === '') {
            return null;
        }

        // Return external absolute URLs or data URIs as-is
        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:image/')) {
            return $path;
        }

        // Already rooted /storage/...
        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        // Clean any leading 'storage/' or '/'
        $clean = preg_replace('#^/?storage/#i', '', $path);
        $clean = ltrim($clean, '/');

        return '/storage/' . $clean;
    }
}
