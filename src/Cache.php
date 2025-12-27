<?php

class Cache {

    private string $cachePath;
    private int $defaultTtl;

    public function __construct(string $cachePath, int $defaultTtl = 86400) {
        $this->cachePath = rtrim($cachePath, '/') . '/';
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cachePath)) {
            if (!mkdir($this->cachePath, 0755, true)) {
                // If we can't create cache dir, we just won't cache.
                // In production, you might want to log this error.
            }
        }
    }

    /**
     * Retrieve data from cache if it exists and hasn't expired
     */
    public function get(string $key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }

        // Check expiration
        $mtime = filemtime($filename);
        if ((time() - $mtime) > $this->defaultTtl) {
            @unlink($filename); // Delete expired file
            return null;
        }

        $data = file_get_contents($filename);
        if ($data === false) {
            return null;
        }

        $decoded = json_decode($data, true);
        
        // Return raw data or null if decode failed
        return $decoded ?: null;
    }

    /**
     * Save data to cache
     */
    public function set(string $key, array $data): bool {
        $filename = $this->getFilename($key);
        $json = json_encode($data);
        
        // Atomic write with lock to prevent race conditions
        return file_put_contents($filename, $json, LOCK_EX) !== false;
    }

    /**
     * Create a safe filename hash from the key
     */
    private function getFilename(string $key): string {
        return $this->cachePath . hash('sha256', $key) . '.cache';
    }
}