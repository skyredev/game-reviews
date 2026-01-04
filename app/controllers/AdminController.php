<?php

/**
 * Admin controller - handles admin panel and moderation actions
 * 
 * @package App\Controllers\AdminController
 */

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/GameModel.php';

/**
 * Show admin panel page with statistics and users list
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showAdminPage(PDO $pdo): void {
    // Update pagination state
    updatePaginationState('admin', ['page']);
    
    // Get statistics
    $stats = getAdminStatistics($pdo);
    
    // Get users pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $result = getUsersPaginated($pdo, $page, 10);

    $currentUser = currentUser();
    
    $content = renderView('admin/index', [
        'stats' => $stats,
        'users' => $result['users'],
        'total' => $result['total'],
        'totalPages' => $result['pages'],
        'currentPage' => $result['current_page'],
        'currentUserId' => $currentUser['id'] ?? null
    ]);
    $title = 'Admin';
    require __DIR__ . '/../views/layout.php';
}

/**
 * Show pending games page
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showPendingGamesPage(PDO $pdo): void {
    // Update pagination state
    updatePaginationState('admin_pending', ['page']);
    
    // Get pending games pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $result = getGamesPaginated($pdo, 'pending', $page, 12, 'date_desc');
    
    $content = renderView('admin/pending-games', [
        'games' => $result['games'],
        'total' => $result['total'],
        'totalPages' => $result['pages'],
        'currentPage' => $result['current_page']
    ]);
    $title = 'Hry na schválení';
    require __DIR__ . '/../views/layout.php';
}

/**
 * Approve game via AJAX
 * Returns JSON response
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
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

/**
 * Reject game via AJAX
 * Returns JSON response
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
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

/**
 * Toggle user admin status via AJAX
 * Returns JSON response
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
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

/**
 * Toggle user block status via AJAX
 * Returns JSON response
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
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