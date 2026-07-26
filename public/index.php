<?php

// 1. Charger l'autoloader de Composer (qui contient la bibliothèque Dotenv)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Initialiser et charger le fichier .env
// __DIR__ . '/../' signifie qu'on remonte d'un dossier (on sort de 'public' pour aller à la racine)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 3. Définir une constante pour les redirections
define('BASE_URL', 'http://localhost:8000/'); 

