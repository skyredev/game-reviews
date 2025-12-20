<?php
/**
 * Pagination component
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links (e.g., '/games' or '/admin?users_page=')
 * @param string $pageParam Query parameter name for page (default: 'page')
 */
if ($totalPages > 1): ?>
    <nav class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="<?= APP_BASE ?><?= $baseUrl ?><?= str_contains($baseUrl, '?') ? '&' : '?' ?><?= $pageParam ?>=<?= $currentPage - 1 ?>" class="pagination-link pagination-prev">
                <img src="<?= APP_BASE ?>/public/assets/icons/light-arrow-left.svg" alt="Arrow-Left" width="16" height="16">
                <span>Předchozí</span>
            </a>
        <?php else: ?>
            <span class="pagination-link pagination-prev disabled">
                <img src="<?= APP_BASE ?>/public/assets/icons/light-arrow-left.svg" alt="Arrow-Left" width="16" height="16">
                <span>Předchozí</span>
            </span>
        <?php endif; ?>
        
        <div class="pagination-pages">
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            $separator = str_contains($baseUrl, '?') ? '&' : '?';
            
            if ($startPage > 1): ?>
                <a href="<?= APP_BASE ?><?= $baseUrl ?><?= $separator ?><?= $pageParam ?>=1" class="pagination-page">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <?php if ($i == $currentPage): ?>
                    <span class="pagination-page active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= APP_BASE ?><?= $baseUrl ?><?= $separator ?><?= $pageParam ?>=<?= $i ?>" class="pagination-page"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
                <a href="<?= APP_BASE ?><?= $baseUrl ?><?= $separator ?><?= $pageParam ?>=<?= $totalPages ?>" class="pagination-page"><?= $totalPages ?></a>
            <?php endif; ?>
        </div>
        
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= APP_BASE ?><?= $baseUrl ?><?= $separator ?><?= $pageParam ?>=<?= $currentPage + 1 ?>" class="pagination-link pagination-next">
                <span>Další</span>
                <img src="<?= APP_BASE ?>/public/assets/icons/light-arrow-right.svg" alt="Arrow-Right" width="16" height="16">
            </a>
        <?php else: ?>
            <span class="pagination-link pagination-next disabled">
                <span>Další</span>
                <img src="<?= APP_BASE ?>/public/assets/icons/light-arrow-right.svg" alt="Arrow-Right" width="16" height="16">
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>

