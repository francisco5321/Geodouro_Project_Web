<?php

return [
    'class' => yii\db\Connection::class,
    'dsn' => getenv('DB_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=geoflora',
    'username' => getenv('DB_USERNAME') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: 'postgres',
    'charset' => 'utf8',
    'schemaMap' => [
        'pgsql' => [
            'class' => yii\db\pgsql\Schema::class,
            'defaultSchema' => 'public',
        ],
    ],
];

