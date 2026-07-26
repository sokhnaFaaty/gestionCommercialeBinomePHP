<header class="border-b border-slate-200">
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-6 px-6">

        <a href="<?= path('utilisateur', 'index') ?>"
           class="py-4 text-sm font-semibold tracking-tight text-slate-900">
            Gestion commerciale
        </a>

        <nav class="flex items-center gap-6">
            <?php foreach (navEntries() as [$libelle, $controleur]): ?>
                <a href="<?= path($controleur, 'index') ?>"
                   class="border-b-2 py-4 text-sm transition-colors
                          <?= $controleur === currentController()
                                ? 'border-slate-900 text-slate-900'
                                : 'border-transparent text-slate-500 hover:text-slate-900' ?>">
                    <?= htmlspecialchars($libelle) ?>
                </a>
            <?php endforeach; ?>
        </nav>

    </div>
</header>
