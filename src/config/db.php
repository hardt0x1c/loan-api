<?php

[$dbHost, $dbPort, $dbName, $dbUser, $dbPassword] = [
    getenv('DB_HOST') ?: 'postgres',
    getenv('DB_PORT') ?: '5432',
    getenv('DB_NAME') ?: 'loans',
    getenv('DB_USER') ?: 'user',
    getenv('DB_PASSWORD') ?: 'password',
];

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf('pgsql:host=%s;port=%s;dbname=%s', $dbHost, $dbPort, $dbName),
    'username' => $dbUser,
    'password' => $dbPassword,
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
