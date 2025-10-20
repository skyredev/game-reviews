<?php

function showAdminPage(PDO $pdo): void {
    requireAdmin();

    $content = renderView('admin');
    $title = 'Admin';
    require __DIR__ . '/../views/layout.php';
}