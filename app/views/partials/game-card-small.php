<?php
/**
 * @var array $game Game data
 */
$covers = $game['covers'] ?? [];
$imageSrc = $covers['thumb_vertical'] ?? $covers['thumb_horizontal'] ?? $covers['full'] ?? 'public/assets/images/placeholder.png';

$rating = $game['average_rating'];
$reviewsCount = $game['review_count'];
$scoreClass = $reviewsCount == 0 ? 'none' : ($rating >= 7.5 ? 'high' : ($rating >= 5 ? 'mid' : 'low'));
?>

<div class="game-card-small">
    <a href="<?= APP_BASE ?>/game?id=<?= $game['id'] ?>" class="game-card-small-link">
        <div class="game-card-small-image">
            <img src="<?= htmlspecialchars($imageSrc) ?>" 
                 alt="<?= htmlspecialchars($game['title']) ?>">
        </div>
        
        <div class="game-card-small-body">
            <h3 class="game-card-small-title" title="<?= htmlspecialchars($game['title']) ?>">
                <?= htmlspecialchars($game['title']) ?>
            </h3>
            
            <div class="game-card-small-footer">
                <div class="game-card-small-score score-<?= $scoreClass ?>">
                    <?= number_format($rating, 1) ?>
                </div>
                <div class="game-card-small-reviews">
                    <?= $reviewsCount ?> <?= $reviewsCount == 1 ? 'recenze' : 'recenzí' ?>
                </div>
            </div>
        </div>
    </a>
</div>
