<?php
/**
 * User profile view
 * 
 * @file App\Views\User\Profile
 * @var array $user User data
 * @var array $stats User statistics
 * @var array $games List of user's games
 * @var int $gamesTotal Total count of games
 * @var int $gamesPages Total pages for games
 * @var int $gamesCurrentPage Current page for games
 * @var string $currentSort Current sort order
 * @var int|null $currentUserId Current logged-in user ID
 */
?>
<section class="user-profile">
    <?php if (isAdmin() && isset($currentUserId) && $currentUserId != $user['id']): ?>
        <?= csrfField() ?>
    <?php endif; ?>
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if (!empty($user['avatar_path'])): ?>
                <img src="<?= APP_BASE ?>/<?= htmlspecialchars($user['avatar_path']) ?>" 
                     alt="<?= htmlspecialchars($user['username']) ?>" 
                     class="avatar-large">
            <?php else: ?>
                <div class="avatar-large avatar-placeholder">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h1 class="profile-username"><?= htmlspecialchars($user['username']) ?></h1>
            <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
            <div class="profile-date">
                Registrován: <?= date('d.m.Y', strtotime($user['created_at'])) ?>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="profile-stat-item">
                <div class="profile-stat-value"><?= $stats['games']['total'] ?></div>
                <div class="profile-stat-label">Her</div>
            </div>
            <div class="profile-stat-item">
                <div class="profile-stat-value"><?= $stats['reviews']['total'] ?></div>
                <div class="profile-stat-label"><?= $stats['reviews']['total'] == 1 ? 'recenze' : 'recenzí' ?></div>
            </div>
            <?php if (isAdmin() && isset($currentUserId) && $currentUserId != $user['id']): ?>
                <div class="profile-admin-actions">
                    <button type="button" class="btn-icon admin-toggle-btn <?= $user['role'] === 'admin' ? 'admin-active' : '' ?>" 
                            data-user-id="<?= $user['id'] ?>" 
                            data-action="toggle-admin"
                            title="<?= $user['role'] === 'admin' ? 'Odebrat admin' : 'Udělit admin' ?>">
                        <img src="<?= APP_BASE ?>/public/assets/icons/admin.svg" alt="Admin" width="20" height="20">
                    </button>
                    <button type="button" class="btn-icon block-toggle-btn <?= $user['is_blocked'] ? 'blocked' : '' ?>" 
                            data-user-id="<?= $user['id'] ?>" 
                            data-action="toggle-block"
                            title="<?= $user['is_blocked'] ? 'Odblokovat' : 'Zablokovat' ?>">
                        <img src="<?= APP_BASE ?>/public/assets/icons/block.svg" alt="Block" width="20" height="20">
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-games">
        <?php if (empty($games)): ?>
            <div class="page-header">
                <h2>Vaše přidané hry</h2>
                </div>
            </div>
            <p class="no-games">Uživatel zatím nepřidal žádné hry.</p>
        <?php else: ?>
            <div class="page-header">
                <h2>Vaše přidané hry</h2>
                <div class="page-header-actions">
                    <label for="sort-select" class="sort-label">Seřadit podle:</label>
                    <select id="sort-select" class="sort-select">
                        <option value="name_asc" <?= $currentSort === 'name_asc' ? 'selected' : '' ?>>Název (A-Z)</option>
                        <option value="name_desc" <?= $currentSort === 'name_desc' ? 'selected' : '' ?>>Název (Z-A)</option>
                        <option value="rating_desc" <?= $currentSort === 'rating_desc' ? 'selected' : '' ?>>Hodnocení (nejvyšší)</option>
                        <option value="rating_asc" <?= $currentSort === 'rating_asc' ? 'selected' : '' ?>>Hodnocení (nejnižší)</option>
                        <option value="date_desc" <?= $currentSort === 'date_desc' ? 'selected' : '' ?>>Datum (nejnovější)</option>
                        <option value="date_asc" <?= $currentSort === 'date_asc' ? 'selected' : '' ?>>Datum (nejstarší)</option>
                        <option value="status_asc" <?= $currentSort === 'status_asc' ? 'selected' : '' ?>>Status (A-Z)</option>
                        <option value="status_desc" <?= $currentSort === 'status_desc' ? 'selected' : '' ?>>Status (Z-A)</option>
                    </select>
                </div>
            </div>
            <div class="games-grid">
                <?php foreach ($games as $game): ?>
                    <?php require __DIR__ . '/../partials/game-card-small-profile.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <?php
            $baseUrl = '/user?id=' . $user['id'];
            $key = 'user_' . $user['id'];
            require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</section>

