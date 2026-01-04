<?php
/**
 * User login form view
 * 
 * @file App\Views\Auth\Login
 * @var array $errors Validation errors
 * @var array $old Old input data
 */
?>
<h1 class="form-title">Přihlášení</h1>

<form class="form-container" 
      action="<?= APP_BASE ?>/login/submit" 
      method="POST" 
      autocomplete="off"
      data-validation-rules='{"identifier":[["required"]],"password":[["required"]]}'>
    
    <?= csrfField() ?>

    <?php if (!empty($errors['csrf'])): ?>
        <div class="form-error-general">
            <?php
            $error = $errors['csrf'];
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>
    <?php endif; ?>

    <?php if ($errorStatus): ?>
        <div class="form-error-general">
            <?php
            $error = [$errorStatus];
            require __DIR__ . '/../partials/errors-tooltip.php';
            ?>
        </div>
    <?php endif; ?>

    <div class="form-row">
        <label for="identifier">Uživatelské jméno nebo email*:</label>
        <input type="text" id="identifier" name="identifier"
               value="<?= htmlspecialchars($old['identifier'] ?? '') ?>"
               class="<?= !empty($errors['identifier']) ? 'error' : '' ?>"
               required>
        <?php
        $error = $errors['identifier'] ?? null;
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

    <button type="submit">Přihlásit se</button>

    <p class="form-link">
        Nemáte ještě účet? <a href="<?= APP_BASE ?>/register">Zaregistrujte se</a>
    </p>
</form>
