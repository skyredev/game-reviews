<?php

function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

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

function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}


