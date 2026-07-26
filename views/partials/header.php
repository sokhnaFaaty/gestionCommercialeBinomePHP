<?php
// Contrôleur courant, pour souligner l'onglet actif.
$urlCourante = isset($_GET['url'])
    ? trim($_GET['url'], '/')
    : trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');

$controleurCourant = explode('/', $urlCourante)[0] ?: 'client';

$onglets = [
    'client'   => 'Clients',
    'produit'  => 'Produits',
    'commande' => 'Commandes',
    'facture'  => 'Factures',
    'paiement' => 'Paiements',
];
?>
<header class="border-b border-slate-200">
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-6">

        <a href="<?= path('client', 'index') ?>" class="py-4 text-sm font-semibold tracking-tight text-slate-900">
            Gestion commerciale
        </a>

        <nav class="flex items-center gap-6">
            <?php foreach ($onglets as $controleur => $libelle): ?>
                <?php $actif = $controleur === $controleurCourant; ?>
                <a href="<?= path($controleur, 'index') ?>"
                   class="border-b-2 py-4 text-sm transition-colors
                          <?= $actif
                                ? 'border-slate-900 text-slate-900'
                                : 'border-transparent text-slate-500 hover:text-slate-900' ?>">
                    <?= $libelle ?>
                </a>
            <?php endforeach; ?>
        </nav>

    </div>
</header>
