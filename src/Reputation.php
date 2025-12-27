<?php

class Reputation {

    private array $disposableDomains;
    
    // Smart MX Patterns: If a domain's mail server contains these words, it's disposable.
    // This catches THOUSANDS of domains automatically without listing them.
    private const DISPOSABLE_MX_PATTERNS = [
        'yopmail',
        'guerrillamail',
        'sharklasers',
        'mailinator',
        'temp-mail',
        'tempmail',
        '10minutemail',
        'throwaway',
        'fake',
        'spam',
        'burn',
        'trash',
        'dropmail',
        'mailnesia',
        'dispostable',
        'grr.la',
        'minitts' // Added to catch minitts.net
    ];

    // Risky TLDs
    private const RISKY_TLDS = [
        'xyz', 'top', 'gq', 'cn', 'ga', 'cf', 'ml', 'tk', 'men', 'loan', 'date', 'faith', 'icu', 'monster'
    ];

    public function __construct(string $disposableConfigPath) {
        if (file_exists($disposableConfigPath)) {
            $this->disposableDomains = require $disposableConfigPath;
        } else {
            $this->disposableDomains = [];
        }
    }

    /**
     * Check if the domain is disposable via Name OR MX Record
     */
    public function isDisposable(string $domain, array $mxRecords = []): bool {
        $domain = strtolower(trim($domain));

        // 1. Direct Domain Check (Fastest)
        if (in_array($domain, $this->disposableDomains)) {
            return true;
        }

        // 2. Smart MX Fingerprinting (Catches unknown domains like forexzig.com)
        foreach ($mxRecords as $mx) {
            // Handle both simple string or array format from DNSChecker
            $host = is_array($mx) ? ($mx['host'] ?? '') : $mx;
            $host = strtolower($host);

            foreach (self::DISPOSABLE_MX_PATTERNS as $pattern) {
                if (str_contains($host, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if the TLD is considered risky
     */
    public function isRiskyTLD(string $domain): bool {
        $parts = explode('.', $domain);
        $tld = end($parts);
        return in_array(strtolower($tld), self::RISKY_TLDS);
    }

    public function hasRiskySubdomain(string $email): bool {
        $domain = explode('@', $email)[1] ?? '';
        $parts = explode('.', $domain);
        return count($parts) > 3;
    }
}