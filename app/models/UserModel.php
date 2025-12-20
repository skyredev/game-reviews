<?php

/**
 * Get users with pagination
 * 
 * @param PDO $pdo Database connection
 * @param int $page Current page (1-based)
 * @param int $perPage Number of users per page
 * @return array ['users' => array, 'total' => int, 'pages' => int, 'current_page' => int]
 */
function getUsersPaginated(PDO $pdo, int $page = 1, int $perPage = 10): array {
    $page = max(1, $page);
    $perPage = max(1, min(50, $perPage));
    $offset = ($page - 1) * $perPage;
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
    $countStmt->execute();
    $total = (int)$countStmt->fetch()['total'];
    $totalPages = max(1, (int)ceil($total / $perPage));
    
    // Get users for current page
    $stmt = $pdo->prepare("
        SELECT id, username, email, role, avatar_path, created_at, is_blocked
        FROM users
        ORDER BY username
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return [
        'users' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage
    ];
}

/**
 * Get user information by ID
 *
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById(PDO $pdo, int $userId, bool $includePassHash = false): ?array {
    $stmt = $pdo->prepare("
        SELECT id, username, email, role, avatar_path, created_at, is_blocked" . ($includePassHash ? ", pass_hash" : "") . "
        FROM users
        WHERE id = :id
    ");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Get user information by identifier (username or email)
 * 
 * @param PDO $pdo Database connection
 * @param string $identifier Username or email
 * @param bool $includePassHash Whether to include password hash in result
 * @return array|null User data or null if not found
 */
function getUserByIdentifier(PDO $pdo, string $identifier, bool $includePassHash = false): ?array {
    $stmt = $pdo->prepare("
        SELECT id, username, email, role, avatar_path, created_at, is_blocked" . ($includePassHash ? ", pass_hash" : "") . "
        FROM users
        WHERE username = :identifier OR email = :identifier
    ");
    $stmt->execute(['identifier' => $identifier]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Block or unblock a user
 *
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param bool $block True to block, false to unblock
 * @return bool True on success, false on failure
 */
function setUserBlockStatus(PDO $pdo, int $userId, bool $block): bool {
    $stmt = $pdo->prepare("
        UPDATE users
        SET is_blocked = :is_blocked
        WHERE id = :id
    ");
    return $stmt->execute([
        'is_blocked' => $block ? 1 : 0,
        'id' => $userId
    ]);
}

/**
 * Checks if identifier matches existing username or email
 *
 * @param PDO $pdo Database connection
 * @param string $username Username to check
 * @param string $email Email to check
 * @return string|null Returns 'username' if username taken, 'email' if email taken, or null if not found
 */
/**
 * Check if username or email already exists
 * 
 * @param PDO $pdo Database connection
 * @param string $username Username to check
 * @param string $email Email to check
 * @return array Array with 'username' and 'email' keys (true if exists)
 */
function checkExistingUser(PDO $pdo, string $username, string $email): array {
    $stmt = $pdo->prepare("
        SELECT username, email 
        FROM users 
        WHERE username = :username OR email = :email
        LIMIT 1
    ");
    $stmt->execute([
        'username' => $username,
        'email' => $email
    ]);

    $row = $stmt->fetch();

    return [
        'username' => $row && $row['username'] === $username,
        'email'    => $row && $row['email'] === $email,
    ];
}

/**
 * Get user statistics
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array User statistics
 */
function getUserStatistics(PDO $pdo, int $userId): array {
    // Total games by user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM games WHERE author_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $totalGames = (int)$stmt->fetch()['total'];
    
    // Games by status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM games 
        WHERE author_id = :user_id
        GROUP BY status
    ");
    $stmt->execute(['user_id' => $userId]);
    $gamesByStatus = [];
    foreach ($stmt->fetchAll() as $row) {
        $gamesByStatus[$row['status']] = (int)$row['count'];
    }
    
    // Total reviews by user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $totalReviews = (int)$stmt->fetch()['total'];
    
    return [
        'games' => [
            'total' => $totalGames,
            'active' => $gamesByStatus['active'] ?? 0,
            'pending' => $gamesByStatus['pending'] ?? 0,
            'rejected' => $gamesByStatus['rejected'] ?? 0
        ],
        'reviews' => [
            'total' => $totalReviews
        ]
    ];
}

/**
 * Toggle user admin status
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return bool Success
 */
function doToggleUserAdmin(PDO $pdo, int $userId): bool {
    // Get current role
    $user = getUserById($pdo, $userId);
    if (!$user) {
        return false;
    }
    
    $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
    
    $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    return $stmt->execute([
        'role' => $newRole,
        'id' => $userId
    ]);
}

/**
 * Toggle user block status
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return bool Success
 */
function doToggleUserBlock(PDO $pdo, int $userId): bool {
    $user = getUserById($pdo, $userId);
    if (!$user) {
        return false;
    }
    
    $newBlockStatus = !$user['is_blocked'];
    return setUserBlockStatus($pdo, $userId, $newBlockStatus);
}

/**
 * Get admin statistics
 * 
 * @param PDO $pdo Database connection
 * @return array Statistics data
 */
function getAdminStatistics(PDO $pdo): array {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = (int)$stmt->fetch()['total'];
    
    // Total admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = (int)$stmt->fetch()['total'];
    
    // Total games
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM games");
    $totalGames = (int)$stmt->fetch()['total'];
    
    // Games by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM games 
        GROUP BY status
    ");
    $gamesByStatus = [];
    foreach ($stmt->fetchAll() as $row) {
        $gamesByStatus[$row['status']] = (int)$row['count'];
    }
    
    // Average rating (from reviews)
    $stmt = $pdo->query("
        SELECT AVG(rating) as avg_rating 
        FROM reviews
    ");
    $avgRating = $stmt->fetch()['avg_rating'];
    $avgRating = $avgRating ? round((float)$avgRating, 1) : 0;
    
    // Total reviews
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
    $totalReviews = (int)$stmt->fetch()['total'];

    return [
        'users' => [
            'total' => $totalUsers,
            'admins' => $totalAdmins,
            'regular' => $totalUsers - $totalAdmins,
        ],
        'games' => [
            'total' => $totalGames,
            'active' => $gamesByStatus['active'] ?? 0,
            'pending' => $gamesByStatus['pending'] ?? 0,
        ],
        'reviews' => [
            'total' => $totalReviews,
            'avg_rating' => $avgRating
        ]
    ];
}


