<?php

/**
 * Home controller - handles homepage
 * 
 * @package App\Controllers
 */

require_once __DIR__ . '/../models/GameModel.php';

/**
 * Show homepage with top games and recent games
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
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