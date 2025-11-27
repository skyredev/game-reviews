<?php

require_once __DIR__ . '/../models/GameModel.php';
require_once __DIR__ . '/../models/TagsModel.php';
require_once __DIR__ . '/../includes/services/validation.php';

function showGamesPage(PDO $pdo): void {
    requireUser();

    $errors = $_SESSION['gameErrors'] ?? [];
    $old = $_SESSION['gameOld'] ?? [];
    unset($_SESSION['gameErrors'], $_SESSION['gameOld']);

    $genres = getTagsByType($pdo, 'genre');
    $platforms = getTagsByType($pdo, 'platform');

    $content = renderView('games', [
        'errors' => $errors,
        'old' => $old,
        'genres' => $genres,
        'platforms' => $platforms
    ]);

    $title = 'Přidat hru';
    require __DIR__ . '/../views/layout.php';
}

function submitGame(PDO $pdo): void {
    requireUser();

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'release_year' => (int)($_POST['release_year'] ?? 0),
        'description' => trim($_POST['description'] ?? ''),
        'genres' => $_POST['genres'] ?? [],
        'platforms' => $_POST['platforms'] ?? [],
        'publisher' => trim($_POST['publisher'] ?? ''),
        'developer' => trim($_POST['developer'] ?? '')
    ];

    $errors = validateGame($data, $_FILES['cover_image'] ?? null);

    if (!empty($errors)) {
        $_SESSION['gameErrors'] = $errors;
        $_SESSION['gameOld'] = $data;
        header('Location: ' . APP_BASE . '/games');
        exit;
    }

    $user = $_SESSION['user'];
    $gameId = saveGame($pdo, $data, $user['id'], $_FILES['cover_image']);

    if (!$gameId) {
        $_SESSION['gameErrors'] = ['general' => ['Nepodařilo se uložit hru.']];
        $_SESSION['gameOld'] = $data;
        header('Location: ' . APP_BASE . '/games/add');
        exit;
    }

    header('Location: ' . APP_BASE . '/games');
    exit;
}
