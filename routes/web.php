<?php
// =========================================================================
// ROUTEUR DE L'APPLICATION
// -------------------------------------------------------------------------
// Toutes les URL passent ici (voir public/.htaccess et public/index.php).
// Convention : /controleur/action/parametres
//   /client              -> ClientController::index()
//   /client/create       -> ClientController::create()
//   /client/detail/3     -> ClientController::detail(3)
// C'est exactement ce que génèrent les helpers path() et redirectTo().
// =========================================================================

const CONTROLEUR_PAR_DEFAUT = 'client';
const ACTION_PAR_DEFAUT     = 'index';

// 1. Récupérer l'URL demandée
// Avec Apache, le .htaccess la place dans $_GET['url'].
// Avec le serveur intégré de PHP (php -S), on la lit dans REQUEST_URI.
if (isset($_GET['url'])) {
    $url = trim($_GET['url'], '/');
} else {
    $url = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');
}

// 2. Découper l'URL en segments
$segments = $url === '' ? [] : explode('/', $url);

$controleur = $segments[0] ?? CONTROLEUR_PAR_DEFAUT;
$action     = $segments[1] ?? ACTION_PAR_DEFAUT;
$params     = array_slice($segments, 2);

// 3. Construire le nom complet de la classe : client -> App\Controllers\ClientController
$classe = 'App\\Controllers\\' . ucfirst($controleur) . 'Controller';

// 4. Vérifier puis exécuter
if (!class_exists($classe)) {
    http_response_code(404);
    exit("<h1>404</h1><p>Contrôleur introuvable : " . htmlspecialchars($classe) . "</p>");
}

if (!method_exists($classe, $action)) {
    http_response_code(404);
    exit("<h1>404</h1><p>Action introuvable : " . htmlspecialchars($classe . '::' . $action) . "()</p>");
}

call_user_func_array([new $classe(), $action], $params);
