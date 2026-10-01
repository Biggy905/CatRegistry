<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__) . '/config/aliases.php';

use yii\web\Application;
use Dotenv\Dotenv;

$rootPath = Yii::getAlias('@root');
$appPath = Yii::getAlias('@app');

(Dotenv::createUnsafeImmutable(
    $rootPath,
    ['.env'],
    false
))->load();

$config = require $appPath . '/config/rest.php';

(new Application($config))->run();