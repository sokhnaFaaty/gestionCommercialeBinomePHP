<?php

return [
    'host'     => getenv('DB_HOST'),
    'port'     => getenv('DB_PORT') ?: '5432',
    'db_name'  => getenv('DB_NAME'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset'  => getenv('DB_CHARSET') ?: 'utf8'
];
