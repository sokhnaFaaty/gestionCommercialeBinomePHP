<?php

// 1. Charger l'autoloader de Composer (qui contient la bibliothèque Dotenv)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Initialiser et charger le fichier .env
// __DIR__ . '/../' signifie qu'on remonte d'un dossier (on sort de 'public' pour aller à la racine)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 3. Définir une constante pour les redirections
define('BASE_URL', 'http://localhost:8000/');

// 4. Constantes utilisées par les helpers (helpers.php / validate.php)
// ROOT    : chemin physique vers la racine du projet (pour loadView)
// WEBROOT : URL de base pour path() et redirectTo()
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('WEBROOT', BASE_URL);

// 5. Charger les fonctions utilitaires
// On les require ici (et pas seulement via l'autoload.files de composer.json)
// pour que le projet fonctionne meme si le composer.json n'est pas le meme
// d'une machine a l'autre. require_once evite le double chargement.
require_once ROOT . 'app/Helpers/helpers.php';
require_once ROOT . 'app/Helpers/validate.php';

// 6. Démarrer la session (nécessaire pour isConnected(), auth(), hasRole())
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

