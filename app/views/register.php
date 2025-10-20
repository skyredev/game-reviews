<h1>Registrujte se</h1>
<form class="register_form_container" action="<?=APP_BASE ?>/register/submit" method="POST" autocomplete="off">
    <div class="register_form">
        <div class="auth_form_box">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="<?= !empty($errors['name']) ? 'error' : '' ?>" value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Vaše jméno" required>
            <?php if (!empty($errors['name'])): ?>
                <small class="error">
                    <?php foreach ($errors['name'] as $error): ?>
                        <span><?= htmlspecialchars($error) ?></span>
                    <?php endforeach; ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="auth_form_box">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" class="<?= !empty($errors['username']) ? 'error' : '' ?>" value="<?= htmlspecialchars($old['username'] ?? '') ?>" placeholder="Uživatelské jméno" required>
            <?php if (!empty($errors['username'])): ?>
                <small class="error">
                    <?php foreach ($errors['username'] as $error): ?>
                        <span><?= htmlspecialchars($error) ?></span>
                    <?php endforeach; ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="auth_form_box">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="<?= !empty($errors['email']) ? 'error' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="Email" required>
            <?php if (!empty($errors['email'])): ?>
                <small class="error">
                    <?php foreach ($errors['email'] as $error): ?>
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
    <button type="submit">Register</button>
</form>
