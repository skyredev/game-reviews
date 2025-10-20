<?php
function validateRegister(string $userName, string $name, string $email, string $password): array {
    $errors = [];

    $usernameErrors = getUserNameErrors($userName);
    if (!empty($usernameErrors)) {
        $errors['username'] = $usernameErrors;
    }

    $nameErrors = getNameErrors($name);
    if (!empty($nameErrors)) {
        $errors['name'] = $nameErrors;
    }

    $emailErrors = getEmailErrors($email);
    if (!empty($emailErrors)) {
        $errors['email'] = $emailErrors;
    }

    $passwordErrors = getPasswordErrors($password);
    if (!empty($passwordErrors)) {
        $errors['password'] = $passwordErrors;
    }

    return $errors;
}

function getUserNameErrors(string $userName): array {
    $errors = [];

    if (empty($userName)) {
        $errors[] = 'Uživatelské jméno nesmí být prázdné.';
    } elseif (!preg_match('/^(?![._-])[A-Za-z0-9._-]+(?<![._-])$/', $userName)) {
        $errors[] = 'Uživatelské jméno nesmí začínat ani končit tečkou, pomlčkou nebo podtržítkem a nesmí obsahovat jiné specialní znaky.';
    } elseif (strlen($userName) < 3) {
        $errors[] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
    }

    return $errors;
}

function getNameErrors(string $name): array {
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Jméno nesmí být prázdné.';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Jméno musí mít alespoň 2 znaky.';
    }

    return $errors;
}

function getEmailErrors(string $email): array {
    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email nesmí být prázdný.';
        return $errors;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Neplatný formát emailu.';
        return $errors;
    }

    [$emailPart] = explode('@', $email);
    if (strlen($emailPart) < 4) {
        $errors[] = 'Část před @ musí mít alespoň 4 znaky.';
    }

    return $errors;
}

function getPasswordErrors(string $password): array {
    $errors = [];

    if (empty($password)) {
        $errors[] = 'Heslo nesmí být prázdné.';
        return $errors;
    }
    if(strlen($password) < 8) $errors[] = 'Heslo musí mít alespoň 8 znaků.';
    if(!preg_match('/[A-Z]/', $password)) $errors[] = 'Musí obsahovat alespoň jedno velké písmeno.';
    if(!preg_match('/\d/', $password)) $errors[] = 'Musí obsahovat alespoň jedno číslo.';
    if(!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Musí obsahovat alespoň jeden speciální znak.';

    return $errors;
}
