<?php
// =========================================================================
// ROUTEUR DE L'APPLICATION
// -------------------------------------------------------------------------
// Toutes les URL du site passent ici (voir public/index.php, étape 7).
//
// Le principe : on ne devine plus le contrôleur à partir de l'URL.
// On déclare à l'avance, dans routes(), la liste des URL autorisées.
// Une URL absente de cette liste renvoie une 404 : rien d'autre n'est
// joignable depuis le navigateur.
//
// Documentation détaillée : docs/routage.md
// =========================================================================

use App\Controllers\CommandeController;
use App\Controllers\UtilisateurController;

/**
 * LA TABLE DE ROUTES : la liste de toutes les URL du site.
 *
 * Format d'une ligne :
 *     'VERBE chemin' => [Classe du contrôleur, 'méthode à appeler']
 *
 *   VERBE   GET pour afficher une page, POST pour envoyer un formulaire.
 *           Une action déclarée en POST ne peut donc pas être déclenchée
 *           par un simple lien : c'est une sécurité (ex. la suppression).
 *   chemin  Ce qui suit le nom de domaine, sans le « / » du début.
 *   {id}    Un paramètre variable. Sa valeur est passée à la méthode :
 *           l'URL /commande/show/7 appelle CommandeController::show(7).
 *
 * C'est une fonction et pas une simple variable, pour que le menu de
 * navigation puisse lire la même liste — voir navEntries() dans
 * app/Helpers/helpers.php. La table reste ainsi la seule source de vérité.
 */
function routes(): array
{
    return [
        // --- Clients (gérés par UtilisateurController) ---
        'GET utilisateur/index'   => [UtilisateurController::class, 'index'],
        'GET utilisateur/create'  => [UtilisateurController::class, 'create'],
        'POST utilisateur/store'  => [UtilisateurController::class, 'store'],
        'POST utilisateur/delete' => [UtilisateurController::class, 'delete'],

        // --- Commandes ---
        'GET commande/index'      => [CommandeController::class, 'index'],
        'GET commande/create'     => [CommandeController::class, 'create'],
        'GET commande/show/{id}'  => [CommandeController::class, 'show'],
        'GET commande/edit/{id}'  => [CommandeController::class, 'edit'],
        'POST commande/store'     => [CommandeController::class, 'store'],
        'POST commande/update'    => [CommandeController::class, 'update'],
        'POST commande/delete'    => [CommandeController::class, 'delete'],

        // --- À décommenter au fur et à mesure que les écrans sont écrits ---
        // (penser à ajouter le « use App\Controllers\XxxController; » en haut)
        //
        // 'GET produit/index'    => [ProduitController::class, 'index'],
        // 'GET categorie/index'  => [CategorieController::class, 'index'],
        // 'GET facture/index'    => [FactureController::class, 'index'],
        // 'GET paiement/index'   => [PaiementController::class, 'index'],
    ];
}

// -------------------------------------------------------------------------
// 1. Quelle URL a été demandée ?
// -------------------------------------------------------------------------
// Avec Apache, le .htaccess la place dans $_GET['url'].
// Avec le serveur intégré de PHP (php -S), on la lit dans REQUEST_URI.
// parse_url(..., PHP_URL_PATH) enlève au passage le « ?telephone=... »
// d'un formulaire de recherche : il ne fait pas partie du chemin.
$url = isset($_GET['url'])
    ? trim($_GET['url'], '/')
    : trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');

// Deux raccourcis, pour ne pas avoir à déclarer les mêmes routes deux fois :
//   ''            (la racine du site) -> utilisateur/index
//   'commande'    (sans action)       -> commande/index
if ($url === '') {
    $url = 'utilisateur/index';
} elseif (!str_contains($url, '/')) {
    $url .= '/index';
}

// Le menu principal a besoin de savoir sur quelle page on se trouve pour
// souligner l'onglet actif : c'est le routeur qui le lui dit, lui seul lit
// l'URL. Voir currentController() dans helpers.php.
define('URL_COURANTE', $url);

// GET (afficher une page) ou POST (envoyer un formulaire).
$verbe = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// -------------------------------------------------------------------------
// 2. Chercher la route correspondante dans la table
// -------------------------------------------------------------------------
// On compare l'URL demandée à chaque ligne de la table, segment par segment.
// « segment » = un morceau entre deux « / » : commande/show/7 en a trois.
$cible        = null;   // le [Classe, méthode] trouvé
$params       = [];     // les valeurs des {…} extraites de l'URL
$cheminExiste = false;  // le chemin existe, mais peut-être avec un autre verbe

$segmentsUrl = explode('/', $url);

foreach (routes() as $definition => $destination) {
    // 'GET commande/show/{id}' -> $verbeRoute = 'GET', $chemin = 'commande/show/{id}'
    [$verbeRoute, $chemin] = explode(' ', $definition);

    $segmentsRoute = explode('/', $chemin);

    // Pas le même nombre de segments : ce n'est pas cette route, on passe.
    if (count($segmentsRoute) !== count($segmentsUrl)) {
        continue;
    }

    $valeurs    = [];
    $correspond = true;

    foreach ($segmentsRoute as $i => $segment) {
        if (str_starts_with($segment, '{')) {
            // Segment variable ({id}) : on accepte n'importe quelle valeur
            // et on la garde pour la passer à la méthode du contrôleur.
            $valeurs[] = $segmentsUrl[$i];
        } elseif ($segment !== $segmentsUrl[$i]) {
            // Segment fixe qui ne correspond pas : cette route est écartée.
            $correspond = false;
            break;
        }
    }

    if (!$correspond) {
        continue;
    }

    $cheminExiste = true;

    // Le chemin correspond : il ne reste qu'à vérifier le verbe HTTP.
    if ($verbeRoute === $verbe) {
        $cible  = $destination;
        $params = $valeurs;
        break;
    }
}

// -------------------------------------------------------------------------
// 3. Répondre
// -------------------------------------------------------------------------
if ($cible === null) {
    if ($cheminExiste) {
        // L'adresse existe, mais pas avec ce verbe : typiquement une action
        // POST (enregistrer, supprimer) appelée par un simple lien.
        http_response_code(405);
        exit('<h1>405</h1><p>Cette adresse n\'accepte pas la méthode '
             . htmlspecialchars($verbe) . ' : ' . htmlspecialchars($url) . '</p>');
    }

    http_response_code(404);
    exit('<h1>404</h1><p>Page introuvable : ' . htmlspecialchars($url) . '</p>');
}

[$classe, $methode] = $cible;

// Filet de sécurité : la route est déclarée mais le contrôleur n'est pas
// encore écrit (fonctionnalité en cours). Message clair plutôt qu'une
// erreur PHP brute.
if (!class_exists($classe) || !method_exists($classe, $methode)) {
    http_response_code(500);
    exit('<h1>500</h1><p>Route déclarée mais pas encore implémentée : '
         . htmlspecialchars($classe . '::' . $methode) . '()</p>');
}

// 4. Appeler la méthode du contrôleur, avec les paramètres de l'URL.
//    Ex. : (new CommandeController())->show(7)
call_user_func_array([new $classe(), $methode], $params);
