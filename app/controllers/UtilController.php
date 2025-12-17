<?php

function showForbiddenPage(PDO $pdo): void {
    $content = renderView('forbidden');
    $title = 'Přístup odepřen';
    require __DIR__ . '/../views/layout.php';
}

function showNotFoundPage(PDO $pdo): void {
    $content = renderView('not-found');
    $title = 'Stránka nenalezena';
    require __DIR__ . '/../views/layout.php';
}