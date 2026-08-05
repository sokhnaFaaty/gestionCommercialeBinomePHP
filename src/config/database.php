<?php

// Les variables du .env sont chargées par phpdotenv (createImmutable) dans $_ENV.
// Attention : getenv() ne fonctionne PAS ici, phpdotenv 5 n'alimente pas putenv() par défaut.
return [
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '5432',
    'db_name'  => $_ENV['DB_NAME'] ?? 'gestion_commerciale_binomephp',
    'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
    'password' => $_ENV['DB_PASSWORD'] ?? 'admin',
    'charset'  => $_ENV['DB_CHARSET'] ?? 'utf8'
];
