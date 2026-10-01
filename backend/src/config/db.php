<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'pgsql:host=' . getenv('DB_HOSTNAME') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
    'tablePrefix' => getenv('DB_PREFIX_TABLE'),
    'schemaMap' => [
        'pgsql' => [
            'class' => \yii\db\pgsql\Schema::class,
            'defaultSchema' => 'public',
        ],
    ],
    'on afterOpen' => static function (\yii\base\Event $event) {
        $event->sender->createCommand("SET time zone 'UTC'")->execute();
    },
    'attributes' => [
        PDO::ATTR_PERSISTENT => getenv('DB_PERSISTENT') ?? true,
    ],
];
