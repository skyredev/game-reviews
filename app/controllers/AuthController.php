<?php

require_once __DIR__ . '/../models/AuthModel.php';

function showRegisterPage(): void {
    $errors = getFlash('auth_errors') ?? [];
    $old = getFlash('auth_old') ?? [];

    $content = renderView('auth/register', [
        'errors' => $errors,
        'old' => $old
    ]);
    $title = 'Registrace';
    require __DIR__ . '/../views/layout.php';
}

function registerUser(PDO $pdo): void {
    [$dbErrors, $user] = createUser(
        $pdo,
        $_POST['username'],
        $_POST['name'],
        $_POST['email'],
        $_POST['password'],
        'user',
        $_FILES['avatar'] ?? null
    );

    if (!empty($dbErrors)) {
        redirectWithErrors('/register', $dbErrors, [
            'username' => $_POST['username'],
            'name' => $_POST['name'],
            'email' => $_POST['email']
        ], 'auth');
    }

    // Login user
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ];

    redirect('/');
}

function showLoginPage(): void {
    $errors = getFlash('auth_errors') ?? [];
    $old = getFlash('auth_old') ?? [];
    $errorStatusCode = getFlash('auth_error_status');

    $errorStatusMessages = [
        'NotFound' => 'Uživatel s tímto jménem nebo e-mailem nebyl nalezen.',
        'WrongPassword' => 'Zadané heslo není správné.',
        'AccountLocked' => 'Váš účet byl zablokován. Kontaktujte administrátora.',
        'AccountInactive' => 'Váš účet ještě nebyl aktivován.'
    ];

    $errorStatus = $errorStatusCode ? ($errorStatusMessages[$errorStatusCode] ?? 'Došlo k chybě při přihlašování.') : null;

    $content = renderView('auth/login', [
        'errors' => $errors,
        'old' => $old,
        'errorStatus' => $errorStatus
    ]);
    $title = 'Login';
    require __DIR__ . '/../views/layout.php';
}

function loginUser(PDO $pdo): void {
    [$dbErrors, $user] = doLogin($pdo, $_POST['identifier'], $_POST['password']);

    if (!empty($dbErrors)) {
        $_SESSION['auth_error_status'] = $user;
        redirectWithErrors('/login', $dbErrors, ['identifier' => $_POST['identifier']], 'auth');
    }

    // Login user
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ];

    redirect('/');
}

function logoutUser(PDO $pdo): void {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    redirect('/');
}
