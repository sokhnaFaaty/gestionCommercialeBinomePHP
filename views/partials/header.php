<?php
use App\Controllers\AuthController;

$utilisateurConnecte = $_SESSION['user'] ?? null;

// Le menu est décrit une seule fois, dans AuthController::menu().
// On n'affiche ici que les entrées déjà développées.
$entrees = $utilisateurConnecte
    ? array_filter(AuthController::menu($utilisateurConnecte['role']), fn($e) => $e['disponible'])
    : [];

// Contrôleur courant, pour souligner l'onglet actif.
$urlCourante = isset($_GET['url'])
    ? trim($_GET['url'], '/')
    : trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');

$controleurCourant = explode('/', $urlCourante)[0] ?: 'auth';
?>
<header class="border-b border-slate-200">
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-6 px-6">

        <a href="<?= path('auth', 'index') ?>" class="py-4 text-sm font-semibold tracking-tight text-slate-900">
            Gestion commerciale
        </a>

        <nav class="flex flex-1 items-center gap-6">
            <?php foreach ($entrees as $entree): ?>
                <?php $actif = $entree['controleur'] === $controleurCourant; ?>
                <a href="<?= path($entree['controleur'], $entree['action']) ?>"
                   class="border-b-2 py-4 text-sm transition-colors
                          <?= $actif
                                ? 'border-slate-900 text-slate-900'
                                : 'border-transparent text-slate-500 hover:text-slate-900' ?>">
                    <?= htmlspecialchars($entree['libelle']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($utilisateurConnecte): ?>
            <div class="flex items-center gap-4 py-4">
                <span class="text-sm text-slate-500">
                    <?= htmlspecialchars($utilisateurConnecte['prenom'] . ' ' . $utilisateurConnecte['nom']) ?>
                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600">
                        <?= htmlspecialchars($utilisateurConnecte['role']) ?>
                    </span>
                </span>

                <form method="post" action="<?= path('auth', 'logout') ?>">
                    <button type="submit" class="text-sm text-slate-500 hover:text-slate-900">
                        Déconnexion
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</header>
