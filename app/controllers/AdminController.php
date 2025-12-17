<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/GameModel.php';

function showAdminPage(PDO $pdo): void {
    // Get statistics
    $stats = getAdminStatistics($pdo);
    
    // Get users pagination
    $usersPage = max(1, (int)($_GET['users_page'] ?? 1));
    $usersResult = getUsersPaginated($pdo, $usersPage, 10);
    
    // Get pending games pagination
    $gamesPage = max(1, (int)($_GET['games_page'] ?? 1));
    $gamesResult = getGamesPaginated($pdo, 'pending', $gamesPage, 12);

    $content = renderView('admin/index', [
        'stats' => $stats,
        'users' => $usersResult['users'],
        'usersTotal' => $usersResult['total'],
        'usersPages' => $usersResult['pages'],
        'usersCurrentPage' => $usersResult['current_page'],
        'games' => $gamesResult['games'],
        'gamesTotal' => $gamesResult['total'],
        'gamesPages' => $gamesResult['pages'],
        'gamesCurrentPage' => $gamesResult['current_page']
    ]);
    $title = 'Admin';
    require __DIR__ . '/../views/layout.php';
}

function approveGame(PDO $pdo): void {
    header('Content-Type: application/json');
    
    $gameId = (int)($_POST['game_id'] ?? 0);
    
    if (!$gameId) {
        echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
        exit;
    }
    
    $success = updateGameStatus($pdo, $gameId, 'active');
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Hra byla schválena.', 'status' => 'active']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nepodařilo se schválit hru.']);
    }
    exit;
}

function rejectGame(PDO $pdo): void {
    header('Content-Type: application/json');
    
    $gameId = (int)($_POST['game_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $userId = $_SESSION['user']['id'];
    
    if (!$gameId) {
        echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
        exit;
    }
    
    require_once __DIR__ . '/../models/GameModel.php';
    
    $success = updateGameStatus($pdo, $gameId, 'rejected');
    
    if ($success) {
        // Save rejection info
        saveGameRejection($pdo, $gameId, $userId, $reason ?: null);
        echo json_encode(['success' => true, 'message' => 'Hra byla zamítnuta.', 'status' => 'rejected']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nepodařilo se zamítnout hru.']);
    }
    exit;
}