<?php
/**
 * Small game card partial for user profile
 * 
 * @file App\Views\Partials\GameCardSmallProfile
 * @var array $game Game data with status, approved_at, rejected_at
 */
$covers = $game['covers'] ?? [];
$imageSrc = $covers['thumb_vertical'] ?? $covers['thumb_horizontal'] ?? $covers['full'] ?? 'public/assets/images/placeholder.png';

$rating = $game['average_rating'] ?? 0;
$reviewsCount = $game['review_count'] ?? 0;
$scoreClass = $reviewsCount == 0 ? 'none' : ($rating >= 7.5 ? 'high' : ($rating >= 5 ? 'mid' : 'low'));
$status = $game['status'] ?? 'pending';

// Determine status text and date
$statusText = '';
$statusClass = '';
$statusDate = '';

if ($status === 'active') {
    $statusText = 'Schváleno';
    $statusClass = 'status-approved';
    $statusDate = $game['approved_at'] ?? $game['display_date'] ?? $game['created_at'] ?? '';
} elseif ($status === 'pending') {
    $statusText = 'Čeká na schválení';
    $statusClass = 'status-pending';
    $statusDate = '';
} elseif ($status === 'rejected') {
    $statusText = 'Zamítnuto';
    $statusClass = 'status-rejected';
    $statusDate = $game['rejected_at'] ?? $game['created_at'] ?? '';
}

$formattedDate = $statusDate ? date('d.m.Y', strtotime($statusDate)) : '';
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

            <div class="game-card-small-date <?= $statusClass ?>">
                <?php if ($status === 'active' && $formattedDate): ?>
                    <?= htmlspecialchars($statusText) ?>: <?= htmlspecialchars($formattedDate) ?>
                <?php elseif ($status === 'pending'): ?>
                    <?= htmlspecialchars($statusText) ?>
                <?php elseif ($status === 'rejected' && $formattedDate): ?>
                    <?= htmlspecialchars($statusText) ?>: <?= htmlspecialchars($formattedDate) ?>
                <?php else: ?>
                    Přidáno: <?= htmlspecialchars($formattedDate ?: date('d.m.Y', strtotime($game['created_at'] ?? ''))) ?>
                <?php endif; ?>
            </div>
            
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

