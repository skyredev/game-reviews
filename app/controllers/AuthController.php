<?php

require_once __DIR__ . '/../models/AuthModel.php';

function showRegisterPage(): void {
    $errors = $_SESSION['authErrors'] ?? [];
    $old = $_SESSION['authOld'] ?? [];

    unset($_SESSION['authErrors'], $_SESSION['authOld']);

    $content = renderView('register', [
        'errors' => $errors,
        'old' => $old
    ]);
    $title = 'Registrace';
    require __DIR__ . '/../views/layout.php';
}

function registerUser(PDO $pdo): bool {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $errors = validateRegister($username, $name, $email, $password);

    if(!empty($errors)) {
        $_SESSION['authOld'] = compact('username', 'name', 'email');
        $_SESSION['authErrors'] = $errors;
        header('Location: ' . APP_BASE . '/register');
        exit;
    }

    [$dbErrors, $user] = createUser($pdo, $username, $email, $password);

    if (!empty($dbErrors)) {
        $_SESSION['authOld'] = compact('username', 'name', 'email');
        $_SESSION['authErrors'] = $dbErrors;
        header('Location: ' . APP_BASE . '/register');
        exit;
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ];

    header('Location: ' . APP_BASE . '/');
    exit;

}