<?php

/**
 * CSRF protection functions
 * 
 * @package App\Includes\Services
 */

/**
 * Generate or retrieve CSRF token from session
 * 
 * @return string CSRF token
 */
function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * Removes token from session after validation
 * 
 * @param string $token Token to validate
 * @return bool True if token is valid
 */
function validateCsrfToken(string $token): bool {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    $isValid = hash_equals($_SESSION['csrf_token'], $token);

    if ($isValid) {
        unset($_SESSION['csrf_token']);
    }

    return $isValid;
}

/**
 * Generate HTML hidden input field with CSRF token
 * 
 * @return string HTML input field
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}


