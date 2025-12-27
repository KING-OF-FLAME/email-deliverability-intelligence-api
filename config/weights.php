<?php

/**
 * Weights for the Deliverability Scorer.
 * Adjust these to tune the algorithm.
 */

return [
    // Base score to start calculation
    'base_score' => 50,

    // Positive Signals (Add to score)
    'positive' => [
        'valid_syntax'    => 10,
        'mx_valid'        => 25,  // Critical
        'spf_valid'       => 15,
        'dkim_present'    => 10,
        'dmarc_present'   => 15,
        'dmarc_reject'    => 5,   // Bonus for strict policy
        'dmarc_quarantine'=> 3,
        'domain_age_old'  => 5,   // Established domain
        'business_email'  => 5,   // Not free provider
        'clean_reputation'=> 5,
    ],

    // Negative Signals (Subtract from score)
    'negative' => [
        'disposable'      => -50, // Immediate kill
        'role_based'      => -5,  // Slight penalty for generic emails
        'no_mx'           => -50, // Immediate kill
        'catch_all'       => 0,   // Neutral usually, but can be risky
        'new_domain'      => -10,
        'suspicious_tld'  => -15,
    ],

    // Thresholds for Risk Levels
    'thresholds' => [
        'excellent' => 80,
        'good'      => 60,
        'risky'     => 40,
        // Below 40 is 'bad'
    ]
];