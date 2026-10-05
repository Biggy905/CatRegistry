<?php

// Install
Yii::setAlias(
    '@root',
    dirname(__DIR__, 4)
);

Yii::setAlias(
    '@app',
    sprintf(
        '%s/%s',
        dirname(__DIR__, 3),
        'src'
    )
);

Yii::setAlias(
    '@runtime',
    sprintf(
        '%s/%s',
        dirname(__DIR__),
        'Support/runtime'
    )
);

Yii::setAlias(
    '@vendor',
    sprintf(
        '%s/%s',
        dirname(__DIR__, 3),
        'vendor'
    )
);

Yii::setAlias(
    '@webroot',
    sprintf(
        '%s/%s',
        dirname(__DIR__, 3),
        'src/public'
    )
);

Yii::setAlias(
    '@CatRegistry',
    dirname(__DIR__, 3)
);
