<?php
/**
 * Review item partial
 * 
 * @file App\Views\Partials\ReviewItem
 * @var array $review Review data
 * @var int|null $gameId Game ID
 */
$reviewUser = $review['username'] ?? '';
$reviewAvatar = $review['avatar_path'] ?? null;
$reviewDate = $review['updated_at'] && $review['updated_at'] != $review['created_at'] 
    ? $review['updated_at'] 
    : $review['created_at'];
$userReaction = $review['user_reaction'] ?? null;
?>

<div class="review-item">
    <div class="review-user">
        <div class="review-avatar">
            <?php if ($reviewAvatar): ?>
                <img src="<?= htmlspecialchars($reviewAvatar) ?>" alt="<?= htmlspecialchars($reviewUser) ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= strtoupper(substr($reviewUser, 0, 1)) ?></div>
            <?php endif; ?>
        </div>
        <div class="review-user-info">
            <div class="review-username"><?= htmlspecialchars($reviewUser) ?></div>
            <div class="review-date"><?= date('d.m.Y H:i', strtotime($reviewDate)) ?></div>
        </div>
    </div>
    
    <div class="review-rating">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
        <?php endfor; ?>
        <span class="rating-value"><?= $review['rating'] ?>/10</span>
    </div>

    <?php if (!empty($review['comment'])): ?>
        <div class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></div>
    <?php endif; ?>
    
    <div class="review-reactions">
        <button class="reaction-btn like-btn <?= $userReaction === 'like' ? 'active' : '' ?>"
                data-review-id="<?= $review['id'] ?>"
                data-reaction="like"
                <?= !isLoggedIn() ? 'disabled' : '' ?>>
            <img src="<?= APP_BASE ?>/public/assets/icons/like.svg" alt="Like" width="16" height="16">
            <span class="reaction-count"><?= (int)($review['likes_count'] ?? 0) ?></span>
        </button>
        <button class="reaction-btn dislike-btn <?= $userReaction === 'dislike' ? 'active' : '' ?>" 
                data-review-id="<?= $review['id'] ?>" 
                data-reaction="dislike"
                <?= !isLoggedIn() ? 'disabled' : '' ?>>
            <img src="<?= APP_BASE ?>/public/assets/icons/dislike.svg" alt="Dislike" width="16" height="16">
            <span class="reaction-count"><?= (int)($review['dislikes_count'] ?? 0) ?></span>
        </button>
    </div>
</div>

