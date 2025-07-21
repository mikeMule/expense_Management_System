<?php
class AssetManager
{
    /**
     * Generates a URL for a given asset path with a cache-busting query string.
     * The version is based on the file's last modification time.
     *
     * @param string $path The path to the asset file relative to the project root.
     * @return string The asset path with the version query string.
     */
    public static function url($path)
    {
        $file_path = __DIR__ . '/../' . ltrim($path, '/');

        if (!file_exists($file_path)) {
            // Log the error for debugging, but don't break the page.
            error_log("Asset file not found: " . $file_path);
            return $path; // Return the original path as a fallback.
        }

        $last_modified = @filemtime($file_path);
        if ($last_modified === false) {
            // If we can't get the timestamp, use the current time as a fallback.
            $last_modified = time();
        }

        return $path . '?v=' . $last_modified;
    }
}
