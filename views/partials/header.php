<?php
// Entrées de navigation.
// Une entrée ne s'affiche que si son contrôleur et son action existent déjà :
// on évite ainsi les liens qui mèneraient à une 404. Elles apparaîtront
// d'elles-mêmes au fur et à mesure que les fonctionnalités seront écrites.
$onglets = [
    ['Clients',    'utilisateur', 'index'],
    ['Produits',   'produit',     'index'],
    ['Commandes',  'commande',    'index'],
    ['Catégories', 'categorie',   'index'],
    ['Factures',   'facture',     'index'],
    ['Paiements',  'paiement',    'index'],
];

$entrees = array_filter($onglets, function (array $onglet): bool {
    $classe = 'App\\Controllers\\' . ucfirst($onglet[1]) . 'Controller';

    return class_exists($classe) && method_exists($classe, $onglet[2]);
});

// Contrôleur courant, pour souligner l'onglet actif.
$urlCourante = isset($_GET['url'])
    ? trim($_GET['url'], '/')
    : trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');

$controleurCourant = explode('/', $urlCourante)[0] ?: 'utilisateur';
?>
<header class="border-b border-slate-200">
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-6 px-6">

        <a href="<?= path('utilisateur', 'index') ?>"
           class="py-4 text-sm font-semibold tracking-tight text-slate-900">
            Gestion commerciale
        </a>

        <nav class="flex items-center gap-6">
            <?php foreach ($entrees as [$libelle, $controleur, $action]): ?>
                <?php $actif = $controleur === $controleurCourant; ?>
                <a href="<?= path($controleur, $action) ?>"
                   class="border-b-2 py-4 text-sm transition-colors
                          <?= $actif
                                ? 'border-slate-900 text-slate-900'
                                : 'border-transparent text-slate-500 hover:text-slate-900' ?>">
                    <?= htmlspecialchars($libelle) ?>
                </a>
            <?php endforeach; ?>
        </nav>

    </div>
</header>
