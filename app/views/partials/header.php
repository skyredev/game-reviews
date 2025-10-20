<nav class="nav-container">
    <a href="<?= APP_BASE ?>/" class="logo">
        <img
            src="<?= APP_BASE ?>/public/assets/images/logo.svg"
            alt="Game Reviews logo"
            width="62" height="62"
        >
    </a>
    <ul class="nav-list">
        <li class="nav-item"><a href="<?=APP_BASE ?>/games">Games</a></li>
        <li class="nav-item"><a href="<?=APP_BASE ?>/admin">Admin</a></li>
        <?php if (!empty($_SESSION['user'])): ?>
            <li class="nav-item"><a href="<?= APP_BASE ?>/logout">Logout (<?= htmlspecialchars($_SESSION['user']['username']) ?>)</a></li>
        <?php else: ?>
            <li class="nav-item"><a href="<?= APP_BASE ?>/login">Login</a></li>
            <li class="nav-item"><a href="<?= APP_BASE ?>/register">Register</a></li>
        <?php endif; ?>
    </ul>
    <?php if (isLoggedIn()): ?>
        <a href="<?= APP_BASE ?>/profile">
            <p><?= htmlspecialchars(currentUser()['username']) ?></p>
        </a>
    <?php endif; ?>
</nav>
