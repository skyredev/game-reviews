<?php

function createUser(PDO $pdo, string $username, string $email, string $password): bool {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    if ($stmt->fetch()) {
        return false;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, pass_hash) VALUES (:username, :email, :password)");
    return $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword]);
}
