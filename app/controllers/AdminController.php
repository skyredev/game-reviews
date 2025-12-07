<?php

function showAdminPage(PDO $pdo): void {
    requireAdmin();

    $content = renderView('admin/index');
    $title = 'Admin';
    require __DIR__ . '/../views/layout.php';
}