<?php

require_once __DIR__ . '/../includes/services/media.php';

function getAllGames(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT 
            g.id,
            g.title,
            g.description,
            g.release_year,
            g.cover_full,
            g.cover_thumb,
            g.developer,
            g.publisher,
            COALESCE(AVG(r.rating), 0) AS average_rating
        FROM games g
        LEFT JOIN reviews r ON g.id = r.game_id
        GROUP BY g.id, g.title, g.description, g.release_year, g.cover_full, g.cover_thumb, g.developer, g.publisher
        ORDER BY average_rating DESC
    ");
    return $stmt->fetchAll();
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

    $slug = makeFolderSlug($data['title']);
    $dir = $uploadBase . '/' . $gameId . '_' . $slug;

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $full = $dir . '/cover_full.webp';
    $thumb = $dir . '/cover_thumb.webp';

    imageResizeWebp($file['tmp_name'], 600, 900, $full);
    imageResizeWebp($file['tmp_name'], 200, 300, $thumb);

    // 4) Uložit cesty
    $stmt = $pdo->prepare("
        UPDATE games
        SET cover_full = :full, cover_thumb = :thumb
        WHERE id = :id
    ");

    $fullUrl = str_replace(PUBLIC_DIR, 'public', $full);
    $thumbUrl = str_replace(PUBLIC_DIR, 'public', $thumb);


    $stmt->execute([
        'full' => $fullUrl,
        'thumb' => $thumbUrl,
        'id' => $gameId
    ]);

    // 5) Tags — žánry i platformy
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