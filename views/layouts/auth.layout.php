<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?>Gestion commerciale</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-full items-center justify-center bg-slate-50 px-6 text-slate-700 antialiased">

    <?= $content ?>

</body>
</html>
