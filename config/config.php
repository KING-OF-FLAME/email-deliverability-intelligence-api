<?php

return [
    'api_name' => 'Email Deliverability Intelligence API',
    'version'  => '1.0.0',
    
    // Debug mode (Set to false in production)
    'debug' => true,

    // File paths
    'log_path'   => __DIR__ . '/../logs/',
    'cache_path' => __DIR__ . '/../storage/cache/',

    // Cache Settings (Shared Hosting Safe)
    'cache_ttl' => 86400, // 24 Hours in seconds

    // Rate Limiting (Default fallback)
    'rate_limit' => [
        'enabled' => true,
        'window' => 60, // 1 minute
        'limit'  => 120 // 2 requests per second avg
    ],

    // Default DNS Servers (Google & Cloudflare) for fallback if local DNS fails
    // Note: dns_get_record uses OS resolver, but we keep these for RDAP/Future use
    'dns_servers' => ['8.8.8.8', '1.1.1.1'],
];