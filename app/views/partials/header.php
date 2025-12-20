<nav class="nav-container">
    <a href="<?= APP_BASE ?>/" class="logo">
        <img src="<?= APP_BASE ?>/public/assets/icons/logo.svg" alt="Game Reviews logo" width="62" height="62">
    </a>
    
    <button class="mobile-menu-toggle" aria-label="Menu">
        <img id="menu-icon" src="<?= APP_BASE ?>/public/assets/icons/menu.svg" alt="Menu" width="24" height="24">
    </button>
    
    <div class="nav-menu">
        <ul class="nav-list">
            <li class="nav-item"><a href="<?= APP_BASE ?><?= buildPaginationUrl('/games', 'games') ?>">Hry</a></li>
            <?php if (isLoggedIn()): ?>
                <li class="nav-item"><a href="<?=APP_BASE ?>/games/create">Přidat hru</a></li>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <?php
                $userId = currentUser()['id'];
                $profileKey = 'user_' . $userId;
                ?>
                <li class="nav-item"><a href="<?= APP_BASE ?><?= buildPaginationUrl('/user?id=' . $userId, $profileKey) ?>">Profil</a></li>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <li class="nav-item"><a href="<?= APP_BASE ?><?= buildPaginationUrl('/admin', 'admin') ?>">Admin</a></li>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <li class="nav-item"><a href="<?= APP_BASE ?>/logout">Odhlásit (<?= htmlspecialchars(currentUser()['username']) ?>)</a></li>
            <?php else: ?>
                <li class="nav-item"><a href="<?= APP_BASE ?>/login">Přihlášení</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>