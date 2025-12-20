<?php

function getTagsByType(PDO $pdo, string $type): array {
    $stmt = $pdo->prepare("SELECT id, name FROM tags WHERE type = :type ORDER BY name");
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll();
}
