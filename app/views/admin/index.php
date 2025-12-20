<section class="admin-page">
    <?= csrfField() ?>
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
                        <a href="<?= APP_BASE ?><?= buildPaginationUrl('/user?id=' . $user['id'], 'user_' . $user['id']) ?>" class="user-username">
                            <?= htmlspecialchars($user['username']) ?>
                        </a>
                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                        <span class="user-role"><?= $user['role'] === 'admin' ? 'Admin' : 'Uživatel' ?></span>
                        <span class="user-date"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                        <?php if (isset($currentUserId) && $currentUserId != $user['id']): ?>
                            <div class="user-actions">
                                <button type="button" class="btn-icon admin-toggle-btn <?= $user['role'] === 'admin' ? 'admin-active' : '' ?>" 
                                        data-user-id="<?= $user['id'] ?>" 
                                        data-action="toggle-admin"
                                        title="<?= $user['role'] === 'admin' ? 'Odebrat admina' : 'Udělit admina' ?>">
                                    <img src="<?= APP_BASE ?>/public/assets/icons/admin.svg" alt="Admin" width="18" height="18">
                                </button>
                                <button type="button" class="btn-icon block-toggle-btn <?= $user['is_blocked'] ? 'blocked' : '' ?>" 
                                        data-user-id="<?= $user['id'] ?>" 
                                        data-action="toggle-block"
                                        title="<?= $user['is_blocked'] ? 'Odblokovat' : 'Zablokovat' ?>">
                                    <img src="<?= APP_BASE ?>/public/assets/icons/block.svg" alt="Block" width="18" height="18">
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php
            $baseUrl = buildPaginationUrl('/admin', 'admin');
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
            $baseUrl = buildPaginationUrl('/admin', 'admin');
            $pageParam = 'games_page';
            $currentPage = $gamesCurrentPage;
            $totalPages = $gamesPages;
            require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</section>
