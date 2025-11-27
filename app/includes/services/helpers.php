<?php

function renderView(string $view, array $data = []): string {
    extract($data);
    ob_start();
    require BASE_DIR . '/app/views/' . $view . '.php';
    return ob_get_clean();
}

function esc(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
