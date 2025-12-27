<?php

class Validator {

    // Common role-based prefixes that usually indicate a non-personal address
    private const ROLE_BASED_PREFIXES = [
        'admin', 'administrator', 'webmaster', 'hostmaster', 'postmaster',
        'info', 'support', 'sales', 'contact', 'help', 'api', 'billing',
        'jobs', 'hr', 'marketing', 'media', 'press', 'office', 'mail',
        'tech', 'noreply', 'no-reply', 'inbox', 'finance', 'audit'
    ];

    /**
     * Check if email syntax is valid according to RFC 822/5322
     */
    public function isValidSyntax(string $email): bool {
        // Basic filter_var check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Additional Regex for stricter control (no double dots, etc.)
        // This ensures we don't pass loose strings that PHP's filter might allow
        return preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,63}$/ix', $email) === 1;
    }

    /**
     * Check if the email is role-based (e.g., admin@, support@)
     */
    public function isRoleBased(string $email): bool {
        $localPart = strtolower(explode('@', $email)[0]);

        // check exact match
        if (in_array($localPart, self::ROLE_BASED_PREFIXES)) {
            return true;
        }

        // check if starts with a role (e.g., support-team@)
        foreach (self::ROLE_BASED_PREFIXES as $role) {
            if (str_starts_with($localPart, $role . '.') || str_starts_with($localPart, $role . '-') || str_starts_with($localPart, $role . '_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract domain from email
     */
    public function getDomain(string $email): ?string {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : null;
    }
}