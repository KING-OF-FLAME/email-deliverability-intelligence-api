<?php

// 1. Setup Environment
error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// 2. Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 3. Load Configurations
$config     = require __DIR__ . '/../config/config.php';
$weights    = __DIR__ . '/../config/weights.php';
$providers  = __DIR__ . '/../config/providers.php';
$disposable = __DIR__ . '/../config/disposable.php';

try {
    $response = new Response();
    
    // CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-RapidAPI-Key');
        exit;
    }

    // Rate Limiting
    $limiter = new RateLimiter(__DIR__ . '/../storage/', $config['rate_limit']);
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    if (!$limiter->check($clientIP, $config['rate_limit']['limit'], $config['rate_limit']['window'])) {
        Response::error("Rate limit exceeded.", 429);
    }

    // Input Validation
    $email = $_GET['email'] ?? null;
    if (!$email) Response::error("Missing 'email' parameter.", 400);
    
    $email = trim(urldecode($email));
    $validator = new Validator();
    
    // Cache Check
    $cache = new Cache($config['cache_path'], $config['cache_ttl']);
    $cachedResult = $cache->get($email);
    
    if ($cachedResult) {
        $cachedResult['cached'] = true;
        Response::json($cachedResult);
    }

    // Start Analysis
    $domain = $validator->getDomain($email);
    $syntaxValid = $validator->isValidSyntax($email);

    if (!$syntaxValid) {
        Response::json([
            'email' => $email, 'valid_syntax' => false, 'deliverability_score' => 0,
            'risk_level' => 'bad', 'confidence' => 'high', 'checked_at' => date('c')
        ]);
    }

    // Classes
    $dns        = new DNSChecker();
    $detector   = new ProviderDetector($providers);
    $reputation = new Reputation($disposable);
    $ageChecker = new DomainAge();
    $scorer     = new Scorer($weights);

    // Execution
    $mx      = $dns->checkMX($domain); // Returns ['valid'=>bool, 'hosts'=>array]
    $spf     = $dns->checkSPF($domain);
    $dmarc   = $dns->checkDMARC($domain);
    $dkim    = $dns->checkDKIM($domain);
    
    $providerName = $detector->detect($mx['hosts']);
    $isFree       = $detector->isFreeProvider($domain);
    $ageSignal    = $ageChecker->getAgeSignal($domain);
    
    // UPDATED: Pass MX hosts to isDisposable for "Smart Check"
    $isDisposable = $reputation->isDisposable($domain, $mx['hosts']);
    
    $isRiskyTLD   = $reputation->isRiskyTLD($domain);
    $isRoleBased  = $validator->isRoleBased($email);
    $riskySub     = $reputation->hasRiskySubdomain($email);

    // Scoring
    $scoreData = $scorer->calculate([
        'valid_syntax' => true,
        'mx' => $mx,
        'spf' => $spf,
        'dmarc' => $dmarc,
        'dkim' => $dkim,
        'is_free_provider' => $isFree,
        'domain_age' => $ageSignal,
        'is_disposable' => $isDisposable,
        'is_role_based' => $isRoleBased,
        'mx_valid' => $mx['valid']
    ]);

    // Result Construction
    $finalResult = [
        'email' => $email,
        'email_hash' => 'sha256:' . hash('sha256', $email),
        'valid_syntax' => true,
        'domain' => $domain,
        'provider' => $providerName ?: 'Unknown',
        'free_provider' => $isFree,
        
        'mx' => $mx['valid'],
        'spf' => $spf['valid'],
        'dmarc' => $dmarc['policy'] ?? 'none',
        'dkim' => $dkim, // Now returns full array
        
        'disposable' => $isDisposable,
        'role_based' => $isRoleBased,
        'catch_all' => false,
        
        'domain_age_signal' => $ageSignal,
        'subdomain_risk' => $riskySub,
        'reputation_flag' => ($isDisposable ? 'disposable_detected' : ($isRiskyTLD ? 'risky_tld' : 'clean')),
        
        'deliverability_score' => $scoreData['score'],
        'risk_level' => $scoreData['risk_level'],
        'confidence' => 'high',
        
        'score_breakdown' => $scoreData['breakdown'],
        'fraud_risk' => ($isDisposable || $scoreData['score'] < 40) ? 'high' : 'low',
        
        'recommended_for' => $scoreData['recommended'],
        'not_recommended_for' => $scoreData['not_recommended'],
        
        'cached' => false,
        'checked_at' => date('c')
    ];

    $cache->set($email, $finalResult);
    Response::json($finalResult);

} catch (Exception $e) {
    error_log($e->getMessage());
    Response::error("Internal Server Error", 500);
}