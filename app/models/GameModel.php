<?php

function getAllGames(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM games");
    return $stmt->fetchAll();
}