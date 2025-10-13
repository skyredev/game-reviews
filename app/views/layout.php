<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Game Reviews' ?> </title>
    <link rel="stylesheet" href="<?= APP_BASE ?>/public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="main-content">
        <?= $content ?? ''; ?>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>