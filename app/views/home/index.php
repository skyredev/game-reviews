<section class="home-page">
    <h1>Vítejte na Game Reviews</h1>
    <p>Objevte recenze nejnovějších her, hodnocení uživatelů a nejnovější herní zprávy.</p>

    <div class="top-games-container">
        <div class="top-games-header">
            <h2>Top 10 Her</h2>
            <div class="carousel-controls">
                <button class="carousel-btn carousel-prev" aria-label="Předchozí">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                <button class="carousel-btn carousel-next" aria-label="Další">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="games-carousel-wrapper">
            <div class="games-carousel">
                <?php 
                $index = 1;
                foreach ($games as $game): 
                    if ($index > 10) break;
                    require __DIR__ . '/../partials/game-card.php';
                    $index++;
                endforeach; 
                ?>
            </div>
        </div>
    </div>
</section>

