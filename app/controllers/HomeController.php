<?php

require_once __DIR__ . '/../models/GameModel.php';

function showHomePage(PDO $pdo): void {
    $games = getAllGames($pdo);

    $content = renderView('home/index', ['games' => $games]);
    $title = 'Hlavní stránka';
    require __DIR__ . '/../views/layout.php';
}