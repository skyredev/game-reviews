<?php

/**
 * Tags model - database operations for tags
 * 
 * @package App\Models\TagsModel
 */

/**
 * Get tags by type (genre, platform)
 * 
 * @param PDO $pdo Database connection
 * @param string $type Tag type ('genre', 'platform')
 * @return array Array of tags with id and name
 */
function getTagsByType(PDO $pdo, string $type): array {
    $stmt = $pdo->prepare("SELECT id, name FROM tags WHERE type = :type ORDER BY name");
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll();
}