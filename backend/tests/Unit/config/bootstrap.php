<?php

declare(strict_types=1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once dirname(__DIR__, 3) . '/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__, 3) . '/vendor/autoload.php';
require __DIR__ . '/aliases.php';

use Dotenv\Dotenv;

$rootPath = Yii::getAlias('@root');

if ($rootPath === false) {
    throw new RuntimeException('Не удалось разрешить алиасы @root или @app');
}

(Dotenv::createUnsafeImmutable(
    $rootPath,
    ['.env'],
    false
))->load();
