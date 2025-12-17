<section class="home-page">
    <h1>Vítejte na Game Reviews</h1>
    <p>Objevte recenze nejnovějších her, hodnocení uživatelů a nejnovější herní zprávy.</p>

    <!-- Top 10 Games Carousel -->
    <div class="top-games-container">
        <div class="top-games-header">
            <h2>🏆 Top 10 Her</h2>
            <div class="carousel-controls">
                <button class="carousel-btn carousel-prev" data-carousel="top" aria-label="Předchozí">
                    <img src="<?= APP_BASE ?>/public/assets/icons/arrow-left.svg" alt="Arrow-Left" width="24" height="24">
                </button>
                <button class="carousel-btn carousel-next" data-carousel="top" aria-label="Další">
                    <img src="<?= APP_BASE ?>/public/assets/icons/arrow-right.svg" alt="Arrow-Right" width="24" height="24">
                </button>
            </div>
        </div>

        <div class="games-carousel-wrapper">
            <div class="games-carousel" data-carousel="top">
                <?php foreach ($topGames as $index => $game): ?>
                    <?php
                    $showRank = true;
                    require __DIR__ . '/../partials/game-card.php';
                    ?>
                <?php endforeach; ?>

            </div>
        </div>
    </div>

    <!-- Recent Games Carousel (only if there are games) -->
    <?php if (!empty($recentGames)): ?>
        <div class="recent-games-container">
            <div class="recent-games-header">
                <h2>Poslední přidané</h2>
                <div class="carousel-controls">
                    <button class="carousel-btn carousel-prev" data-carousel="recent" aria-label="Předchozí">
                        <img src="<?= APP_BASE ?>/public/assets/icons/arrow-left.svg" alt="Arrow-Left" width="24" height="24">
                    </button>
                    <button class="carousel-btn carousel-next" data-carousel="recent" aria-label="Další">
                        <img src="<?= APP_BASE ?>/public/assets/icons/arrow-right.svg" alt="Arrow-Right" width="24" height="24">
                    </button>
                </div>
            </div>

            <div class="games-carousel-wrapper">
                <div class="games-carousel" data-carousel="recent">
                    <?php foreach ($recentGames as $index => $game): ?>
                        <?php
                        $showRank = false;
                        require __DIR__ . '/../partials/game-card.php';
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Call to Action Button -->
    <div class="cta-section">
        <a href="<?= APP_BASE ?>/games" class="cta-button">
            <span>Prohlédnout všechny hry</span>
        </a>
    </div>
</section>
