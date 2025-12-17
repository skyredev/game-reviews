<?php

require_once __DIR__ . '/../includes/services/helpers.php';

/**
 * Upload and process avatar image
 * 
 * @param array|null $avatarFile Uploaded file from $_FILES
 * @return string|null Path to avatar or null if no file
 */
function uploadAvatar(?array $avatarFile): ?string {
    if (!$avatarFile || !isset($avatarFile['error']) || $avatarFile['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = BASE_DIR . '/public/uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('avatar_', true) . '.webp';
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($avatarFile['tmp_name'], $targetPath)) {
        return null;
    }

    // Resize to 200x200
    $resizedPath = $uploadDir . 'thumb_' . $filename;
    if (imageResizeWebp($targetPath, 200, 200, $resizedPath)) {
        // Delete original, keep only thumbnail
        unlink($targetPath);
        return '/uploads/avatars/thumb_' . $filename;
    }

    return '/uploads/avatars/' . $filename;
}

function createUser(PDO $pdo, string $username, string $name, string $email, string $password, string $role = 'user', ?array $avatarFile = null): array {
    $errors = [];

    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['username'] === $username) {
            $errors['username'][] = 'Uživatelské jméno je již obsazeno.';
        }
        if ($existing['email'] === $email) {
            $errors['email'][] = 'Tento e-mail je již zaregistrován.';
        }
        return [$errors, null];
    }

    // Upload avatar if provided
    $avatarPath = uploadAvatar($avatarFile);

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, name, email, pass_hash, role, avatar_path)
        VALUES (:username, :name, :email, :password, :role, :avatar_path)
    ");
    $stmt->execute([
        'username' => $username,
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role,
        'avatar_path' => $avatarPath
    ]);

    $id = $pdo->lastInsertId();

    $user = [
        'id' => (int)$id,
        'username' => $username,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'avatar_path' => $avatarPath
    ];

    return [[], $user];
}

function doLogin($pdo, $identifier, $password): array {
    $errors = [];

    $stmt = $pdo->prepare("SELECT id, username, email, pass_hash, role FROM users WHERE username = :identifier OR email = :identifier");
    $stmt->execute(['identifier' => $identifier]);
    $existing = $stmt->fetch();

    if (!$existing) {
        $errors['identifier'][] = 'Takový uživatel nebyl nalezen.';
        return [$errors, 'NotFound'];
    }

    if (!password_verify($password, $existing['pass_hash'])) {
        $errors['password'][] = 'Zadané heslo je špatně, zkuste znovu.';
        return [$errors, 'WrongPassword'];
    }

    $user = [
        'id' => (int)$existing['id'],
        'username' => $existing['username'],
        'email' => $existing['email'],
        'role' => $existing['role']
    ];

    return [[], $user];
}

