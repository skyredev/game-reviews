<?php

require_once __DIR__ . '/redirect.php';

function requireGuest(): void {
    if (!empty($_SESSION['user'])) {
        redirect('/');
    }
}

function requireUser(): void {
    if (empty($_SESSION['user'])) {
        redirect('/login');
    }
    
    // Check if user is blocked
    if (!empty($_SESSION['user']['is_blocked'])) {
        unset($_SESSION['user']);
        redirect('/login');
    }
}

function requireAdmin(): void {
    requireUser();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        redirect('/forbidden');
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

function currentUser(): ?array {
    $user = $_SESSION['user'] ?? null;
    
    // If user is blocked, clear session
    if ($user && !empty($user['is_blocked'])) {
        unset($_SESSION['user']);
        return null;
    }
    
    return $user;
}