<?php
// Autoloader fallback (permet de se passer de composer au besoin)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Déterminer le chemin de base (BASE_URL) pour une compatibilité absolue
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = dirname($scriptName);
$baseDir = str_replace('\\', '/', $baseDir);
$baseDir = rtrim($baseDir, '/');
define('BASE_URL', $baseDir);

// Analyse de l'URI de la requête
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Nettoyer l'URL par rapport au dossier racine si sous-dossier (ex: /gesclasse/public/)
$url = substr($path, strlen($baseDir));
$url = '/' . trim($url, '/');
if ($url === '') {
    $url = '/';
}

// Chargement des routes
$routes = require_once __DIR__ . '/../routes/web.php';

// Dispatcher simple
$matched = false;
foreach ($routes as $routePath => $handler) {
    if ($url === $routePath) {
        $controllerName = $handler[0];
        $actionName = $handler[1];
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $actionName)) {
                // Récupérer l'identifiant optionnel 'id' depuis la requête GET
                $id = $_GET['id'] ?? null;
                if ($id !== null) {
                    $controller->$actionName($id);
                } else {
                    $controller->$actionName();
                }
                $matched = true;
                break;
            }
        }
    }
}

if (!$matched) {
    header("HTTP/1.0 404 Not Found");
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>404 - Page non trouvée</title>
        <style>
            body { font-family: sans-serif; text-align: center; padding-top: 100px; background-color: #f7f9fa; }
            h1 { color: #e74c3c; font-size: 48px; }
            p { font-size: 18px; color: #555; }
            a { color: #3498db; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>404 Not Found</h1>
        <p>La page demandée n'existe pas ou a été déplacée.</p>
        <p><a href='" . BASE_URL . "/classes'>Retour à l'accueil</a></p>
    </body>
    </html>";
}
