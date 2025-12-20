<?php

require_once __DIR__ . '/../includes/services/helpers.php';

/**
 * Upload and process game cover images
 * 
 * @param array $coverFile Uploaded file from $_FILES
 * @param int $gameId Game ID for folder naming
 * @return array|null Array with cover URLs or null on failure
 */
function uploadGameCovers(array $coverFile, int $gameId): ?array {
    if (!isset($coverFile['tmp_name']) || !file_exists($coverFile['tmp_name'])) {
        return null;
    }

    $uploadBase = PUBLIC_DIR . '/uploads/games';
    if (!is_dir($uploadBase)) {
        mkdir($uploadBase, 0755, true);
    }

    $slug = date('Y-m-d') . '_' . $gameId;
    $dir = $uploadBase . '/' . $slug;

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $full = $dir . '/cover_full.webp';
    $thumbVertical = $dir . '/cover_thumb_vertical.webp';
    $thumbHorizontal = $dir . '/cover_thumb_horizontal.webp';

    // Create different sizes
    if (!imageResizeWebp($coverFile['tmp_name'], 600, 900, $full)) {
        return null;
    }
    imageResizeWebp($coverFile['tmp_name'], 200, 300, $thumbVertical);
    imageResizeWebp($coverFile['tmp_name'], 300, 170, $thumbHorizontal);

    // Return paths with public/ prefix
    return [
        'full' => str_replace(PUBLIC_DIR, 'public', $full),
        'thumb_vertical' => str_replace(PUBLIC_DIR, 'public', $thumbVertical),
        'thumb_horizontal' => str_replace(PUBLIC_DIR, 'public', $thumbHorizontal)
    ];
}

/**
 * Get top rated games (only active status)
 * 
 * @param PDO $pdo Database connection
 * @param int $limit Number of games to return
 * @return array Top rated games
 */
function getTopGames(PDO $pdo, int $limit = 10): array {
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.covers,
            g.developer,
            g.publisher,
            g.created_at,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres
        FROM games g
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        WHERE g.status = 'active'
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher, g.created_at
        ORDER BY average_rating DESC, review_count DESC
        LIMIT :limit
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return processGamesData($stmt->fetchAll());
}

/**
 * Get recently added games (excluding games from exclude list)
 * 
 * @param PDO $pdo Database connection
 * @param array $excludeIds Array of game IDs to exclude
 * @param int $limit Number of games to return
 * @return array Recently added games
 */
function getRecentGames(PDO $pdo, int $limit = 10): array {
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.covers,
            g.developer,
            g.publisher,
            g.created_at,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres
        FROM games g
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        WHERE g.status = 'active'
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher, g.created_at
        ORDER BY g.created_at DESC
        LIMIT :limit
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return processGamesData($stmt->fetchAll());
}

/**
 * Process games data (decode JSON fields)
 * 
 * @param array $games Raw games data from database
 * @return array Processed games data
 */
/**
 * Process games data from database - parse JSON fields and format data
 * 
 * @param array $games Raw games data from database
 * @return array Processed games data
 */
function processGamesData(array $games): array {
    foreach ($games as &$game) {
        if ($game['covers']) {
            $game['covers'] = json_decode($game['covers'], true);
        } else {
            $game['covers'] = [];
        }
        
        if ($game['genres']) {
            $game['genres'] = explode('|', $game['genres']);
        } else {
            $game['genres'] = [];
        }
        
        // Use approved_at if exists, otherwise use created_at for display date
        if (isset($game['approved_at']) && $game['approved_at']) {
            $game['display_date'] = $game['approved_at'];
        } else {
            $game['display_date'] = $game['created_at'] ?? null;
        }
    }
    
    return $games;
}

/**
 * Get games with pagination by status
 * 
 * @param PDO $pdo Database connection
 * @param string $status Game status ('active', 'pending', 'rejected')
 * @param int $page Current page (1-based)
 * @param int $perPage Number of games per page
 * @return array ['games' => array, 'total' => int, 'pages' => int, 'current_page' => int]
 */
function getGamesPaginated(PDO $pdo, string $status = 'active', int $page = 1, int $perPage = 12, string $sort = 'rating_desc'): array {
    $page = max(1, $page);
    $perPage = max(1, min(50, $perPage));
    $offset = ($page - 1) * $perPage;
    
    // Validate status
    $allowedStatuses = ['active', 'pending', 'rejected'];
    if (!in_array($status, $allowedStatuses)) {
        $status = 'active';
    }
    
    // Validate and set sort order
    $allowedSorts = [
        'name_asc' => 'ORDER BY g.title ASC',
        'name_desc' => 'ORDER BY g.title DESC',
        'rating_asc' => 'ORDER BY average_rating ASC, COALESCE(approved_at, g.created_at) DESC',
        'rating_desc' => 'ORDER BY average_rating DESC, COALESCE(approved_at, g.created_at) DESC',
        'date_asc' => 'ORDER BY COALESCE(approved_at, g.created_at) ASC',
        'date_desc' => 'ORDER BY COALESCE(approved_at, g.created_at) DESC'
    ];
    
    if (!isset($allowedSorts[$sort])) {
        $sort = 'rating_desc'; // Default
    }
    
    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT g.id) as total
        FROM games g
        WHERE g.status = :status
    ");
    $countStmt->execute(['status' => $status]);
    $total = (int)$countStmt->fetch()['total'];
    $totalPages = max(1, (int)ceil($total / $perPage));
    
    // Determine order by
    $orderBy = $allowedSorts[$sort];
    
    // Get games for current page
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.covers,
            g.developer,
            g.publisher,
            g.created_at,
            g.author_id,
            g.status,
            u.username as author_username,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres,
            (SELECT gm.created_at FROM game_moderations gm WHERE gm.game_id = g.id AND gm.type = 'approve' ORDER BY gm.created_at DESC LIMIT 1) as approved_at
        FROM games g
        LEFT JOIN users u ON g.author_id = u.id
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        WHERE g.status = :status
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher, g.created_at, g.author_id, g.status, u.username
        {$orderBy}
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return [
        'games' => processGamesData($stmt->fetchAll()),
        'total' => $total,
        'pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage
    ];
}

/**
 * Update game status
 * 
 * @param PDO $pdo Database connection
 * @param int $gameId Game ID
 * @param string $status New status ('active', 'pending', 'rejected')
 * @return bool Success
 */
function updateGameStatus(PDO $pdo, int $gameId, string $status): bool {
    $stmt = $pdo->prepare("UPDATE games SET status = :status WHERE id = :id");
    return $stmt->execute([
        'status' => $status,
        'id' => $gameId
    ]);
}

/**
 * Save game review (approve or reject)
 * 
 * @param PDO $pdo Database connection
 * @param int $gameId Game ID
 * @param int $reviewedBy User ID who reviewed
 * @param string $type Review type ('approve' or 'reject')
 * @param string|null $reason Rejection reason (only for reject)
 * @return bool Success
 */
function saveGameReview(PDO $pdo, int $gameId, int $reviewedBy, string $type, ?string $reason = null): bool {
    // Delete existing review of this type if any
    $deleteStmt = $pdo->prepare("DELETE FROM game_moderations WHERE game_id = :game_id AND type = :type");
    $deleteStmt->execute(['game_id' => $gameId, 'type' => $type]);
    
    // Insert new review
    $stmt = $pdo->prepare("
        INSERT INTO game_moderations (game_id, reviewed_by, type, reason)
        VALUES (:game_id, :reviewed_by, :type, :reason)
    ");
    return $stmt->execute([
        'game_id' => $gameId,
        'reviewed_by' => $reviewedBy,
        'type' => $type,
        'reason' => $reason
    ]);
}

/**
 * Get game moderation info (approve or reject)
 * 
 * @param PDO $pdo Database connection
 * @param int $gameId Game ID
 * @param string|null $type Review type ('approve' or 'reject'), null for any
 * @return array|null Review info with username or null
 */
function getGameModeration(PDO $pdo, int $gameId, ?string $type = null): ?array {
    $sql = "
        SELECT gm.*, u.username as reviewed_by_username
        FROM game_moderations gm
        JOIN users u ON gm.reviewed_by = u.id
        WHERE gm.game_id = :game_id
    ";
    
    $params = ['game_id' => $gameId];
    
    if ($type !== null) {
        $sql .= " AND gm.type = :type";
        $params['type'] = $type;
    }
    
    $sql .= " ORDER BY gm.created_at DESC LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Get games by user with pagination
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param int $page Current page (1-based)
 * @param int $perPage Number of games per page
 * @param string $sort Sort order
 * @return array ['games' => array, 'total' => int, 'pages' => int, 'current_page' => int]
 */
function getGamesByUserPaginated(PDO $pdo, int $userId, int $page = 1, int $perPage = 12, string $sort = 'date_desc'): array {
    $page = max(1, $page);
    $perPage = max(1, min(50, $perPage));
    $offset = ($page - 1) * $perPage;

    // Validate and set sort order
    $allowedSorts = [
        'name_asc' => 'ORDER BY g.title ASC',
        'name_desc' => 'ORDER BY g.title DESC',
        'rating_asc' => 'ORDER BY average_rating ASC, COALESCE(approved_at, g.created_at) DESC',
        'rating_desc' => 'ORDER BY average_rating DESC, COALESCE(approved_at, g.created_at) DESC',
        'date_asc' => 'ORDER BY COALESCE(approved_at, g.created_at) ASC',
        'date_desc' => 'ORDER BY COALESCE(approved_at, g.created_at) DESC',
        'status_asc' => 'ORDER BY g.status ASC, COALESCE(approved_at, g.created_at) DESC',
        'status_desc' => 'ORDER BY g.status DESC, COALESCE(approved_at, g.created_at) DESC'
    ];
    
    if (!isset($allowedSorts[$sort])) {
        $sort = 'date_desc'; // Default
    }
    
    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM games g
        WHERE g.author_id = :user_id
    ");
    $countStmt->execute(['user_id' => $userId]);
    $total = (int)$countStmt->fetch()['total'];
    $totalPages = max(1, (int)ceil($total / $perPage));
    
    $orderBy = $allowedSorts[$sort];
    
    // Get games for current page
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.covers,
            g.developer,
            g.publisher,
            g.created_at,
            g.author_id,
            g.status,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres,
            (SELECT gm.created_at FROM game_moderations gm WHERE gm.game_id = g.id AND gm.type = 'approve' ORDER BY gm.created_at DESC LIMIT 1) as approved_at,
            (SELECT gm.created_at FROM game_moderations gm WHERE gm.game_id = g.id AND gm.type = 'reject' ORDER BY gm.created_at DESC LIMIT 1) as rejected_at
        FROM games g
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        WHERE g.author_id = :user_id
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher, g.created_at, g.author_id, g.status
        {$orderBy}
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return [
        'games' => processGamesData($stmt->fetchAll()),
        'total' => $total,
        'pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage
    ];
}

/**
 * Get game by ID with all details
 * Does not filter by status - access control should be done in controller
 * 
 * @param PDO $pdo Database connection
 * @param int $gameId Game ID
 * @return array|null Game data or null if not found
 */
function getGameById(PDO $pdo, int $gameId): ?array {
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.covers,
            g.developer,
            g.publisher,
            g.author_id,
            g.status,
            g.created_at,
            u.username as author_username,
            u.avatar_path as author_avatar,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'platform' THEN t.name END ORDER BY t.name SEPARATOR '|') AS platforms
        FROM games g
        LEFT JOIN users u ON g.author_id = u.id
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        WHERE g.id = :id
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher, g.author_id, g.status, g.created_at, u.username, u.avatar_path
    ");
    
    $stmt->execute(['id' => $gameId]);
    $game = $stmt->fetch();
    
    if (!$game) {
        return null;
    }
    
    // Process JSON fields
    if ($game['covers']) {
        $game['covers'] = json_decode($game['covers'], true);
    } else {
        $game['covers'] = [];
    }
    
    if ($game['genres']) {
        $game['genres'] = explode('|', $game['genres']);
    } else {
        $game['genres'] = [];
    }
    
    if ($game['platforms']) {
        $game['platforms'] = explode('|', $game['platforms']);
    } else {
        $game['platforms'] = [];
    }
    
    return $game;
}

/**
 * Save game to database
 * 
 * @param PDO $pdo Database connection
 * @param array $data Game data (title, description, release_year, publisher, developer, genres, platforms)
 * @param int $userId Author user ID
 * @param array|null $coverFile Uploaded cover image file from $_FILES
 * @return int|null Game ID on success, null on failure
 */
function saveGame(PDO $pdo, array $data, int $userId, ?array $coverFile = null): ?int
{
    $status = isAdmin() ? 'active' : 'pending';

    // Insert game first
    $stmt = $pdo->prepare("
        INSERT INTO games (title, description, release_year, author_id, developer, publisher, status)
        VALUES (:title, :desc, :year, :author, :developer, :publisher, :status)
    ");
    
    $stmt->execute([
        'title' => $data['title'],
        'desc' => $data['description'],
        'year' => $data['release_year'],
        'developer' => $data['developer'],
        'publisher' => $data['publisher'],
        'author' => $userId,
        'status' => $status
    ]);

    $gameId = (int)$pdo->lastInsertId();
    if (!$gameId) {
        return null;
    }

    // Upload and update covers if file provided
    if ($coverFile && isset($coverFile['tmp_name'])) {
        $coversUrls = uploadGameCovers($coverFile, $gameId);
        if ($coversUrls) {
            $stmt = $pdo->prepare("UPDATE games SET covers = :covers WHERE id = :id");
            $stmt->execute([
                'covers' => json_encode($coversUrls, JSON_UNESCAPED_SLASHES),
                'id' => $gameId
            ]);
        }
    }

    // Add tags (genres and platforms)
    $insert = $pdo->prepare("
        INSERT INTO game_tags (game_id, tag_id) VALUES (:gid, :tid)
    ");

    foreach ($data['genres'] as $genreId) {
        $insert->execute(['gid' => $gameId, 'tid' => $genreId]);
    }

    foreach ($data['platforms'] as $platformId) {
        $insert->execute(['gid' => $gameId, 'tid' => $platformId]);
    }

    return $gameId;
}