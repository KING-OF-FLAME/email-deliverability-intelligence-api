<?php

class DNSChecker {

    private const COMMON_SELECTORS = [
        'default', 'google', 'selector1', 'selector2',
        's1', 's2', 'k1', 'k2', 'k3',
        'mail', 'smtp', 'dkim',
        'sendgrid', 'mandrill', 'zendesk',
        '2022', '2023', '2024', '2025'
    ];

    private function normalizeDomain(string $domain): string {
        return strtolower(rtrim(trim($domain), '.'));
    }

    /**
     * Helper to safely fetch and reconstruct TXT records.
     * Handles RFC 4408 split records (where strings are chunked).
     */
    private function getTxtRecords(string $hostname): array {
        // Suppress warnings for clean API output
        $records = @dns_get_record($hostname, DNS_TXT);
        $results = [];

        if ($records) {
            foreach ($records as $r) {
                if (!empty($r['entries'])) {
                    $results[] = implode('', $r['entries']);
                } elseif (!empty($r['txt'])) {
                    $results[] = $r['txt'];
                }
            }
        }
        return $results;
    }

    /**
     * Check if domain has valid MX records
     */
    public function checkMX(string $domain): array {
        $domain = $this->normalizeDomain($domain);
        $records = @dns_get_record($domain, DNS_MX);

        $hosts = [];
        if ($records) {
            foreach ($records as $r) {
                if (!empty($r['target'])) {
                    // FIXED: Return simple string to prevent TypeError in ProviderDetector
                    $hosts[] = strtolower($r['target']); 
                }
            }
        }

        return [
            'valid' => !empty($hosts),
            'hosts' => $hosts
        ];
    }

    /**
     * Check for SPF Record (TXT) with Policy Awareness
     */
    public function checkSPF(string $domain): array {
        $domain = $this->normalizeDomain($domain);
        $records = $this->getTxtRecords($domain);

        foreach ($records as $txt) {
            if (stripos($txt, 'v=spf1') === 0) {
                return [
                    'valid' => true, // 'valid' key required for Scorer compatibility
                    'found' => true,
                    'record' => $txt,
                    'policy' =>
                        (str_contains($txt, '-all') ? 'hard_fail' :
                        (str_contains($txt, '~all') ? 'soft_fail' :
                        (str_contains($txt, '+all') ? 'allow_all' :
                        (str_contains($txt, '?all') ? 'neutral' : 'implicit_neutral'))))
                ];
            }
        }

        return ['valid' => false, 'found' => false];
    }

    /**
     * Check for DMARC Record with detailed parsing
     */
    public function checkDMARC(string $domain): array {
        $domain = $this->normalizeDomain($domain);
        $records = $this->getTxtRecords("_dmarc.$domain");

        foreach ($records as $txt) {
            if (stripos($txt, 'v=dmarc1') === 0) {
                preg_match('/p=([^;]+)/i', $txt, $p);
                preg_match('/sp=([^;]+)/i', $txt, $sp);
                preg_match('/pct=(\d+)/i', $txt, $pct);

                return [
                    'found' => true,
                    'policy' => isset($p[1]) ? strtolower(trim($p[1])) : 'none',
                    'subdomain_policy' => isset($sp[1]) ? strtolower(trim($sp[1])) : null,
                    'pct' => isset($pct[1]) ? (int)$pct[1] : 100
                ];
            }
        }

        return ['found' => false, 'policy' => 'none'];
    }

    /**
     * Attempt to detect DKIM presence using expanded heuristic list.
     */
    public function checkDKIM(string $domain): array {
        $domain = $this->normalizeDomain($domain);
        $selectors = [];

        foreach (self::COMMON_SELECTORS as $selector) {
            $records = $this->getTxtRecords("$selector._domainkey.$domain");

            foreach ($records as $txt) {
                if (stripos($txt, 'v=DKIM1') !== false) {
                    $selectors[] = $selector;
                    break; // Move to next selector once found for this key
                }
            }
        }

        return [
            'found' => !empty($selectors),
            'selectors' => $selectors,
            'confidence' =>
                count($selectors) >= 2 ? 'high' :
                (count($selectors) === 1 ? 'medium' : 'low')
        ];
    }

    /**
     * Aggregate analysis method
     */
    public function analyze(string $domain): array {
        return [
            'mx' => $this->checkMX($domain),
            'spf' => $this->checkSPF($domain),
            'dmarc' => $this->checkDMARC($domain),
            'dkim' => $this->checkDKIM($domain)
        ];
    }
}