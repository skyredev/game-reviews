<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Game Reviews' ?> </title>
    <link rel="stylesheet" href="<?= APP_BASE ?>/public/css/style.css">
    <script src="<?= APP_BASE ?>/public/js/main.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <?php include __DIR__ . '/partials/header.php'; ?>
        </div>
    </header>

    <main>
        <div class="container">
            <?= $content ?? ''; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <?php include __DIR__ . '/partials/footer.php'; ?>
        </div>
    </footer>
</body>
</html>