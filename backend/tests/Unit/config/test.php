<?php

$modules =  'modules.php';
$components = require 'components.php';
$singletons = require 'singletons.php';
$definitions = require 'definitions.php';

return [
    'id' => 'test',
    'name' => 'Cat Test',
    'basePath' => Yii::getAlias('@app'),
    'controllerNamespace' => 'CatRegistry\applications\controllers',
    'language' => getenv('APP_LANGUAGE'),
    'bootstrap' => [],
    'vendorPath' => Yii::getAlias('@vendor'),
    'modules' => [],
    'components' => $components,
    'container' => [
        'singletons' => $singletons,
        'definitions' => $definitions,
    ],
];
