<?php

// Install
Yii::setAlias(
    '@root',
    dirname(__DIR__, 3)
);

Yii::setAlias(
    '@app',
    dirname(__DIR__)
);

Yii::setAlias(
    '@runtime',
    sprintf(
        '%s/%s',
        dirname(__DIR__),
        'runtime'
    )
);

Yii::setAlias(
    '@vendor',
    sprintf(
        '%s/%s',
        dirname(__DIR__, 2),
        'vendor'
    )
);

Yii::setAlias(
    '@webroot',
    sprintf(
        '%s/%s',
        dirname(__DIR__),
        'Public'
    )
);

Yii::setAlias(
    '@CatRegistry',
    dirname(__DIR__, 2)
);
