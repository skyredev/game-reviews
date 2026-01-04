<?php
/**
 * Game detail view
 * 
 * @file App\Views\Games\Show
 * @var array $game Game data
 * @var array $reviews List of reviews
 * @var array|null $userReview Current user's review
 * @var array $errors Validation errors
 * @var array $old Old input data
 */
$covers = $game['covers'] ?? [];
$coverImageFull = $covers['full'] ?? $covers['thumb_vertical'] ?? $covers['thumb_horizontal'] ?? 'public/assets/images/placeholder.png';
$coverImageThumb = $covers['thumb_vertical'] ?? $covers['full'] ?? $covers['thumb_horizontal'] ?? 'public/assets/images/placeholder.png';

$rating = $game['average_rating'];
$reviewsCount = $game['review_count'];
$sentiment = getRatingText($rating, $reviewsCount);
$scoreClass = $reviewsCount == 0 ? 'none' : ($rating >= 7.5 ? 'high' : ($rating >= 5 ? 'mid' : 'low'));
?>

<section class="game-page">
    <!-- Hidden CSRF token for AJAX requests -->
    <?php if (isAdmin()): ?>
        <?= csrfField() ?>
    <?php endif; ?>
    <div class="game-page-wrapper">
        <?php if (!empty($successMessage)): ?>
            <div class="success-message">
                <?php
                $message = $successMessage;
                require __DIR__ . '/../partials/success-tooltip.php';
                ?>
            </div>
        <?php endif; ?>
        <div class="game-page-header">
        <!-- Left: Cover Image -->
        <div class="game-cover">
            <img src="<?= htmlspecialchars($coverImageFull) ?>" 
                 data-thumb="<?= htmlspecialchars($coverImageThumb) ?>"
                 data-full="<?= htmlspecialchars($coverImageFull) ?>"
                 alt="<?= htmlspecialchars($game['title']) ?>"
                 class="game-cover-image">
        </div>

        <!-- Center: Title and Description -->
        <div class="game-info">
            <h1><?= htmlspecialchars($game['title']) ?></h1>
            <p class="game-description"><?= nl2br(htmlspecialchars($game['description'])) ?></p>
            
            <div class="game-details">
                <h2>O hře</h2>
                <dl class="detail-list">
                    <div class="detail-item">
                        <dt>Rok vydání:</dt>
                        <dd><?= htmlspecialchars($game['release_year'] ?? '—') ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>Developer:</dt>
                        <dd><?= htmlspecialchars($game['developer'] ?? '—') ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>Publisher:</dt>
                        <dd><?= htmlspecialchars($game['publisher'] ?? '—') ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>Žánry:</dt>
                        <dd><?= !empty($game['genres']) ? htmlspecialchars(implode(', ', $game['genres'])) : '—' ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>Platformy:</dt>
                        <dd><?= !empty($game['platforms']) ? htmlspecialchars(implode(', ', $game['platforms'])) : '—' ?></dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <!-- Right: Rating Box -->
        <div class="game-rating-box">
            <?php if ($game['status'] === 'pending' && isAdmin()): ?>
                <!-- Admin actions for pending games -->
                <div class="admin-actions">
                    <button type="button" class="btn btn-success approve-game-btn" data-game-id="<?= $game['id'] ?>">Schválit</button>
                    <button type="button" class="btn btn-danger reject-game-btn" data-game-id="<?= $game['id'] ?>">Zamítnout</button>
                </div>
                <div class="game-author">
                    <small>Hru nabídnul: <?= htmlspecialchars($game['author_username']) ?></small>
                </div>
                
                <!-- Rejection Modal -->
                <div id="reject-modal" class="modal hidden">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Zamítnout hru</h3>
                            <button type="button" class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <label for="reject-reason">Důvod zamítnutí (volitelné):</label>
                            <textarea id="reject-reason" rows="4" placeholder="Zadejte důvod zamítnutí..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary modal-cancel">Zrušit</button>
                            <button type="button" class="btn btn-danger modal-confirm-reject">Zamítnout</button>
                        </div>
                    </div>
                </div>
            <?php elseif ($game['status'] === 'pending'): ?>
                <!-- User's own pending game -->
                <div class="game-status-info">
                    <div class="status-message">Čeká na schválení</div>
                </div>
            <?php elseif ($game['status'] === 'rejected'): ?>
                <!-- Rejected game info -->
                <div class="game-status-info rejected">
                    <div class="status-message">Hra byla zamítnuta</div>
                    <?php if ($rejectionInfo): ?>
                        <div class="rejection-details">
                            <div class="rejection-by">
                                Zamítnul: <?= htmlspecialchars($rejectionInfo['reviewed_by_username']) ?>
                            </div>
                            <div class="rejection-date">
                                <?= date('d.m.Y', strtotime($rejectionInfo['created_at'])) ?>
                            </div>
                            <?php if (!empty($rejectionInfo['reason'])): ?>
                                <div class="rejection-reason">
                                    <strong>Důvod:</strong>
                                    <p><?= nl2br(htmlspecialchars($rejectionInfo['reason'])) ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="game-author">
                                <small>Hru nabídnul: <?= htmlspecialchars($game['author_username']) ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="rating-badge-large score-<?= $scoreClass ?>">
                    <?= number_format($rating, 1) ?>
                </div>
                <div class="rating-info">
                    <div class="rating-sentiment"><?= htmlspecialchars($sentiment) ?></div>
                    <div class="rating-count"><?= $reviewsCount ?> <?= $reviewsCount == 1 ? 'recenze' : 'recenzí' ?></div>
                </div>
                <div class="game-author">
                    <small>Přidal: <?= htmlspecialchars($game['author_username']) ?></small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
    
    <!-- Reviews Section -->
    <?php if ($game['status'] === 'active'): ?>
    <div class="container">
        <div class="reviews-section">
            <h2>Recenze</h2>
        
        <?php if ($userReview): ?>
            <!-- User's Review (if logged in and has review) -->
            <div class="review-block user-review" id="user-review-block">
                <div class="review-header">
                    <h3>Vaše recenze</h3>
                    <div class="review-actions">
                        <button class="btn-icon edit-review-btn" data-review-id="<?= $userReview['id'] ?>" title="Upravit">
                            <img src="<?= APP_BASE ?>/public/assets/icons/edit.svg" alt="Edit" width="18" height="18">
                        </button>
                        <button type="button" class="btn-icon delete-review-btn" data-review-id="<?= $userReview['id'] ?>" data-game-id="<?= $game['id'] ?>" title="Smazat">
                            <img src="<?= APP_BASE ?>/public/assets/icons/delete.svg" alt="Delete" width="18" height="18">
                        </button>
                    </div>
                </div>
                <div id="review-display">
                    <?php $review = $userReview; require __DIR__ . '/../partials/review-item.php'; ?>
                </div>
                <div id="review-edit-form" class="hidden">
                    <form method="POST" 
                          action="<?= APP_BASE ?>/game/review" 
                          class="review-form"
                          data-validation-rules='{"rating":[["required"],["rating",1,10]],"comment":[["required"],["string"],["min",10]]}'>
                        <?= csrfField() ?>
                        <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                        
                        <div class="form-row">
                            <label for="edit_rating">Hodnocení (1-10):</label>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <input type="radio" id="edit_star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $userReview['rating'] == $i ? 'checked' : '' ?>>
                                    <label for="edit_star<?= $i ?>" class="star-label"><?= $i ?></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <label for="edit_comment">Komentář:</label>
                            <textarea id="edit_comment" name="comment" rows="5" required><?= htmlspecialchars($userReview['comment'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn">Uložit změny</button>
                            <button type="button" class="btn btn-secondary cancel-edit-btn">Zrušit</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif (isLoggedIn()): ?>
            <!-- Review Form (if logged in but no review) -->
            <div class="review-form-block">
                <h3>Napsat recenzi</h3>
                <form method="POST" 
                      action="<?= APP_BASE ?>/game/review" 
                      class="review-form"
                      data-validation-rules='{"rating":[["required"],["rating",1,10]],"comment":[["required"],["string"],["min",10]]}'>
                    <?= csrfField() ?>
                    <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                    
                    <?php if (!empty($errors['general'])): ?>
                        <div class="form-error-general">
                            <?php
                            $error = $errors['general'];
                            require __DIR__ . '/../partials/errors-tooltip.php';
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors['csrf'])): ?>
                        <div class="form-error-general">
                            <?php
                            $error = $errors['csrf'];
                            require __DIR__ . '/../partials/errors-tooltip.php';
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <label for="star1">Hodnocení (1-10)*:</label>
                        <div class="rating-stars" id="rating-stars">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= (int)($old['rating'] ?? 0) == $i ? 'checked' : '' ?>>
                                <label for="star<?= $i ?>" class="star-label"><?= $i ?></label>
                            <?php endfor; ?>
                        </div>
                        <?php
                        $error = $errors['rating'] ?? null;
                        require __DIR__ . '/../partials/errors-tooltip.php';
                        ?>
                    </div>
                    
                    <div class="form-row">
                        <label for="comment">Komentář*:</label>
                        <textarea id="comment" name="comment" rows="5" required><?= htmlspecialchars($old['comment'] ?? '') ?></textarea>
                        <?php
                        $error = $errors['comment'] ?? null;
                        require __DIR__ . '/../partials/errors-tooltip.php';
                        ?>
                    </div>
                    
                    <button type="submit" class="btn">Uložit recenzi</button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Other Reviews -->
        <?php if (!empty($reviews)): ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-block">
                        <?php if (isAdmin() && $review['user_id'] != ($_SESSION['user']['id'] ?? 0)): ?>
                            <div class="review-header">
                                <div></div>
                                <button type="button" class="btn-icon delete-review-btn" data-review-id="<?= $review['id'] ?>" data-game-id="<?= $game['id'] ?>" title="Smazat (admin)">
                                    <img src="<?= APP_BASE ?>/public/assets/icons/delete.svg" alt="Delete" width="18" height="18">
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php require __DIR__ . '/../partials/review-item.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!$userReview && !isLoggedIn()): ?>
            <p class="no-reviews">Zatím nejsou žádné recenze.</p>
        <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

