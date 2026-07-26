<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?>Gestion commerciale</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-white text-slate-700 antialiased">

    <?php require ROOT . 'views/partials/header.php'; ?>

    <main class="mx-auto w-full max-w-5xl px-6 py-10">
        <?= $content ?>
    </main>

    <?php require ROOT . 'views/partials/footer.php'; ?>

</body>
</html>
