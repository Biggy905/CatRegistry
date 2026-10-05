<?php

use CatRegistry\applications\exceptions\BadRequestHttpException;

$db = require 'db.php';

return [
    'db' => $db,
    'request' => [
        'cookieValidationKey' => 'IJMmEWMukvTFJBHuchcM5kWvXnqNroCE',
        'parsers' => [
            'application/json' => 'yii\web\JsonParser',
            'application/json; charset=UTF-8' => 'yii\web\JsonParser',
        ],
    ],

    'response' => [
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            if (
                $response->format === \yii\web\Response::FORMAT_JSON
                && $response->data !== null
            ) {
                if ($response->statusCode >= 400 && $response->statusCode <= 499) {
                    $exception = Yii::$app->errorHandler->exception;
                    if ($exception instanceof BadRequestHttpException) {
                        $response->data = [
                            'code' => $response->statusCode,
                            'status' => $response->data['status'],
                            'errors' => $exception->getData(),
                            'name' => $response->data['name'],
                        ];
                    } else {
                        $response->data = [
                            'code' => $response->statusCode,
                            'status' => $response->data['status'],
                            'errors' => $response->data['message'],
                            'name' => $response->data['name'],
                        ];
                    }
                }
            }
        },
    ],
    'cache' => [
        'class' => \yii\caching\DummyCache::class,
    ],
    'errorHandler' => [
        'class' => \CatRegistry\applications\components\ErrorHandler::class,
    ],
    'session' => [
        'class' => \yii\web\Session::class,
        'cookieParams' => [
            'httpOnly' => true,
            'lifetime' => 86400 * 30,
            'secure' => true,
        ],
        'timeout' => 86400 * 30,
        'useCookies' => true,
    ],
    'log' => [
        // @phpstan-ignore ternary.alwaysFalse
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => \yii\log\FileTarget::class,
                'levels' => ['error', 'warning', 'info'],
                'logFile' => Yii::getAlias('@runtime') . '/logs/error.app',
            ],
        ],
    ],
];
