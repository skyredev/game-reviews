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

function validateLogin(string $identifier): array {
    $errors = [];

    $identifierErrors = getIdentifierErrors($identifier);
    if (!empty($identifierErrors)) {
        $errors['identifier'] = $identifierErrors;
    }

    return $errors;
}

function getIdentifierErrors(string $identifier): array {
    if(!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $errors = getUserNameErrors($identifier);
    } else {
        $errors = getEmailErrors($identifier);
    }
    if(!empty($errors)) return $errors;

    return [];
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

function validateGame(array $data, ?array $file): array
{
    $errors = [];

    if ($data['title'] === '') {
        $errors['title'][] = 'Název nesmí být prázdný.';
    }

    if ($data['description'] === '') {
        $errors['description'][] = 'Popis nesmí být prázdný.';
    }

    if ($data['publisher'] === '') {
        $errors['publisher'][] = 'Vydavatel nesmí být prázdný.';
    }

    if ($data['developer'] === '') {
        $errors['developer'][] = 'Vývojář nesmí být prázdný.';
    }

    if (empty($data['genres'])) {
        $errors['genres'][] = 'Vyberte alespoň jeden žánr.';
    }

    if (empty($data['platforms'])) {
        $errors['platforms'][] = 'Vyberte alespoň jednu platformu.';
    }

    if ($data['release_year'] < 1980 || $data['release_year'] > (int)date('Y')) {
        $errors['release_year'][] = 'Rok vydání není platný.';
    }

    if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
        $errors['cover_image'][] = 'Obrázek je povinný.';
        return $errors;
    }

    // mime check
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        $errors['cover_image'][] = 'Podporované formáty: JPG, PNG, WEBP.';
    }

    return $errors;
}


