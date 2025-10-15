<?php

require_once __DIR__ . '/../models/AuthModel.php';

function showRegisterPage(): void {
    $content = renderView('register');
    $title = 'Registrace';
    require __DIR__ . '/../views/layout.php';
}

function registerUser(PDO $pdo): bool {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        return false;
    }
    else {
        $success = createUser($pdo, $username, $email, $password);
            $errors = ['username' => "USERNAME CANT BE EMPTY", 'email' => 'email taken', 'password' => "password error example"];
            $content = renderView('register', ['errors' => $errors]);
            $title = 'Registrace';
            require __DIR__ . '/../views/layout.php';
        }
        return true;

}