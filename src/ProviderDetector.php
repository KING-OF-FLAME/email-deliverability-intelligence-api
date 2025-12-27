<?php

class ProviderDetector {

    private array $providers;
    private array $freePatterns;

    public function __construct(string $configPath) {
        if (!file_exists($configPath)) {
            throw new Exception("Providers config not found at $configPath");
        }
        $config = require $configPath;
        
        $this->freePatterns = $config['free_patterns'] ?? [];
        unset($config['free_patterns']); // Separate free list from main provider list
        $this->providers = $config;
    }

    /**
     * Identify the provider based on MX hosts
     */
    public function detect(array $mxHosts): ?string {
        if (empty($mxHosts)) return null;

        foreach ($mxHosts as $host) {
            $host = strtolower($host);

            // Check against defined providers
            foreach ($this->providers as $providerName => $signatures) {
                foreach ($signatures as $sig) {
                    if (str_contains($host, $sig)) {
                        return $providerName;
                    }
                }
            }
        }

        return null; // Unknown provider
    }

    /**
     * Check if it is likely a free email provider (Gmail, Yahoo, etc.)
     */
    public function isFreeProvider(string $domain): bool {
        $domain = strtolower($domain);
        
        foreach ($this->freePatterns as $pattern) {
            if ($domain === $pattern || str_ends_with($domain, '.' . $pattern)) {
                return true;
            }
        }
        return false;
    }
}