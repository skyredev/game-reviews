<?php
/**
 * Main layout template
 * 
 * @file App\Views\Layout
 * @var string $title Page title
 * @var string $content Page content
 */
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Game Reviews' ?> </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_BASE ?>/public/css/style.css">
    <!-- For favicon generation https://realfavicongenerator.net/ website was used -->
    <link rel="icon" type="image/png" href="<?= APP_BASE ?>/public/assets/icons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="<?= APP_BASE ?>/public/assets/icons/favicon.svg">
    <link rel="shortcut icon" href="<?= APP_BASE ?>/public/assets/icons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= APP_BASE ?>/public/assets/icons/apple-touch-icon.png">
    <link rel="manifest" href="<?= APP_BASE ?>/public/assets/icons/site.webmanifest">
    <script defer src="<?= APP_BASE ?>/public/js/carousel.js"></script>
    <script defer src="<?= APP_BASE ?>/public/js/frontend.js"></script>
    <script defer src="<?= APP_BASE ?>/public/js/ajax.js"></script>
    <script defer src="<?= APP_BASE ?>/public/js/validation.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <?php include __DIR__ . '/partials/header.php'; ?>
        </div>
    </header>

    <main>
        <?php if (!empty($fullWidth)): ?>
            <?= $content ?? ''; ?>
        <?php else: ?>
            <div class="container">
                <?= $content ?? ''; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <?php include __DIR__ . '/partials/footer.php'; ?>
        </div>
    </footer>
</body>
</html>