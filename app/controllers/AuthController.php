<?php

require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../includes/services/validation.php';


function showRegisterPage(): void {
    requireGuest();

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
    requireGuest();

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

function showLoginPage(): void {
    requireGuest();

    $errors = $_SESSION['authErrors'] ?? [];
    $old = $_SESSION['authOld'] ?? [];
    $errorStatus = $_SESSION['errorStatus'] ?? null;

    unset($_SESSION['authErrors'], $_SESSION['authOld'], $_SESSION['errorStatus']);

    $content = renderView('login', [
        'errors' => $errors,
        'old' => $old,
        'errorStatus' => $errorStatus
    ]);
    $title = 'Login';
    require __DIR__ . '/../views/layout.php';
}

function loginUser(PDO $pdo): bool {
    requireGuest();

    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    $errors = validateLogin($identifier);

    if(!empty($errors)) {
        $_SESSION['authOld'] = compact('identifier');
        $_SESSION['authErrors'] = $errors;
        header('Location: ' . APP_BASE . '/login');
        exit;
    }

    [$dbErrors, $user] = doLogin($pdo, $identifier, $password);

    if (!empty($dbErrors)) {
        $_SESSION['authOld'] = compact('identifier');
        $_SESSION['authErrors'] = $dbErrors;
        $_SESSION['errorStatus'] = $user;
        header('Location: ' . APP_BASE . '/login');
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

function logoutUser(PDO $pdo): bool {
    requireUser();

    unset($_SESSION['user']);

    session_regenerate_id(true);

    header('Location: ' . APP_BASE . '/');
    exit;
}
