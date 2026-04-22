<?php

return [
    'class' => yii\db\Connection::class,
    'dsn' => getenv('DB_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=Geodouro',
    'username' => getenv('DB_USERNAME') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: '123',
    'charset' => 'utf8',
    'schemaMap' => [
        'pgsql' => [
            'class' => yii\db\pgsql\Schema::class,
            'defaultSchema' => 'public',
        ],
    ],
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
