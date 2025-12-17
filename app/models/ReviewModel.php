<?php

/**
 * Get reviews for a game
 */
function getGameReviews(PDO $pdo, int $gameId, ?int $currentUserId = null): array {
    if ($currentUserId) {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.user_id,
                r.rating,
                r.comment,
                r.created_at,
                r.updated_at,
                u.username,
                u.avatar_path,
                (
                    SELECT COUNT(*) 
                    FROM review_reactions rr 
                    WHERE rr.review_id = r.id AND rr.reaction = 'like'
                ) as likes_count,
                (
                    SELECT COUNT(*) 
                    FROM review_reactions rr 
                    WHERE rr.review_id = r.id AND rr.reaction = 'dislike'
                ) as dislikes_count,
                (
                    SELECT reaction 
                    FROM review_reactions rr 
                    WHERE rr.review_id = r.id AND rr.user_id = :current_user_id
                    LIMIT 1
                ) as user_reaction
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.game_id = :game_id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute(['game_id' => $gameId, 'current_user_id' => $currentUserId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.user_id,
                r.rating,
                r.comment,
                r.created_at,
                r.updated_at,
                u.username,
                u.avatar_path,
                (
                    SELECT COUNT(*) 
                    FROM review_reactions rr 
                    WHERE rr.review_id = r.id AND rr.reaction = 'like'
                ) as likes_count,
                (
                    SELECT COUNT(*) 
                    FROM review_reactions rr 
                    WHERE rr.review_id = r.id AND rr.reaction = 'dislike'
                ) as dislikes_count,
                NULL as user_reaction
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.game_id = :game_id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute(['game_id' => $gameId]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Get user's review for a game
 */
function getUserReview(PDO $pdo, int $gameId, int $userId): ?array {
    $stmt = $pdo->prepare("
        SELECT r.*
        FROM reviews r
        WHERE r.game_id = :game_id AND r.user_id = :user_id
        LIMIT 1
    ");
    
    $stmt->execute(['game_id' => $gameId, 'user_id' => $userId]);
    return $stmt->fetch() ?: null;
}

/**
 * Create a review
 */
function createReview(PDO $pdo, int $gameId, int $userId, int $rating, string $comment): ?int {
    // Check if review already exists
    $existing = getUserReview($pdo, $gameId, $userId);
    if ($existing) {
        return null; // User already has a review
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO reviews (game_id, user_id, rating, comment)
        VALUES (:game_id, :user_id, :rating, :comment)
    ");
    
    $stmt->execute([
        'game_id' => $gameId,
        'user_id' => $userId,
        'rating' => $rating,
        'comment' => $comment
    ]);
    
    return (int)$pdo->lastInsertId();
}

/**
 * Update a review
 */
function updateReview(PDO $pdo, int $reviewId, int $userId, int $rating, string $comment): bool {
    $stmt = $pdo->prepare("
        UPDATE reviews 
        SET rating = :rating, comment = :comment, updated_at = CURRENT_TIMESTAMP
        WHERE id = :id AND user_id = :user_id
    ");
    
    $stmt->execute([
        'id' => $reviewId,
        'user_id' => $userId,
        'rating' => $rating,
        'comment' => $comment
    ]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Delete a review
 */
function doDeleteReview(PDO $pdo, int $reviewId, int $userId, bool $isAdmin = false): bool {
    if ($isAdmin) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id");
        $stmt->execute(['id' => $reviewId]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $reviewId, 'user_id' => $userId]);
    }
    
    return $stmt->rowCount() > 0;
}

/**
 * Toggle review reaction (like/dislike)
 */
function toggleReviewReaction(PDO $pdo, int $reviewId, int $userId, string $reaction): array {
    // Check existing reaction
    $stmt = $pdo->prepare("
        SELECT reaction 
        FROM review_reactions 
        WHERE review_id = :review_id AND user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute(['review_id' => $reviewId, 'user_id' => $userId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['reaction'] === $reaction) {
            // Remove reaction (toggle off)
            $stmt = $pdo->prepare("
                DELETE FROM review_reactions 
                WHERE review_id = :review_id AND user_id = :user_id
            ");
            $stmt->execute(['review_id' => $reviewId, 'user_id' => $userId]);
            return ['action' => 'removed', 'reaction' => null];
        } else {
            // Change reaction
            $stmt = $pdo->prepare("
                UPDATE review_reactions 
                SET reaction = :reaction 
                WHERE review_id = :review_id AND user_id = :user_id
            ");
            $stmt->execute(['review_id' => $reviewId, 'user_id' => $userId, 'reaction' => $reaction]);
            return ['action' => 'changed', 'reaction' => $reaction];
        }
    } else {
        // Add new reaction
        $stmt = $pdo->prepare("
            INSERT INTO review_reactions (review_id, user_id, reaction)
            VALUES (:review_id, :user_id, :reaction)
        ");
        $stmt->execute(['review_id' => $reviewId, 'user_id' => $userId, 'reaction' => $reaction]);
        return ['action' => 'added', 'reaction' => $reaction];
    }
}

/**
 * Get reaction counts for a review
 */
function getReviewReactionCounts(PDO $pdo, int $reviewId): array {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN reaction = 'like' THEN 1 END) as likes,
            COUNT(CASE WHEN reaction = 'dislike' THEN 1 END) as dislikes
        FROM review_reactions
        WHERE review_id = :review_id
    ");
    $stmt->execute(['review_id' => $reviewId]);
    $result = $stmt->fetch();
    return [
        'likes' => (int)$result['likes'],
        'dislikes' => (int)$result['dislikes']
    ];
}

