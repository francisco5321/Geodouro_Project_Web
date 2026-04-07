<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

return [
    'id' => 'geoflora-web',
    'name' => $params['appName'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@webroot' => dirname(__DIR__) . '/web',
        '@web' => '/',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: 'change-me-in-production',
        ],
        'user' => [
            'identityClass' => app\models\AppUser::class,
            'enableAutoLogin' => false,
            'loginUrl' => ['site/login'],
        ],
        'session' => [
            'name' => 'GEOFLORASESSID',
        ],
        'db' => $db,
        'assetManager' => [
            'basePath' => dirname(__DIR__) . '/web/assets',
            'baseUrl' => '/assets',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
    ],
    'params' => $params,
];
