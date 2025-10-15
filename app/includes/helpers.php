<?php

function renderView(string $view, array $data = []): string {
    extract($data);
    ob_start();
    require __DIR__ . '/../views/' . $view . '.php';
    return ob_get_clean();
}

function esc(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function validateRegister(string $userName, string $email, string $password){

}
