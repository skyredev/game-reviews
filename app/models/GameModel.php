<?php

function getAllGames(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.cover_image,
            COALESCE(AVG(r.rating), 0) AS average_rating
        FROM games g
        LEFT JOIN reviews r ON g.id = r.game_id
        GROUP BY g.id, g.title, g.description, g.release_year, g.cover_image
        ORDER BY average_rating DESC
    ");
    return $stmt->fetchAll();
}
