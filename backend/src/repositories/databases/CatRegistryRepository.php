<?php

namespace CatRegistry\applications\repositories\databases;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\db\Exception;
use LogicException;
use DateTimeImmutable;

final class CatRegistryRepository implements CatRegistryRepositoryInterface
{
    /**
     * @return array<int, mixed>
     */
    public function findAll(FilterCatForm $form): array
    {
        $query = CatRegistry::find();

        if (!empty($filters['gender'])) {
            $query->andWhere(['gender' => $filters['gender']]);
        }
        if (isset($filters['age_from'])) {
            $query->andWhere(['>=', 'age', (int)$filters['age_from']]);
        }
        if (isset($filters['age_to'])) {
            $query->andWhere(['<=', 'age', (int)$filters['age_to']]);
        }

        return $query
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }

    /**
     * @throws Exception
     */
    public function create(CatRegistry $catRegistry): void
    {
        if (!$catRegistry->save()) {
            throw new LogicException('Не удалось создать сущность');
        }
    }

    public function update(CatRegistry $catRegistry): void
    {
        if (!$catRegistry->save()) {
            throw new LogicException('Не удалось обновить сущность');
        }
    }

    public function delete(CatRegistry $catRegistry): void
    {
        $catRegistry->deleted_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        if (!$catRegistry->save()) {
            throw new LogicException('Не удалось удалить сущность');
        }
    }

    public function findId(int $id): CatRegistry
    {
        return CatRegistry::find()
            ->andWhere(['id' => $id])
            ->one();
    }

    public function existsId(int $id): bool
    {
        return CatRegistry::find()
            ->andWhere(['id' => $id])
            ->exists();
    }
}