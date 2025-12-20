<?php

/**
 * Games controller - handles game-related pages and actions
 * 
 * @package App\Controllers
 */

require_once __DIR__ . '/../models/GameModel.php';
require_once __DIR__ . '/../models/TagsModel.php';
require_once __DIR__ . '/../models/ReviewModel.php';

/**
 * Show games list page with pagination and sorting
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showGamesPage(PDO $pdo): void {
    // Update pagination state
    updatePaginationState('games', ['page', 'sort']);
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 12;
    $sort = $_GET['sort'] ?? 'rating_desc';
    
    $result = getGamesPaginated($pdo, 'active', $page, $perPage, $sort);
    
    $content = renderView('games/index', [
        'games' => $result['games'],
        'currentPage' => $result['current_page'],
        'totalPages' => $result['pages'],
        'total' => $result['total'],
        'currentSort' => $sort
    ]);
    $title = 'Hry';
    require __DIR__ . '/../views/layout.php';
}

/**
 * Show game creation form page
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showGamesCreatePage(PDO $pdo): void {
    $errors = getFlash('game_errors') ?? [];
    $old = getFlash('game_old') ?? [];

    $genres = getTagsByType($pdo, 'genre');
    $platforms = getTagsByType($pdo, 'platform');

    $content = renderView('games/create', [
        'errors' => $errors,
        'old' => $old,
        'genres' => $genres,
        'platforms' => $platforms
    ]);

    $title = 'Přidat hru';
    require __DIR__ . '/../views/layout.php';
}

/**
 * Handle game submission (create new game)
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function submitGame(PDO $pdo): void {
    $user = $_SESSION['user'];

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'release_year' => (int)($_POST['release_year'] ?? 0),
        'description' => trim($_POST['description'] ?? ''),
        'genres' => $_POST['genres'] ?? [],
        'platforms' => $_POST['platforms'] ?? [],
        'publisher' => trim($_POST['publisher'] ?? ''),
        'developer' => trim($_POST['developer'] ?? '')
    ];

    $gameId = saveGame($pdo, $data, $user['id'], $_FILES['cover_image'] ?? null);

    if (!$gameId) {
        redirectWithErrors('/games/create', ['general' => ['Nepodařilo se uložit hru.']], $data, 'game');
    }
    
    // Use buildPaginationUrl to preserve pagination state
    require_once __DIR__ . '/../includes/services/pagination.php';
    $redirectUrl = buildPaginationUrl('/games', 'games');
    redirectWithSuccess($redirectUrl, 'Hra byla úspěšně přidána.');
}

/**
 * Show single game page with reviews
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function showGamePage(PDO $pdo): void {
    $gameId = (int)($_GET['id'] ?? 0);
    
    if (!$gameId) {
        http_response_code(404);
        require __DIR__ . '/../views/not-found.php';
        return;
    }
    
    // Get game without status filtering - we'll check access after
    $game = getGameById($pdo, $gameId);
    
    // Check if game exists
    if (!$game) {
        redirect('/not-found');
        return;
    }
    
    $isAdmin = isAdmin();
    $currentUser = currentUser();
    $currentUserId = $currentUser['id'] ?? null;
    
    // Access control:
    // - Regular users can only see active games (unless it's their own pending/rejected)
    // - Admins can see all games
    if (!$isAdmin) {
        if ($game['status'] !== 'active' && $game['author_id'] != $currentUserId) {
            // Not admin, not active, and not the author - forbidden
            redirect('/forbidden');
            return;
        }
    }
    
    // Get all reviews (with reactions if logged in) - only for active games
    $allReviews = [];
    $reviews = [];
    $userReviewData = null;
    
    if ($game['status'] === 'active') {
        $allReviews = getGameReviews($pdo, $gameId, $currentUserId);
        
        // Separate user's review from others
        foreach ($allReviews as $review) {
            if ($currentUserId && $review['user_id'] == $currentUserId) {
                $userReviewData = $review;
            } else {
                $reviews[] = $review;
            }
        }
    }
    
    $errors = getFlash('review_errors') ?? [];
    $old = getFlash('review_old') ?? [];
    
    // Get moderation info if game is rejected
    $rejectionInfo = null;
    if ($game['status'] === 'rejected') {
        require_once __DIR__ . '/../models/GameModel.php';
        $rejectionInfo = getGameModeration($pdo, $gameId, 'reject');
    }
    
    $content = renderView('games/show', [
        'game' => $game,
        'userReview' => $userReviewData,
        'reviews' => $reviews,
        'errors' => $errors,
        'old' => $old,
        'rejectionInfo' => $rejectionInfo
    ]);
    $title = $game['title'];
    $fullWidth = true;
    require __DIR__ . '/../views/layout.php';
}

/**
 * Handle review submission (create or update review)
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function submitReview(PDO $pdo): void {
    $gameId = (int)($_POST['game_id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    
    if (!$gameId || $rating < 1 || $rating > 10) {
        redirectWithErrors('/game?id=' . $gameId, ['general' => ['Neplatná data.']], [], 'review');
    }
    
    // Check if updating existing review
    $existingReview = getUserReview($pdo, $gameId, $userId);
    
    if ($existingReview) {
        // Update existing review
        $success = updateReview($pdo, $existingReview['id'], $userId, $rating, $comment);
        if (!$success) {
            redirectWithErrors('/game?id=' . $gameId, ['general' => ['Nepodařilo se aktualizovat recenzi.']], ['comment' => $comment, 'rating' => $rating], 'review');
        }
    } else {
        // Create new review
        $reviewId = createReview($pdo, $gameId, $userId, $rating, $comment);
        if (!$reviewId) {
            redirectWithErrors('/game?id=' . $gameId, ['general' => ['Již máte recenzi na tuto hru.']], ['comment' => $comment, 'rating' => $rating], 'review');
        }
    }
    
    redirect('/game?id=' . $gameId);
}

/**
 * Delete review via AJAX
 * Returns JSON response
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function deleteReview(PDO $pdo): void {
    header('Content-Type: application/json');
    
    $reviewId = (int)($_POST['review_id'] ?? 0);
    $gameId = (int)($_POST['game_id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $isAdmin = isAdmin();
    
    if (!$reviewId || !$gameId) {
        echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
        exit;
    }
    
    $success = doDeleteReview($pdo, $reviewId, $userId, $isAdmin);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Recenze byla smazána.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nepodařilo se smazat recenzi.']);
    }
    exit;
}

/**
 * Toggle review reaction (like/dislike) via AJAX
 * Returns JSON response
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function toggleReaction(PDO $pdo): void {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $reviewId = (int)($_POST['review_id'] ?? 0);
    $reaction = $_POST['reaction'] ?? '';
    $userId = $_SESSION['user']['id'];
    
    if (!$reviewId || !in_array($reaction, ['like', 'dislike'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        return;
    }
    
    $result = toggleReviewReaction($pdo, $reviewId, $userId, $reaction);
    $counts = getReviewReactionCounts($pdo, $reviewId);
    
    echo json_encode([
        'success' => true,
        'action' => $result['action'],
        'reaction' => $result['reaction'],
        'counts' => $counts
    ]);
}
