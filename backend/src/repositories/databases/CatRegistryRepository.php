<?php

namespace CatRegistry\applications\repositories\databases;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\queries\CatRegistryQuery;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use LogicException;
use DateTimeImmutable;

final class CatRegistryRepository implements CatRegistryRepositoryInterface
{
    public function findAll(FilterCatForm $form): array
    {
        return $this->queryFilter($form)->all();
    }

    public function countForFilter(FilterCatForm $form): ?int
    {
        $count = $this->queryFilter($form)->count();

        return !empty($count) ? (int) $count : null;
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

    public function findId(int $id): ?CatRegistry
    {
        return CatRegistry::find()
            ->with('mother')
            ->with('fathers')
            ->andWhere([CatRegistry::tableName() . '.id' => $id])
            ->one();
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $gender
     * @return CatRegistry[]
     */
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

    private function queryFilter(FilterCatForm $form): CatRegistryQuery
    {
        $query = CatRegistry::find()
            ->with('mother')
            ->orderBy([CatRegistry::tableName() . '.name' => SORT_ASC]);

        /** @var ?array<int, int> $age */
        $age = $form->age ?? null;
        if (!empty($age)) {
            [$min, $max] = array_map('intval', $age);
            if ($min > $max) {
                [$min, $max] = [$max, $min];
            }

            $query->andWhere(['between', CatRegistry::tableName() . '.age', $min, $max]);
        }

        /** @var ?string $gender */
        $gender = $form->gender ?? null;
        if (!empty($gender)) {
            $query->andWhere([CatRegistry::tableName() . '.gender' => $gender]);
        }

        /** @var int $limit */
        $limit = $form->limit ?? 10;
        if ($limit < 10 || $limit > 100) {
            $limit = 10;
            $form->limit = 10;
        }

        /** @var int $page */
        $page = $form->page ?? 1;
        if ($page) {
            $query->limit($limit);
            $query->offset($page * $limit - $limit);
        }

        return $query;
    }
}