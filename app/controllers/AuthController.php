<?php

/**
 * Authentication controller - handles login, registration, logout
 * 
 * @package App\Controllers\AuthController
 */

require_once __DIR__ . '/../models/AuthModel.php';

/**
 * Show registration form page
 * 
 * @return void
 */
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

/**
 * Handle user registration
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function registerUser(PDO $pdo): void {
    [$dbErrors, $user] = createUser(
        $pdo,
        $_POST['username'],
        $_POST['email'],
        $_POST['password'],
        'user',
        $_FILES['avatar'] ?? null
    );

    if (!empty($dbErrors)) {
        redirectWithErrors('/register', $dbErrors, [
            'username' => $_POST['username'],
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

/**
 * Show login form page
 * 
 * @return void
 */
function showLoginPage(): void {
    $errors = getFlash('auth_errors') ?? [];
    $old = getFlash('auth_old') ?? [];
    $errorStatusCode = getFlash('auth_error_status');

    $errorStatusMessages = [
        'NotFound' => 'Uživatel s tímto jménem nebo e-mailem nebyl nalezen.',
        'WrongPassword' => 'Zadané heslo není správné.',
        'AccountLocked' => 'Váš účet byl zablokován. Kontaktujte administrátora.',
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

/**
 * Handle user login
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function loginUser(PDO $pdo): void {
    [$dbErrors, $user] = doLogin($pdo, $_POST['identifier'], $_POST['password']);

    // If $user is a string, it's an error status code
    if (is_string($user)) {
        $_SESSION['auth_error_status'] = $user;
        redirectWithErrors('/login', $dbErrors, ['identifier' => $_POST['identifier']], 'auth');
        return;
    }

    // If there are errors and user is not an array, redirect
    if (!empty($dbErrors) || !is_array($user)) {
        redirectWithErrors('/login', $dbErrors, ['identifier' => $_POST['identifier']], 'auth');
        return;
    }

    // Login user
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'is_blocked' => (bool)($user['is_blocked'] ?? false)
    ];

    redirect('/');
}

/**
 * Handle user logout
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function logoutUser(PDO $pdo): void {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    redirect('/');
}
