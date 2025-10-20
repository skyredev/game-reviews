<?php

function createUser(PDO $pdo, string $username, string $email, string $password, string $role = 'user'): array {
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

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, pass_hash, role)
        VALUES (:username, :email, :password, :role)
    ");
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role
    ]);

    $id = $pdo->lastInsertId();

    $user = [
        'id' => (int)$id,
        'username' => $username,
        'email' => $email,
        'role' => $role
    ];

    return [[], $user];
}

