<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$cookieValidationKey = getenv('COOKIE_VALIDATION_KEY') ?: '';

$config = [
    'id' => 'loan-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => $cookieValidationKey,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'GET health' => 'health/index',
                'POST requests' => 'requests/create',
                'GET processor' => 'processor/index',
            ],
        ],
    ],
    'params' => $params,
];

if ($cookieValidationKey === '') {
    throw new \RuntimeException('COOKIE_VALIDATION_KEY env variable is required.');
}

return $config;
