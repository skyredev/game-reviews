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

function getRatingText(float $rating): string {
    $texts = [
        8.5 => 'Velmi pozitivní',
        7.0 => 'Pozitivní',
        5.0 => 'Smíšené',
        2.5 => 'Negativní',
        0.0 => 'Velmi negativní'
    ];

    foreach ($texts as $threshold => $text) {
        if ($rating >= $threshold) {
            return $text;
        }
    }

    return 'Velmi negativní';
}