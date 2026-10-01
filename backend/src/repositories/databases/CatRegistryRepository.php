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
    public function findAll(FilterCatForm $form): array
    {
        $query = CatRegistry::find()
            ->with('mother')
            ->orderBy([CatRegistry::tableName() . '.name' => SORT_ASC]);

        $age = $form->age ?? null;
        if (!empty($age)) {
            $query->andWhere([CatRegistry::tableName() . '.age' => $age]);
        }

        $gender = $form->gender ?? null;
        if (!empty($gender)) {
            $query->andWhere([CatRegistry::tableName() . '.gender' => $gender]);
        }

        $limit = $form->limit ?? 20;
        if ($limit < 20 || $limit > 100) {
            $limit = 20;
            $form->limit = 20;
        }

        $query->limit($limit);

        return $query->all();
    }

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
            ->with('mother')
            ->with('fathers')
            ->andWhere([CatRegistry::tableName() . '.id' => $id])
            ->one();
    }

    public function findByNameAndGender(
        int $id,
        string $name,
        string $gender
    ): array {
        $query = CatRegistry::find()
            ->orderBy([CatRegistry::tableName() . '.name' => SORT_ASC])
            ->limit(50);

        if ($id !== 0) {
            $query->andWhere(['!=', CatRegistry::tableName() . '.id', $id]);
        }

        if (!empty($name)) {
            $query->andWhere(['ilike', CatRegistry::tableName() . '.name', $name]);
        }

        if (!empty($gender)) {
            $query->andWhere([CatRegistry::tableName() . '.gender' => $gender]);
        }

        return $query->all();
    }

    public function existsId(int $id): bool
    {
        return CatRegistry::find()
            ->andWhere([CatRegistry::tableName() . '.id' => $id])
            ->exists();
    }

    public function count(): ?int
    {
        return CatRegistry::find()->count();
    }
}