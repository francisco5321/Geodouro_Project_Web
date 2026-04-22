<?php

$db = require __DIR__ . '/db.php';
$params = require __DIR__ . '/params.php';

return [
    'id' => 'geoflora-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\\commands',
    'bootstrap' => ['log'],
    'components' => [
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'db' => $db,
        'log' => [
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
