<?php
/**
 * @var array $game Game data (with author_username for admin view)
 */
$covers = $game['covers'] ?? [];
$imageSrc = $covers['thumb_vertical'] ?? $covers['thumb_horizontal'] ?? $covers['full'] ?? 'public/assets/images/placeholder.png';

$authorUsername = $game['author_username'] ?? '—';
$createdDate = $game['created_at'] ?? '';
$formattedDate = $createdDate ? date('d.m.Y', strtotime($createdDate)) : '—';
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
                <div class="game-card-small-date">
                    <?= htmlspecialchars($formattedDate) ?>
                </div>
            </div>
        </div>
    </a>
</div>


