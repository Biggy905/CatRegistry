<?php

namespace CatRegistry\applications\components;

use yii\db\ActiveRecord;

abstract class AbstractModel extends ActiveRecord
{
    public static string $tableName = '';

    public static function tableName(): string
    {
        return static::$tableName;
    }
}
