<h1>Registrujte se</h1>
<form class="register_form_container" action="<?=APP_BASE ?>/register/submit" method="POST" autocomplete="off">
    <div class="register_form">
        <div class="auth_form_box">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <?php if (!empty($errors['name'])): ?>
                <small class="error"><?= htmlspecialchars($errors['name']) ?></small>
            <?php endif; ?>
        </div>
        <div class="auth_form_box">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <?php if (!empty($errors['username'])): ?>
                <small class="error"><?= htmlspecialchars($errors['username']) ?></small>
            <?php endif; ?>
        </div>
        <div class="auth_form_box">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <?php if (!empty($errors['email'])): ?>
                <small class="error"><?= htmlspecialchars($errors['email']) ?></small>
            <?php endif; ?>
        </div>
        <div class="auth_form_box">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="password" required>
            <?php if (!empty($errors['password'])): ?>
                <small class="error"><?= htmlspecialchars($errors['password']) ?></small>
            <?php endif; ?>
        </div>
    </div>
    <button type="submit">Register</button>
</form>
