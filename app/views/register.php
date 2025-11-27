<h1 class="form-title">Registrace</h1>

<form class="form-container" action="<?= APP_BASE ?>/register/submit" method="POST" autocomplete="off">

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

    <button type="submit">Registrovat</button>
</form>
