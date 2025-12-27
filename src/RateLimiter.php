<?php

class RateLimiter {

    private string $storagePath;
    private bool $enabled;

    public function __construct(string $storagePath, array $config) {
        $this->storagePath = rtrim($storagePath, '/') . '/limits/';
        $this->enabled = $config['enabled'] ?? false;

        if ($this->enabled && !is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Check if request is allowed. 
     * Uses client IP as the identifier.
     */
    public function check(string $ip, int $limit, int $window): bool {
        if (!$this->enabled) return true;

        // Clean old files occasionally (simple garbage collection logic)
        // 1 in 100 chance to run cleanup to save I/O
        if (rand(1, 100) === 1) {
            $this->cleanup($window);
        }

        $file = $this->storagePath . md5($ip) . '.json';
        $current = time();

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            
            // If window has passed, reset
            if ($current - $data['start_time'] > $window) {
                $data = ['start_time' => $current, 'count' => 1];
            } else {
                // Check limit
                if ($data['count'] >= $limit) {
                    return false;
                }
                $data['count']++;
            }
        } else {
            $data = ['start_time' => $current, 'count' => 1];
        }

        file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }

    /**
     * Remove old limit files
     */
    private function cleanup(int $window): void {
        $files = glob($this->storagePath . '*.json');
        $now = time();
        foreach ($files as $file) {
            if ($now - filemtime($file) > $window + 60) {
                @unlink($file);
            }
        }
    }
}