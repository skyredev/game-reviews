<section class="admin-page">
    <div class="page-header">
        <h1>Administrace</h1>
    </div>
    
    <!-- Statistics -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['users']['total'] ?></div>
                <div class="stat-label">Uživatelé</div>
                <div class="stat-detail"><?= $stats['users']['admins'] ?> admin, <?= $stats['users']['regular'] ?> uživatel</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🎮</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['games']['total'] ?></div>
                <div class="stat-label">Hry</div>
                <div class="stat-detail"><?= $stats['games']['active'] ?> aktivní, <?= $stats['games']['pending'] ?> čeká</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['games']['pending'] ?></div>
                <div class="stat-label">Čeká na schválení</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['reviews']['avg_rating'] ?></div>
                <div class="stat-label">Průměrné hodnocení</div>
                <div class="stat-detail">z <?= $stats['reviews']['total'] ?> recenzí</div>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="admin-section">
        <h2>Uživatelé</h2>
        <?php if (empty($users)): ?>
            <p class="no-items">Žádní uživatelé.</p>
        <?php else: ?>
            <div class="users-list">
                <?php foreach ($users as $user): ?>
                    <div class="user-row">
                        <a href="<?= APP_BASE ?>/user?id=<?= $user['id'] ?>" class="user-username">
                            <?= htmlspecialchars($user['username']) ?>
                        </a>
                        <span class="user-name"><?= htmlspecialchars($user['name'] ?? '—') ?></span>
                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                        <span class="user-role"><?= $user['role'] === 'admin' ? 'Admin' : 'Uživatel' ?></span>
                        <span class="user-date"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php
            // Build URL with other params preserved
            $gamesPage = $_GET['games_page'] ?? 1;
            $baseUrl = '/admin' . ($gamesPage > 1 ? '?games_page=' . $gamesPage : '');
            $pageParam = 'users_page';
            $currentPage = $usersCurrentPage;
            $totalPages = $usersPages;
            require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
    
    <!-- Pending Games -->
    <div class="admin-section">
        <h2>Hry na schválení</h2>
        <?php if (empty($games)): ?>
            <p class="no-items">Žádné hry čekající na schválení.</p>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($games as $game): ?>
                    <?php require __DIR__ . '/../partials/game-card-small-admin.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <?php
            // Build URL with other params preserved
            $usersPage = $_GET['users_page'] ?? 1;
            $baseUrl = '/admin' . ($usersPage > 1 ? '?users_page=' . $usersPage : '');
            $pageParam = 'games_page';
            $currentPage = $gamesCurrentPage;
            $totalPages = $gamesPages;
            require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</section>
