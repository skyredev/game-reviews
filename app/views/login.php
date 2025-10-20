<h1>Prihlaste se</h1>
<form class="auth_form" action="<?=APP_BASE ?>/login/submit" method="POST" autocomplete="off">
    <div class="auth_form_container">
        <div class="auth_form_box">
            <label for="identifier">Username or email:</label>
            <input type="text" id="identifier" name="identifier" class="<?= !empty($errors['identifier']) ? 'error' : '' ?>" value="<?= htmlspecialchars($old['identifier'] ?? '') ?>" placeholder="Uživatelské jméno nebo email" required>
            <?php if (!empty($errors['identifier'])): ?>
                <small class="error">
                    <?php foreach ($errors['identifier'] as $error): ?>
                        <span><?= htmlspecialchars($error) ?></span>
                    <?php endforeach; ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="auth_form_box">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="<?= !empty($errors['password']) ? 'error' : '' ?>" placeholder="Heslo" required>
            <?php if (!empty($errors['password'])): ?>
                <small class="error">
                    <?php foreach ($errors['password'] as $error): ?>
                        <span><?= htmlspecialchars($error) ?></span>
                    <?php endforeach; ?>
                </small>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($errorStatus): ?>
        <span><?= htmlspecialchars($errorStatus) ?></span>
    <?php endif; ?>
    <button type="submit">Login</button>
</form>
