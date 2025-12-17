<?php

require_once __DIR__ . '/../models/GameModel.php';

function showHomePage(PDO $pdo): void {
    // Get top 10 games by rating
    $topGames = getTopGames($pdo);
    
    // Get recent games
    $recentGames = getRecentGames($pdo);

    $content = renderView('home/index', [
        'topGames' => $topGames,
        'recentGames' => $recentGames
    ]);
    $title = 'Hlavní stránka';
    require __DIR__ . '/../views/layout.php';
}