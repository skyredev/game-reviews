<?php

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to (without APP_BASE prefix)
 */
function redirect(string $url): void {
    $location = APP_BASE . $url;
    header('Location: ' . $location);
    exit;
}

/**
 * Exclude sensitive fields from input data
 * 
 * @param array $input Input data
 * @return array Input data without sensitive fields
 */
function excludeSensitiveFields(array $input): array {
    $excludedFields = ['password', 'password_confirmation', 'csrf_token', 'password_confirm'];
    foreach ($excludedFields as $field) {
        unset($input[$field]);
    }
    return $input;
}

/**
 * Post-Redirect-Get pattern with errors and old input
 * 
 * @param string $url URL to redirect to (without APP_BASE prefix)
 * @param array $errors Validation errors
 * @param array $oldInput Old input data to repopulate form
 * @param string $sessionKey Session key prefix for storing errors and old input
 */
function redirectWithErrors(string $url, array $errors, array $oldInput = [], string $sessionKey = 'form'): void {
    $_SESSION[$sessionKey . '_errors'] = $errors;
    $_SESSION[$sessionKey . '_old'] = excludeSensitiveFields($oldInput);
    redirect($url);
}

/**
 * Post-Redirect-Get pattern with success message
 * 
 * @param string $url URL to redirect to (without APP_BASE prefix)
 * @param string $message Success message
 * @param string $sessionKey Session key for storing message
 */
function redirectWithSuccess(string $url, string $message, string $sessionKey = 'success'): void {
    $_SESSION[$sessionKey] = $message;
    redirect($url);
}

/**
 * Get and clear flash data from session
 * 
 * @param string $key Session key
 * @return mixed Flash data or null
 */
function getFlash(string $key): mixed
{
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $value;
}


