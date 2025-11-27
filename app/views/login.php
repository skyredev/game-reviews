<h1 class="form-title">Přihlaste se</h1>

<form class="form-container" action="<?= APP_BASE ?>/login/submit" method="POST" autocomplete="off">

    <div class="form-row">
        <label for="identifier">Username nebo email:</label>
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


    <?php if ($errorStatus): ?>
        <small class="error"><?= htmlspecialchars($errorStatus) ?></small>
    <?php endif; ?>

    <button type="submit">Login</button>
</form>
