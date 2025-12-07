<?php

require_once __DIR__ . '/../includes/services/media.php';

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
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(DISTINCT r.id) AS review_count,
            GROUP_CONCAT(DISTINCT CASE WHEN t.type = 'genre' THEN t.name END ORDER BY t.name SEPARATOR '|') AS genres
        FROM games g
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN game_tags gt ON g.id = gt.game_id
        LEFT JOIN tags t ON gt.tag_id = t.id
        GROUP BY g.id, g.title, g.description, g.release_year, g.covers, g.developer, g.publisher
        ORDER BY average_rating DESC
    ");
    
    $games = $stmt->fetchAll();
    
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
function saveGame(PDO $pdo, array $data, int $userId, array $file): ?int
{
    $status = isAdmin() ? 'active' : 'pending';

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
    if (!$gameId) return null;

    $uploadBase = PUBLIC_DIR . '/uploads/games';

    if (!is_dir($uploadBase)) {
        mkdir($uploadBase, 0777, true);
    }

    $slug = date('Y-m-d') . '_' . $gameId;
    $dir = $uploadBase . '/' . $slug;

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $full = $dir . '/cover_full.webp';
    $thumb_vertical = $dir . '/cover_thumb_vertical.webp';
    $thumb_horizontal = $dir . '/cover_thumb_horizontal.webp';

    imageResizeWebp($file['tmp_name'], 600, 900, $full);
    imageResizeWebp($file['tmp_name'], 200, 300, $thumb_vertical);
    imageResizeWebp($file['tmp_name'], 300, 170, $thumb_horizontal);

    $fullUrl = str_replace(PUBLIC_DIR, 'public', $full);
    $thumbVerticalUrl = str_replace(PUBLIC_DIR, 'public', $thumb_vertical);
    $thumbHorizontalUrl = str_replace(PUBLIC_DIR, 'public', $thumb_horizontal);

    $coversJson = json_encode([
        'full' => $fullUrl,
        'thumb_vertical' => $thumbVerticalUrl,
        'thumb_horizontal' => $thumbHorizontalUrl
    ], JSON_UNESCAPED_SLASHES);

    $stmt = $pdo->prepare("
        UPDATE games
        SET covers = :covers
        WHERE id = :id
    ");

    $stmt->execute([
        'covers' => $coversJson,
        'id' => $gameId
    ]);
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