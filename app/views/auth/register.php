<?php
/**
 * User registration form view
 * 
 * @file App\Views\Auth\Register
 * @var array $errors Validation errors
 * @var array $old Old input data
 */
?>
<h1 class="form-title">Registrace</h1>

<form class="form-container" 
      action="<?= APP_BASE ?>/register/submit" 
      method="POST" 
      enctype="multipart/form-data" 
      autocomplete="off"
      data-validation-rules='{"username":[["required"],["username"],["max",50]],"email":[["required"],["email"],["email_part_min",4]],"password":[["required"],["password"]],"password_confirmation":[["required"],["confirmed"]],"avatar":[["image"],["image_max_size",<?= 2 * 1024 * 1024 ?>]]}'>
    
    <?= csrfField() ?>

    <?php if (!empty($errors['csrf'])): ?>
        <div class="form-error-general">
            <?php
            $error = $errors['csrf'];
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>
    <?php endif; ?>

    <div class="form-row">
        <label for="username">Uživatelské jméno*:</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($old['username'] ?? '') ?>"
               class="<?= !empty($errors['username']) ? 'error' : '' ?>"
               required>
        <?php
        $error = $errors['username'] ?? null;
        require __DIR__ . '/../partials/errors-tooltip.php';
        ?>
    </div>

    <div class="form-row">
        <label for="email">Email*:</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
               class="<?= !empty($errors['email']) ? 'error' : '' ?>"
               required>
        <?php
        $error = $errors['email'] ?? null;
        require __DIR__ . '/../partials/errors-tooltip.php';
        ?>
    </div>

    <div class="form-row">
        <label for="password">Heslo*:</label>
        <input type="password" id="password" name="password"
               class="<?= !empty($errors['password']) ? 'error' : '' ?>"
               required>
        <?php
        $error = $errors['password'] ?? null;
        require __DIR__ . '/../partials/errors-tooltip.php';
        ?>
    </div>

    <div class="form-row">
        <label for="password_confirmation">Potvrzení hesla*:</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="<?= !empty($errors['password_confirmation']) ? 'error' : '' ?>"
               required>
        <?php
        $error = $errors['password_confirmation'] ?? null;
        require __DIR__ . '/../partials/errors-tooltip.php';
        ?>
    </div>

    <div class="form-row">
        <label for="avatar">Profilový obrázek (volitelné):</label>
        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp"
               class="<?= !empty($errors['avatar']) ? 'error' : '' ?>">
        <?php
        $error = $errors['avatar'] ?? null;
        require __DIR__ . '/../partials/errors-tooltip.php';
        ?>
    </div>

    <button type="submit">Registrovat</button>

    <p class="form-link">
        Už máte účet? <a href="<?= APP_BASE ?>/login">Přihlaste se</a>
    </p>
</form>

