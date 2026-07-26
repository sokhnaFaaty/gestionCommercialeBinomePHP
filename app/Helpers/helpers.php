<?php

function dd($test): void {
    echo "<pre>";
    var_dump($test);
    echo "</pre>";
    die();
}

function loadView(string $view, array $datas = [], string $layout = "base") {
    ob_start();
    extract($datas);
    require(ROOT . "views/" . $view . ".php");
    $content = ob_get_clean();
    require(ROOT . "views/layouts/" . $layout . ".layout.php");
}

function path(string $controller, string $action, array $params = []): string {
    $url = WEBROOT . $controller . '/' . $action;
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

function redirectTo(string $controller, string $action, array $params = []): void {
    $url = WEBROOT . "$controller/$action";
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    header('Location:' . $url);
    exit();
}

/**
 * Onglets du menu principal, pour views/partials/header.php.
 *
 * On ne garde que ceux dont la route « GET xxx/index » est déclarée dans
 * routes() (routes/web.php) : tant qu'un écran n'est pas routé, son onglet
 * n'apparaît pas, et on évite un lien qui mènerait à une 404.
 *
 * Chaque entrée est un couple [libellé affiché, nom du contrôleur].
 */
function navEntries(): array {
    $onglets = [
        ['Clients',    'utilisateur'],
        ['Produits',   'produit'],
        ['Commandes',  'commande'],
        ['Catégories', 'categorie'],
        ['Factures',   'facture'],
        ['Paiements',  'paiement'],
    ];

    $routesDeclarees = routes();

    $visibles = array_filter(
        $onglets,
        fn(array $onglet): bool => isset($routesDeclarees['GET ' . $onglet[1] . '/index'])
    );

    // array_values() renumérote les clés après le filtrage.
    return array_values($visibles);
}

/**
 * Nom du contrôleur de la page affichée ('utilisateur', 'commande', ...),
 * pour souligner l'onglet actif dans le menu.
 *
 * URL_COURANTE est définie par le routeur : lui seul lit l'URL, la vue se
 * contente de lire le résultat.
 */
function currentController(): string {
    if (!defined('URL_COURANTE')) {
        return '';
    }

    return explode('/', URL_COURANTE)[0];
}

function isConnected(): bool {
    return isset($_SESSION["user"]);
}

function auth(): void {
    if (!isConnected()) {
        redirectTo("auth", "login");
    }
}

function hasRole(string $role): bool {
    if (!isConnected()) return false;
    return $_SESSION["user"]["role"] === $role;
}
