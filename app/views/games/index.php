<?php
/**
 * Games list view
 * 
 * @file App\Views\Games\Index
 * @var array $games List of games
 * @var string|null $currentSort Current sort order
 * @var string|null $successMessage Success message
 */
?>
<section class="games-page">
    <?php if (!empty($successMessage)): ?>
        <div class="success-message">
            <?php
            $message = $successMessage;
            require __DIR__ . '/../partials/success-tooltip.php';
            ?>
        </div>
    <?php endif; ?>
    <div class="page-header">
        <h1>Seznam her</h1>
        <div class="page-header-actions">
            <label for="sort-select" class="sort-label">Seřadit podle:</label>
            <select id="sort-select" class="sort-select">
                <option value="name_asc" <?= ($currentSort ?? 'rating_desc') === 'name_asc' ? 'selected' : '' ?>>Název (A-Z)</option>
                <option value="name_desc" <?= ($currentSort ?? 'rating_desc') === 'name_desc' ? 'selected' : '' ?>>Název (Z-A)</option>
                <option value="rating_desc" <?= ($currentSort ?? 'rating_desc') === 'rating_desc' ? 'selected' : '' ?>>Hodnocení (nejvyšší)</option>
                <option value="rating_asc" <?= ($currentSort ?? 'rating_desc') === 'rating_asc' ? 'selected' : '' ?>>Hodnocení (nejnižší)</option>
                <option value="date_desc" <?= ($currentSort ?? 'rating_desc') === 'date_desc' ? 'selected' : '' ?>>Datum (nejnovější)</option>
                <option value="date_asc" <?= ($currentSort ?? 'rating_desc') === 'date_asc' ? 'selected' : '' ?>>Datum (nejstarší)</option>
            </select>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_BASE ?>/games/create" class="btn">Přidat hru</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (empty($games)): ?>
        <p class="no-games">Zatím nejsou žádné hry k dispozici.</p>
    <?php else: ?>
        <div class="games-grid">
            <?php foreach ($games as $game): ?>
                <?php require __DIR__ . '/../partials/game-card-small.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <?php
        $baseUrl = '/games';
        $key = 'games';
        require __DIR__ . '/../partials/pagination.php';
        ?>
    <?php endif; ?>
</section>
