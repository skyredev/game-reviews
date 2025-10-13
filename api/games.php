<?php
require_once __DIR__ . '/../app/includes/config.php';
header('Content-Type: application/json; charset=utf-8');

/** @var PDO $pdo */
global $pdo;

$stmt = $pdo->query("SELECT id, title, description FROM games ORDER BY created_at DESC");
echo json_encode($stmt->fetchAll());

