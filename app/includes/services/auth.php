<?php

function requireGuest(): void {
    if (!empty($_SESSION['user'])) {
        header('Location: ' . APP_BASE . '/');
        exit;
    }
}
function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        header('Location: ' . APP_BASE . '/login');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo "Přístup odepřen.";
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}