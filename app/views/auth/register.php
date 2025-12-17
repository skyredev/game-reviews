<h1 class="form-title">Registrace</h1>

<form class="form-container" action="<?= APP_BASE ?>/register/submit" method="POST" enctype="multipart/form-data" autocomplete="off">
    
    <?= csrfField() ?>

    <?php if (!empty($errors['csrf'])): ?>
        <div class="form-error-general">
            <small class="error"><?= htmlspecialchars($errors['csrf'][0]) ?></small>
        </div>
    <?php endif; ?>

    <div class="form-row">
        <label for="name">Jméno:</label>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($old['name'] ?? '') ?>"
               class="<?= !empty($errors['name']) ? 'error' : '' ?>"
               required>
        <?php if (!empty($errors['name'])): ?>
            <small class="error"><?= htmlspecialchars($errors['name'][0]) ?></small>
        <?php endif; ?>
    </div>

    <div class="form-row">
        <label for="username">Uživatelské jméno:</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($old['username'] ?? '') ?>"
               class="<?= !empty($errors['username']) ? 'error' : '' ?>"
               required>
        <?php if (!empty($errors['username'])): ?>
            <small class="error"><?= htmlspecialchars($errors['username'][0]) ?></small>
        <?php endif; ?>
    </div>

    <div class="form-row">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
               class="<?= !empty($errors['email']) ? 'error' : '' ?>"
               required>
        <?php if (!empty($errors['email'])): ?>
            <small class="error"><?= htmlspecialchars($errors['email'][0]) ?></small>
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

    <div class="form-row">
        <label for="password_confirmation">Potvrzení hesla:</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="<?= !empty($errors['password_confirmation']) ? 'error' : '' ?>"
               required>
        <?php if (!empty($errors['password_confirmation'])): ?>
            <small class="error"><?= htmlspecialchars($errors['password_confirmation'][0]) ?></small>
        <?php endif; ?>
    </div>

    <div class="form-row">
        <label for="avatar">Profilový obrázek (volitelné):</label>
        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"
               class="<?= !empty($errors['avatar']) ? 'error' : '' ?>">
        <?php if (!empty($errors['avatar'])): ?>
            <small class="error"><?= htmlspecialchars($errors['avatar'][0]) ?></small>
        <?php endif; ?>
    </div>

    <button type="submit">Registrovat</button>

    <p class="form-link">
        Už máte účet? <a href="<?= APP_BASE ?>/login">Přihlaste se</a>
    </p>
</form>

