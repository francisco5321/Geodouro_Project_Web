<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

return [
    'id' => 'geoflora-web',
    'name' => $params['appName'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Europe/Lisbon',
    'aliases' => [
        '@webroot' => dirname(__DIR__) . '/web',
        '@web' => '/',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: 'change-me-in-production',
        ],
        'user' => [
            'identityClass' => app\models\ApiIdentity::class,
            'enableAutoLogin' => false,
            'loginUrl' => ['site/login'],
        ],
        'session' => [
            'name' => 'GEOFLORASESSID',
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'formatter' => [
            'class' => yii\i18n\Formatter::class,
            'defaultTimeZone' => 'UTC',
            'timeZone' => 'Europe/Lisbon',
            'locale' => 'pt-PT',
        ],
        'db' => $db,
        'backendApi' => [
            'class' => app\components\BackendApiClient::class,
            'baseUrl' => $params['backendBaseUrl'] ?? '',
            'timeoutSeconds' => (int) ($params['backendTimeoutSeconds'] ?? 15),
            'connectTimeoutSeconds' => (int) ($params['backendConnectTimeoutSeconds'] ?? 2),
        ],
        'backendAuthSession' => [
            'class' => app\components\BackendAuthSession::class,
        ],
        'routePlanApi' => [
            'class' => app\services\RoutePlanApiService::class,
        ],
        'visitTargetApi' => [
            'class' => app\services\VisitTargetApiService::class,
        ],
        'speciesApi' => [
            'class' => app\services\SpeciesApiService::class,
        ],
        'dashboardApi' => [
            'class' => app\services\DashboardApiService::class,
        ],
        'observationApi' => [
            'class' => app\services\ObservationApiService::class,
        ],
        'publicationApi' => [
            'class' => app\services\PublicationApiService::class,
        ],
        'accountApi' => [
            'class' => app\services\AccountApiService::class,
        ],
        'assetManager' => [
            'basePath' => dirname(__DIR__) . '/web/assets',
            'baseUrl' => '/assets',
            'appendTimestamp' => true,
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

