<h1 class="form-title">Přihlášení</h1>

<form class="form-container" action="<?= APP_BASE ?>/login/submit" method="POST" autocomplete="off">
    
    <?= csrfField() ?>

    <?php if (!empty($errors['csrf'])): ?>
        <div class="form-error-general">
            <small class="error"><?= htmlspecialchars($errors['csrf'][0]) ?></small>
        </div>
    <?php endif; ?>

    <?php if ($errorStatus): ?>
        <div class="form-error-general">
            <small class="error"><?= htmlspecialchars($errorStatus) ?></small>
        </div>
    <?php endif; ?>

    <div class="form-row">
        <label for="identifier">Uživatelské jméno nebo email:</label>
        <input type="text" id="identifier" name="identifier"
               value="<?= htmlspecialchars($old['identifier'] ?? '') ?>"
               class="<?= !empty($errors['identifier']) ? 'error' : '' ?>"
               required>
        <?php if (!empty($errors['identifier'])): ?>
            <small class="error"><?= htmlspecialchars($errors['identifier'][0]) ?></small>
        <?php endif; ?>
    </div>

    <div class="form-row">
        <label for="password">Heslo:</label>
        <input type="password" id="password" name="password"
               class="<?= !empty($errors['password']) ? 'error' : '' ?>"
               required>
        <?php if (!empty($errors['password'])): ?>
            <small class="error"><?= htmlspecialchars($errors['password'][0]) ?></small>
        <?php endif; ?>
    </div>

    <button type="submit">Přihlásit se</button>

    <p class="form-link">
        Nemáte ještě účet? <a href="<?= APP_BASE ?>/register">Zaregistrujte se</a>
    </p>
</form>
