<nav class="nav-container">
    <a href="<?= APP_BASE ?>/" class="logo">
        <img
            src="<?= APP_BASE ?>/public/assets/images/logo.svg"
            alt="Game Reviews logo"
            width="62" height="62"
        >
    </a>
    
    <button class="mobile-menu-toggle" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <div class="nav-menu">
        <ul class="nav-list">
            <li class="nav-item"><a href="<?=APP_BASE ?>/games">Hry</a></li>
            <?php if (isLoggedIn()): ?>
                <li class="nav-item"><a href="<?=APP_BASE ?>/games/create">Přidat hru</a></li>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <li class="nav-item"><a href="<?=APP_BASE ?>/admin">Admin</a></li>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <li class="nav-item"><a href="<?= APP_BASE ?>/logout">Odhlásit (<?= htmlspecialchars(currentUser()['username']) ?>)</a></li>
            <?php else: ?>
                <li class="nav-item"><a href="<?= APP_BASE ?>/login">Přihlášení</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>