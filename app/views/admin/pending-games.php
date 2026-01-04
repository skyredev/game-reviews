<?php
/**
 * Admin pending games view
 * 
 * @file App\Views\Admin\PendingGames
 * @var array $games List of pending games
 * @var int $gamesTotal Total count of pending games
 * @var int $gamesPages Total pages for pending games
 * @var int $gamesCurrentPage Current page for pending games
 * @var string $currentSort Current sort order
 */
?>
<section class="admin-page">
    <div class="page-header">
        <h1>Hry na schválení</h1>
    </div>
    
    <?php if (empty($games)): ?>
        <p class="no-items">Žádné hry čekající na schválení.</p>
    <?php else: ?>
        <div class="games-grid">
            <?php foreach ($games as $game): ?>
                <?php require __DIR__ . '/../partials/game-card-small-admin.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <?php
        $baseUrl = '/pending-games';
        $key = 'admin_pending';
        require __DIR__ . '/../partials/pagination.php';
        ?>
    <?php endif; ?>
</section>

