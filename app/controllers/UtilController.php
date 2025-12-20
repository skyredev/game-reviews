<?php

/**
 * Utility controller - handles error pages
 * 
 * @package App\Controllers
 */

/**
 * Show forbidden (403) error page
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showForbiddenPage(PDO $pdo): void {
    $content = renderView('forbidden');
    $title = 'Přístup odepřen';
    require __DIR__ . '/../views/layout.php';
}

/**
 * Show not found (404) error page
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showNotFoundPage(PDO $pdo): void {
    $content = renderView('not-found');
    $title = 'Stránka nenalezena';
    require __DIR__ . '/../views/layout.php';
}