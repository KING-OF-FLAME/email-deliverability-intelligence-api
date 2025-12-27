<?php

class DomainAge {

    /**
     * Attempt to determine domain age signal via RDAP.
     * * Returns: 'new', 'established', or 'unknown'
     * Note: This makes an HTTP request. We set a low timeout to ensure speed.
     */
    public function getAgeSignal(string $domain): string {
        // RDAP is the modern replacement for WHOIS (JSON based)
        // We use a public RDAP bootstrap or specific service
        $endpoint = "https://rdap.org/domain/" . urlencode($domain);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: EmailDeliverabilityAPI/1.0\r\n",
                'timeout' => 2.0, // 2 seconds max timeout
                'ignore_errors' => true // Don't fail script on 404
            ]
        ]);

        try {
            $response = @file_get_contents($endpoint, false, $context);
            
            if ($response === false) {
                return 'unknown';
            }

            $data = json_decode($response, true);
            
            if (!isset($data['events'])) {
                return 'unknown';
            }

            $registrationDate = null;
            foreach ($data['events'] as $event) {
                if ($event['eventAction'] === 'registration' || $event['eventAction'] === 'last changed') {
                    $registrationDate = $event['eventDate'];
                    break;
                }
            }

            if ($registrationDate) {
                $created = strtotime($registrationDate);
                $ageInDays = (time() - $created) / (60 * 60 * 24);

                if ($ageInDays < 30) return 'new';
                if ($ageInDays > 90) return 'established';
                return 'new'; // In between 30-90 usually treated as warning
            }

        } catch (Exception $e) {
            return 'unknown';
        }

        return 'unknown';
    }
}