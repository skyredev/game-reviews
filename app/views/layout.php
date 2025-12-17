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
    <script defer src="<?= APP_BASE ?>/public/js/main.js"></script>
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