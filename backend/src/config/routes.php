<?php

return [
    /** @see \CatRegistry\applications\controllers\IndexController */
    [
        'verb' => ['get'],
        'pattern' => '/',
        'route' => 'index/index',
    ],
    /** @see \CatRegistry\applications\controllers\CatController */
    [
        'verb' => ['get'],
        'pattern' => '/cat/<id>',
        'route' => 'cat/item',
        'defaults' => [
            'id' => 1
        ],
    ],
    [
        'verb' => ['get'],
        'pattern' => '/cats',
        'route' => 'cat/list',
    ],
    [
        'verb' => ['get'],
        'pattern' => '/cats/search',
        'route' => 'cat/search',
    ],
    [
        'verb' => ['post'],
        'pattern' => '/cats',
        'route' => 'cat/create',
    ],
    [
        'verb' => ['put'],
        'pattern' => '/cats/<id>',
        'route' => 'cat/update',
    ],
    [
        'verb' => ['delete'],
        'pattern' => '/cats/<id>',
        'route' => 'cat/delete',
    ],
];
