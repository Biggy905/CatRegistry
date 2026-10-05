<?php

$db = require 'db.php';

return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'CatRegistry\applications\controllers\console',
    'controllerMap' => [
        'fixture' => [
            'class' => \yii\console\controllers\FixtureController::class,
            'namespace' => 'CatRegistry\tests\Unit\fixtures',
            'globalFixtures' => [
                \CatRegistry\tests\Unit\fixtures\CatRegistryFixture::class,
                \CatRegistry\tests\Unit\fixtures\CatMaleFixture::class,
            ],
        ],
    ],
    'aliases' => [],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => null,
            'migrationNamespaces' => [
                'CatRegistry\applications\migrations',
            ],
        ],
        'db' => $db,
    ],
];
