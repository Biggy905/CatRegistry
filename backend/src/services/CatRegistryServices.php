<?php

namespace CatRegistry\applications\services;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\repositories\databases\CatRegistryRepository;
use Yii;
use DateTimeImmutable;

final class CatRegistryServices
{
    public function __construct(
        private CatRegistryRepository $catRegistryRepository
    ) {

    }
    public function item(IdCatForm $form): array
    {
        return [];
    }

    public function list(FilterCatForm $form): array
    {
        return [];
    }

    public function insert(CreateCatForm $form): array
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $catRegitry = new CatRegistry();
            $catRegitry->name = $form->name;
            $catRegitry->age = $form->age;
            $catRegitry->gender = $form->gender;
            $catRegitry->mother_id = $form->mother_id ?? null;
            $catRegitry->created_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $this->catRegistryRepository->create($catRegitry);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function update(UpdateCatForm $form): array
    {
        return [];
    }

    public function delete(IdCatForm $form): array
    {
        return [];
    }
}
