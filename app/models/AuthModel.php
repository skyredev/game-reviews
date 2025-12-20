<?php

require_once __DIR__ . '/../includes/services/helpers.php';
require_once __DIR__ . '/UserModel.php';

/**
 * Upload and process avatar image
 * 
 * @param array|null $avatarFile Uploaded file from $_FILES
 * @return string|null Path to avatar or null if no file
 */
function uploadAvatar(?array $avatarFile): ?string {
    if (!$avatarFile || !isset($avatarFile['tmp_name']) || !file_exists($avatarFile['tmp_name'])) {
        return null;
    }

    $uploadBase = PUBLIC_DIR . '/uploads/avatars';
    if (!is_dir($uploadBase)) {
        mkdir($uploadBase, 0755, true);
    }

    $filename = uniqid('avatar_', true) . '.webp';
    $targetPath = $uploadBase . '/' . $filename;

    // Resize to 200x200 and save directly
    if (!imageResizeWebp($avatarFile['tmp_name'], 200, 200, $targetPath)) {
        return null;
    }

    // Return path with public/ prefix
    return str_replace(PUBLIC_DIR, 'public', $targetPath);
}

/**
 * Create a new user account
 * 
 * @param PDO $pdo Database connection
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Plain text password (will be hashed)
 * @param string $role User role (default: 'user')
 * @param array|null $avatarFile Uploaded avatar file from $_FILES
 * @return array Array with [errors, user] - errors is empty array on success, user is null on error
 */
function createUser(PDO $pdo, string $username, string $email, string $password, string $role = 'user', ?array $avatarFile = null): array {
    $errors = [];

    $existing = checkExistingUser($pdo, $username, $email);

    if ($existing['username']) {
        $errors['username'][] = 'Uživatelské jméno je již obsazeno.';
    }

    if ($existing['email']) {
        $errors['email'][] = 'Tento e-mail je již zaregistrován.';
    }

    if ($errors) {
        return [$errors, null];
    }

    // Upload avatar if provided
    $avatarPath = uploadAvatar($avatarFile);

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, pass_hash, role, avatar_path)
        VALUES (:username, :email, :password, :role, :avatar_path)
    ");
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role,
        'avatar_path' => $avatarPath
    ]);

    $id = $pdo->lastInsertId();

    $user = [
        'id' => (int)$id,
        'username' => $username,
        'email' => $email,
        'role' => $role,
        'avatar_path' => $avatarPath
    ];

    return [[], $user];
}

/**
 * Authenticate user login
 * 
 * @param PDO $pdo Database connection
 * @param string $identifier Username or email
 * @param string $password Plain text password
 * @return array Array with [errors, user] - errors is empty array on success, user can be array or error status string ('NotFound', 'AccountLocked', 'WrongPassword')
 */
function doLogin($pdo, $identifier, $password): array {
    $errors = [];

    $existing = getUserByIdentifier($pdo, $identifier, true);

    if (!$existing) {
        $errors['identifier'][] = 'Takový uživatel nebyl nalezen.';
        return [$errors, 'NotFound'];
    }

    if ($existing['is_blocked']) {
        return [$errors, 'AccountLocked'];
    }

    if (!password_verify($password, $existing['pass_hash'])) {
        $errors['password'][] = 'Zadané heslo je špatně, zkuste znovu.';
        return [$errors, 'WrongPassword'];
    }

    $user = [
        'id' => (int)$existing['id'],
        'username' => $existing['username'],
        'email' => $existing['email'],
        'role' => $existing['role'],
        'is_blocked' => (bool)$existing['is_blocked']
    ];

    return [[], $user];
}

