<section class="games-page">
    <div class="page-header">
        <h1>Seznam her</h1>
        <?php if (isLoggedIn()): ?>
            <a href="<?= APP_BASE ?>/games/create" class="btn">Přidat hru</a>
        <?php endif; ?>
    </div>
    
    <p>Zde bude seznam všech her s možností filtrování a řazení.</p>
</section>