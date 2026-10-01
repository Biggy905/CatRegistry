<?php

namespace CatRegistry\applications\entities;

use CatRegistry\applications\components\AbstractModel;
use CatRegistry\applications\queries\CatMaleQuery;
use CatRegistry\applications\queries\CatRegistryQuery;

/**
 * @property int $id
 * @property int $cat_id
 * @property int $father_id
 */
final class CatMale extends AbstractModel
{
    public static string $tableName = 'cat_males';

    public static function find(): CatMaleQuery
    {
        return (new CatMaleQuery(get_called_class()));
    }

    public function getCats(): \yii\db\ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'mother_id']);
    }


}
