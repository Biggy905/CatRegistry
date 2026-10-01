<?php

namespace CatRegistry\applications\repositories;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\FilterCatForm;
use yii\db\Exception;

interface CatRegistryRepositoryInterface
{
    /**
     * @return array<int, mixed>
     */
    public function findAll(FilterCatForm $form): array;

    /**
     * @throws Exception
     */
    public function create(CatRegistry $catRegistry): void;

    public function update(CatRegistry $catRegistry): void;

    public function delete(CatRegistry $catRegistry): void;

    public function findId(int $id): CatRegistry;

    public function findByNameAndGender(
        int $id,
        string $name,
        string $gender
    ): array;

    public function existsId(int $id): bool;

    public function countForFilter(FilterCatForm $form): ?int;
}