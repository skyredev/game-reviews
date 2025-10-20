<?php

function requireGuest(): void {
    if (!empty($_SESSION['user'])) {
        header('Location: ' . APP_BASE . '/');
        exit;
    }
}
function requireUser(): void {
    if (empty($_SESSION['user'])) {
        header('Location: ' . APP_BASE . '/login');
        exit;
    }
}

function requireAdmin(): void {
    requireUser();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        header('Location: ' . APP_BASE . '/forbidden');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}