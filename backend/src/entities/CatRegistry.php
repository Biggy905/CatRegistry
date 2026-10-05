<?php

namespace CatRegistry\applications\entities;

use CatRegistry\applications\components\AbstractModel;
use CatRegistry\applications\queries\CatRegistryQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $age
 * @property string $gender
 * @property ?int $mother_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 *
 * @property-read CatRegistry $mother
 * @property-read array<CatRegistry> $fathers
 */
final class CatRegistry extends AbstractModel
{
    public static string $tableName = 'cat_registries';

    /**
     * @return CatRegistryQuery
     */
    public static function find(): CatRegistryQuery
    {
        return new CatRegistryQuery(get_called_class())
            ->andWhere([ CatRegistry::tableName() . '.deleted_at' => null]);
    }

    /**
     * @return \yii\db\ActiveQuery<CatRegistry>
     */
    public function getMother(): \yii\db\ActiveQuery
    {
        return $this
            ->hasOne(CatRegistry::class, ['id' => 'mother_id']);
    }

    /**
     * @return \yii\db\ActiveQuery<CatRegistry>
     */
    public function getFathers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(self::class, ['id' => 'father_id'])
            ->viaTable(CatMale::tableName(), ['cat_id' => 'id']);
    }
}
