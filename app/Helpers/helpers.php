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
    // [libellé affiché, nom du contrôleur, rôle qui voit l'onglet]
    $onglets = [
        ['Clients',        'utilisateur', ROLE_GESTIONNAIRE],
        ['Produits',       'produit',     ROLE_GESTIONNAIRE],
        ['Commandes',      'commande',    ROLE_GESTIONNAIRE],
        ['Catégories',     'categorie',   ROLE_GESTIONNAIRE],
        ['Factures',       'facture',     ROLE_GESTIONNAIRE],
        ['Paiements',      'paiement',    ROLE_GESTIONNAIRE],
        ['Mes commandes',  'espace',      ROLE_CLIENT],
    ];

    $routesDeclarees = routes();

    $visibles = array_filter(
        $onglets,
        fn(array $onglet): bool => hasRole($onglet[2])
                                   && isset($routesDeclarees['GET ' . $onglet[1] . '/index'])
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

// =========================================================================
// AUTHENTIFICATION
// -------------------------------------------------------------------------
// Les deux rôles de la table utilisateur. Ce sont les mêmes valeurs que
// UtilisateurModel::ROLE_GESTIONNAIRE et ROLE_CLIENT ; on les redéclare ici
// en constantes parce que ce fichier n'est pas dans un namespace et que les
// vues s'en servent aussi.
// =========================================================================

const ROLE_GESTIONNAIRE = 'gestionnaire';
const ROLE_CLIENT       = 'client';

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

/**
 * Informations de la personne connectée, ou null si personne ne l'est.
 * Contient id, nom, prenom, email et role (voir AuthController::authenticate()).
 */
function utilisateurConnecte(): ?array {
    return $_SESSION["user"] ?? null;
}

/**
 * Barrière d'entrée des écrans de gestion.
 *
 * Appelée dans le constructeur des contrôleurs concernés : le routeur crée
 * l'objet avant d'appeler la méthode, donc toutes les actions du contrôleur
 * sont protégées d'un coup, sans répéter le contrôle dans chacune.
 */
function authGestionnaire(): void {
    auth();   // personne n'est connecté -> page de connexion

    if (!hasRole(ROLE_GESTIONNAIRE)) {
        // Un client connecté n'a rien à faire ici : on le renvoie chez lui
        // plutôt que vers la page de connexion, où il est déjà passé.
        redirectTo("espace", "index");
    }
}

/**
 * Barrière d'entrée de l'espace client.
 */
function authClient(): void {
    auth();

    if (!hasRole(ROLE_CLIENT)) {
        redirectTo("utilisateur", "index");
    }
}
