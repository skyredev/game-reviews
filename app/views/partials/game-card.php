<?php
/**
 * @var array $game Game data
 * @var int $index Card position index
 */
$covers = $game['covers'] ?? [];
$imageSrc = $covers['thumb_horizontal'] ?? $covers['thumb_vertical'] ?? $covers['full'] ?? 'public/assets/images/placeholder.png';

$rating = $game['average_rating'];
$sentiment = getRatingText($rating);
$scoreClass = $rating >= 7.5 ? 'high' : ($rating >= 5 ? 'mid' : 'low');
?>

<div class="game-card">
    <?php if ($index <= 3): ?>
        <div class="game-card-rank rank-<?= $index ?>"><?= $index ?></div>
    <?php endif; ?>
    
    <a href="/games/<?= $game['id'] ?>" class="game-card-link">
        <div class="game-card-image">
            <img src="<?= htmlspecialchars($imageSrc) ?>" 
                 alt="<?= htmlspecialchars($game['title']) ?>">
        </div>
        
        <div class="game-card-body">
            <h3 class="game-card-title" title="<?= htmlspecialchars($game['title']) ?>">
                <?= htmlspecialchars($game['title']) ?>
            </h3>
            
            <?php if (!empty($game['genres'])): ?>
                <div class="game-card-tags">
                    <?php foreach ($game['genres'] as $genre): ?>
                        <span class="game-tag"><?= htmlspecialchars($genre) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="game-card-divider"></div>
            
            <div class="game-card-footer">
                <div class="game-card-meta">
                    <div class="score-label">HODNOCENÍ</div>
                    <div class="score-text"><?= $sentiment ?></div>
                    <div class="score-reviews">
                        <?= $game['review_count'] ?> <?= $game['review_count'] == 1 ? 'recenze' : 'recenzí' ?>
                    </div>
                </div>
                <div class="score-badge score-<?= $scoreClass ?>">
                    <?= number_format($rating, 1) ?>
                </div>
            </div>
        </div>
    </a>
</div>