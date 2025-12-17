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

    // Return URLs relative to public directory
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
        'rating_asc' => 'ORDER BY average_rating ASC, g.created_at DESC',
        'rating_desc' => 'ORDER BY average_rating DESC, g.created_at DESC',
        'date_asc' => 'ORDER BY g.created_at ASC',
        'date_desc' => 'ORDER BY g.created_at DESC'
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
            u.username as author_username,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres
        FROM games g
        LEFT JOIN users u ON g.author_id = u.id
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        WHERE g.status = :status
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher, g.created_at, g.author_id, u.username
        {$orderBy}
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
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

function getAllGames(PDO $pdo): array {
    $stmt = $pdo->query("
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
        ORDER BY average_rating DESC
    ");
    
    return processGamesData($stmt->fetchAll());
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
 * Save game rejection info
 * 
 * @param PDO $pdo Database connection
 * @param int $gameId Game ID
 * @param int $rejectedBy User ID who rejected
 * @param string|null $reason Rejection reason
 * @return bool Success
 */
function saveGameRejection(PDO $pdo, int $gameId, int $rejectedBy, ?string $reason = null): bool {
    // Delete existing rejection if any
    $deleteStmt = $pdo->prepare("DELETE FROM game_rejects WHERE game_id = :game_id");
    $deleteStmt->execute(['game_id' => $gameId]);
    
    // Insert new rejection
    $stmt = $pdo->prepare("
        INSERT INTO game_rejects (game_id, rejected_by, reason)
        VALUES (:game_id, :rejected_by, :reason)
    ");
    return $stmt->execute([
        'game_id' => $gameId,
        'rejected_by' => $rejectedBy,
        'reason' => $reason
    ]);
}

/**
 * Get game rejection info
 * 
 * @param PDO $pdo Database connection
 * @param int $gameId Game ID
 * @return array|null Rejection info with username or null
 */
function getGameRejection(PDO $pdo, int $gameId): ?array {
    $stmt = $pdo->prepare("
        SELECT gr.*, u.username as rejected_by_username
        FROM game_rejects gr
        JOIN users u ON gr.rejected_by = u.id
        WHERE gr.game_id = :game_id
        ORDER BY gr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute(['game_id' => $gameId]);
    $result = $stmt->fetch();
    return $result ?: null;
}
/**
 * Get game by ID with all details
 * Does not filter by status - access control should be done in controller
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