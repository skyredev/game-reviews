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
        SELECT id, username, name, email, role, avatar_path, created_at
        FROM users
        ORDER BY created_at DESC
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
    while ($row = $stmt->fetch()) {
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


