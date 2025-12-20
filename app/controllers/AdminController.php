<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/GameModel.php';

function showAdminPage(PDO $pdo): void {
    // Update pagination state (both users_page and games_page)
    updatePaginationState('admin', ['users_page', 'games_page']);
    
    // Get statistics
    $stats = getAdminStatistics($pdo);
    
    // Get users pagination
    $usersPage = max(1, (int)($_GET['users_page'] ?? 1));
    $usersResult = getUsersPaginated($pdo, $usersPage, 10);
    
    // Get pending games pagination
    $gamesPage = max(1, (int)($_GET['games_page'] ?? 1));
    $gamesResult = getGamesPaginated($pdo, 'pending', $gamesPage, 12,'date_desc');

    $currentUser = currentUser();
    
    $content = renderView('admin/index', [
        'stats' => $stats,
        'users' => $usersResult['users'],
        'usersTotal' => $usersResult['total'],
        'usersPages' => $usersResult['pages'],
        'usersCurrentPage' => $usersResult['current_page'],
        'games' => $gamesResult['games'],
        'gamesTotal' => $gamesResult['total'],
        'gamesPages' => $gamesResult['pages'],
        'gamesCurrentPage' => $gamesResult['current_page'],
        'currentUserId' => $currentUser['id'] ?? null
    ]);
    $title = 'Admin';
    require __DIR__ . '/../views/layout.php';
}

function approveGame(PDO $pdo): void {
    header('Content-Type: application/json');
    
    $gameId = (int)($_POST['game_id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    
    if (!$gameId) {
        echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
        exit;
    }
    
    require_once __DIR__ . '/../models/GameModel.php';
    
    $success = updateGameStatus($pdo, $gameId, 'active');
    
    if ($success) {
        // Save approval info
        saveGameReview($pdo, $gameId, $userId, 'approve', null);
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
        saveGameReview($pdo, $gameId, $userId, 'reject', $reason ?: null);
        echo json_encode(['success' => true, 'message' => 'Hra byla zamítnuta.', 'status' => 'rejected']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nepodařilo se zamítnout hru.']);
    }
    exit;
}

function toggleUserAdmin(PDO $pdo): void {
    header('Content-Type: application/json');
    
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $currentUserId = $_SESSION['user']['id'];
    
    if (!$targetUserId || $targetUserId === $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
        exit;
    }
    
    require_once __DIR__ . '/../models/UserModel.php';
    
    $success = doToggleUserAdmin($pdo, $targetUserId);
    
    if ($success) {
        $user = getUserById($pdo, $targetUserId);
        echo json_encode([
            'success' => true, 
            'message' => $user['role'] === 'admin' ? 'Uživateli byl udělen status admin.' : 'Uživateli byl odebrán status admin.',
            'role' => $user['role']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nepodařilo se změnit status uživatele.']);
    }
    exit;
}

function toggleUserBlock(PDO $pdo): void {
    header('Content-Type: application/json');
    
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $currentUserId = $_SESSION['user']['id'];
    
    if (!$targetUserId || $targetUserId === $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
        exit;
    }
    
    require_once __DIR__ . '/../models/UserModel.php';
    
    $success = doToggleUserBlock($pdo, $targetUserId);
    
    if ($success) {
        $user = getUserById($pdo, $targetUserId);
        echo json_encode([
            'success' => true, 
            'message' => $user['is_blocked'] ? 'Uživatel byl zablokován.' : 'Uživatel byl odblokován.',
            'is_blocked' => (bool)$user['is_blocked']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nepodařilo se změnit status blokování.']);
    }
    exit;
}