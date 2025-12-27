<?php

class Scorer {

    private array $weights;

    public function __construct(string $weightsPath) {
        if (!file_exists($weightsPath)) {
            throw new Exception("Weights config not found");
        }
        $this->weights = require $weightsPath;
    }

    public function calculate(array $results): array {
        $score = $this->weights['base_score'];
        $breakdown = [];
        $w = $this->weights;

        // Pre-calculate common flags for cleaner logic scope
        $spfValid = is_array($results['spf']) ? ($results['spf']['valid'] ?? false) : (bool)$results['spf'];
        $dkimFound = is_array($results['dkim']) ? ($results['dkim']['found'] ?? false) : (bool)$results['dkim'];

        // ---------------------------------------------------------
        // 1. POSITIVE SIGNALS
        // ---------------------------------------------------------

        if ($results['valid_syntax']) {
            $score += $w['positive']['valid_syntax'];
            $breakdown['syntax'] = $w['positive']['valid_syntax'];
        }

        if ($results['mx']['valid']) {
            $score += $w['positive']['mx_valid'];
            $breakdown['mx'] = $w['positive']['mx_valid'];
        }

        // Handle SPF
        if ($spfValid) {
            $score += $w['positive']['spf_valid'];
            $breakdown['spf'] = $w['positive']['spf_valid'];
        }

        // Handle DKIM
        if ($dkimFound) {
            $score += $w['positive']['dkim_present'];
            $breakdown['dkim'] = $w['positive']['dkim_present'];
        }

        // DMARC Logic
        if ($results['dmarc']['found']) {
            $score += $w['positive']['dmarc_present'];
            $breakdown['dmarc'] = $w['positive']['dmarc_present'];

            $policy = $results['dmarc']['policy'] ?? 'none';
            if ($policy === 'reject') {
                $score += $w['positive']['dmarc_reject'];
            } elseif ($policy === 'quarantine') {
                $score += $w['positive']['dmarc_quarantine'];
            }
        }

        if (!$results['is_free_provider']) {
            $score += $w['positive']['business_email'];
            $breakdown['business_domain'] = $w['positive']['business_email'];
        }

        if ($results['domain_age'] === 'established') {
            $score += $w['positive']['domain_age_old'];
        }

        // ---------------------------------------------------------
        // 2. NEGATIVE SIGNALS
        // ---------------------------------------------------------

        // Handle Disposable / Alias Logic (3-Level Detection)
        $disposableStatus = $results['is_disposable'];

        if ($disposableStatus === true || $disposableStatus === 'true') {
            // CASE 1: STANDARD DISPOSABLE (Trash/Burner)
            $score += $w['negative']['disposable']; // Massive penalty
            $breakdown['disposable_penalty'] = $w['negative']['disposable'];
            // Force kill score immediately
            $score = min($score, 10); 
        } 
        elseif ($disposableStatus === 'alias') {
            // CASE 2: ALIAS / FORWARDING (SimpleLogin, AnonAddy, etc.)
            // "Disguised" email. Valid for signup, but bad for marketing/LTV.
            $penalty = -20;
            $score += $penalty;
            $breakdown['alias_penalty'] = $penalty;
        }

        if ($results['is_role_based']) {
            $score += $w['negative']['role_based'];
            $breakdown['role_penalty'] = $w['negative']['role_based'];
        }

        if (!$results['mx']['valid']) {
            $score += $w['negative']['no_mx'];
            $breakdown['no_mx_penalty'] = $w['negative']['no_mx'];
        }

        if ($results['domain_age'] === 'new') {
            $score += $w['negative']['new_domain'];
            $breakdown['new_domain_penalty'] = $w['negative']['new_domain'];
        }

        // ---------------------------------------------------------
        // 3. ADVANCED HEURISTICS
        // ---------------------------------------------------------

        // A. WEAK AUTHENTICATION CAP
        // Logic: If it's a Business Domain (not Gmail/Yahoo) but lacks Strong Auth (DKIM or Strict DMARC),
        // it is likely a low-quality or "Smart Disposable" domain (like minitts.net).
        
        $dmarcPolicy = $results['dmarc']['policy'] ?? 'none';
        $hasStrictDmarc = in_array($dmarcPolicy, ['reject', 'quarantine']);
        $hasStrongAuth  = $dkimFound || $hasStrictDmarc;

        if (!$results['is_free_provider'] && !$hasStrongAuth) {
            $penalty = -30;
            $score += $penalty;
            $breakdown['weak_auth_penalty'] = $penalty;
            
            // Hard Cap: A business domain without DKIM/Strict DMARC is never "Excellent".
            // We cap it at 50 (Risky/Neutral border).
            if ($score > 50) {
                $score = 50;
                $breakdown['score_capped'] = 'weak_auth_limit';
            }
        }

        // B. SUSPICIOUS USERNAME DETECTION (Bot Heuristic)
        // Detects generated usernames like "uwqqv93760" (High digit ratio)
        // Requires 'email' to be present in $results.
        if (isset($results['email'])) {
            if ($this->isSuspiciousUsername($results['email'])) {
                $penalty = -20;
                $score += $penalty;
                $breakdown['suspicious_username'] = $penalty;
            }
        }

        // ---------------------------------------------------------
        // 4. FINAL CALCULATIONS
        // ---------------------------------------------------------

        // Cap score 0-100
        $score = max(0, min(100, $score));

        // Determine Risk Level
        $risk = 'bad';
        // Note: Thresholds in weights.php must be sorted descending (e.g. 80, 60, 40...)
        foreach ($w['thresholds'] as $label => $threshold) {
            if ($score >= $threshold) {
                $risk = $label;
                break;
            }
        }

        // Overrides
        if ($disposableStatus === true || $disposableStatus === 'true') {
            $risk = 'bad';
        } elseif ($disposableStatus === 'alias') {
            // Alias emails are valid but risky for long-term value
            if ($risk === 'excellent' || $risk === 'good') {
                $risk = 'risky';
            }
        }

        // Recommendation Logic
        $recommended = [];
        $notRecommended = [];

        if ($risk === 'excellent' || $risk === 'good') {
            $recommended = ['signup', 'transactional', 'marketing'];
        } elseif ($risk === 'risky') {
            $recommended = ['signup']; 
            $notRecommended = ['cold_marketing', 'high_value_transaction'];
        } else {
            $notRecommended = ['signup', 'transactional', 'marketing'];
        }

        // Specific Alias overrides
        if ($disposableStatus === 'alias') {
            // Remove 'transactional' and 'marketing' from recommended if present
            $recommended = array_diff($recommended, ['marketing', 'transactional']);
            // Explicitly add to not_recommended
            $notRecommended = array_unique(array_merge($notRecommended, ['marketing', 'transactional']));
            // Re-index array keys cleanly
            $recommended = array_values($recommended);
            $notRecommended = array_values($notRecommended);
        }

        return [
            'score' => $score,
            'risk_level' => $risk,
            'breakdown' => $breakdown,
            'recommended' => $recommended,
            'not_recommended' => $notRecommended
        ];
    }

    /**
     * Detects bot-like usernames (e.g. uwqqv93760)
     * Heuristic: High ratio of digits or pure random-looking strings
     */
    private function isSuspiciousUsername(string $email): bool {
        $local = explode('@', $email)[0];
        $len = strlen($local);
        
        // Short usernames are usually fine
        if ($len < 6) return false;

        // Calculate digit density
        $digits = preg_match_all('/[0-9]/', $local);
        $digitRatio = $len > 0 ? ($digits / $len) : 0;

        // Heuristic 1: If username is long (>8) and has >30% numbers (e.g. "john12345" or "ab9281c")
        if ($len > 8 && $digitRatio > 0.30) {
            return true;
        }

        // Heuristic 2: If it looks like a hex string (common in temporary mails)
        if ($len > 10 && preg_match('/^[a-f0-9]+$/i', $local)) {
            return true;
        }

        return false;
    }
}