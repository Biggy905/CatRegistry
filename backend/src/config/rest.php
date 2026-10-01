<?php

$modules = require 'modules.php';
$components = require 'components.php';
$singletons = require 'singletons.php';
$definitions = require 'definitions.php';


return [
    'id' => getenv('APP_ID'),
    'name' => getenv('APP_NAME'),
    'basePath' => Yii::getAlias('@app'),
    'controllerNamespace' => 'CatRegistry\applications\controllers',
    'language' => getenv('APP_LANGUAGE'),
    'bootstrap' => [
        //'log',
    ],
    'vendorPath' => Yii::getAlias('@vendor'),
    'modules' => $modules,
    'components' => $components,
    'container' => [
        'singletons' => $singletons,
        'definitions' => $definitions,
    ],
];
