<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/GameModel.php';

function showUserProfile(PDO $pdo): void {
    $userId = (int)($_GET['id'] ?? 0);

    if (!$userId) {
        redirect('/not-found');
        return;
    }

    $currentUser = currentUser();

    if (!$currentUser) {
        redirect('/login');
        return;
    } else if (($currentUser['id'] !== $userId && !isAdmin())) {
        http_response_code(403);
        redirect('/forbidden');
        return;
    }


    $user = getUserById($pdo, $userId);
    
    if (!$user) {
        redirect('/not-found');
        return;
    }
    
    // Get user statistics
    $userStats = getUserStatistics($pdo, $userId);
    
    // Update pagination state
    $paginationKey = 'user_' . $userId;
    updatePaginationState($paginationKey, ['page', 'sort']);
    
    // Get games pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 8;
    $sort = $_GET['sort'] ?? 'date_desc';
    
    $gamesResult = getGamesByUserPaginated($pdo, $userId, $page, $perPage, $sort);
    
    $content = renderView('user/profile', [
        'user' => $user,
        'stats' => $userStats,
        'games' => $gamesResult['games'],
        'currentPage' => $gamesResult['current_page'],
        'totalPages' => $gamesResult['pages'],
        'total' => $gamesResult['total'],
        'currentSort' => $sort,
        'currentUserId' => $currentUser['id'] ?? null
    ]);
    $title = 'Profil: ' . htmlspecialchars($user['username']);
    require __DIR__ . '/../views/layout.php';
}

