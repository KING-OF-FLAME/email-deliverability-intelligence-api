<?php

class Response {

    /**
     * Send a JSON success response
     */
    public static function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        // Add CORS headers if needed for direct browser usage
        header('Access-Control-Allow-Origin: *'); 
        header('Access-Control-Allow-Methods: GET');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send a JSON error response
     */
    public static function error(string $message, int $code = 400): void {
        self::json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'timestamp' => date('c')
        ], $code);
    }
}