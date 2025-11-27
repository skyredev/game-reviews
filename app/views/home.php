<?php
/** @var array $games List of games passed from the controller */
?>
<section class="home-page">
    <h1>Vítejte na Game Reviews</h1>
    <p>Objevte recenze nejnovějších her, hodnocení uživatelů a nejnovější herní zprávy.</p>

    <div class="top-games-container">
        <h2>Nejlépe hodnocené hry</h2>
        <div class="games-list">
            <?php foreach ($games as $game): ?>
                <div class="game-card-top">
                    <div class="game-image">
                        <img src="<?= htmlspecialchars($game['cover_thumb']) ?>" alt="<?= htmlspecialchars($game['title']) ?> Cover">
                    </div>
                    <div class="game-info">
                        <h3><?= htmlspecialchars($game['title']) ?></h3>
                        <p class="desc"><?= htmlspecialchars($game['description']) ?></p>
                    </div>
                    <div class="game-summary">
                        <span><?= number_format($game['average_rating'], 1) ?>/10</span>
                        <span class="year">Rok vydání: <?= htmlspecialchars($game['release_year'] ?? '—') ?></span>
                        <a href="/games/<?= $game['id'] ?>" class="btn">Zobrazit více</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


