<?php

/**
 * Authentication and authorization helper functions
 * 
 * @package App\Includes\Services
 */

require_once __DIR__ . '/redirect.php';

/**
 * Require user to be a guest (not logged in)
 * Redirects to home if logged in
 * 
 * @return void
 */
function requireGuest(): void {
    if (!empty($_SESSION['user'])) {
        redirect('/');
    }
}

/**
 * Require user to be logged in
 * Redirects to login if not logged in or if user is blocked
 * 
 * @return void
 */
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

/**
 * Require user to be an admin
 * Redirects to login if not logged in, or to forbidden if not admin
 * 
 * @return void
 */
function requireAdmin(): void {
    requireUser();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        redirect('/forbidden');
    }
}

/**
 * Check if user is currently logged in
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Check if current user is an admin
 * 
 * @return bool True if user is logged in and is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

/**
 * Get current logged in user data
 * Returns null if not logged in or if user is blocked
 * 
 * @return array|null User data or null
 */
function currentUser(): ?array {
    $user = $_SESSION['user'] ?? null;
    
    // If user is blocked, clear session
    if ($user && !empty($user['is_blocked'])) {
        unset($_SESSION['user']);
        return null;
    }
    
    return $user;
}