<?php

function showForbiddenPage(PDO $pdo): void {
    $content = renderView('forbidden');
    $title = 'Přístup odepřen';
    require __DIR__ . '/../views/layout.php';
}