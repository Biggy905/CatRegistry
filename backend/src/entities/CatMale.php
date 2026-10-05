<?php

namespace CatRegistry\applications\entities;

use CatRegistry\applications\components\AbstractModel;
use CatRegistry\applications\queries\CatMaleQuery;

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

    /**
     * @return \yii\db\ActiveQuery<CatRegistry>
     */
    public function getChildren(): \yii\db\ActiveQuery
    {
        return $this->hasMany(CatRegistry::class, ['id' => 'cat_id'])
            ->viaTable(CatMale::tableName(), ['father_id' => 'id']);
    }
}
