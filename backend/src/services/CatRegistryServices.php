<?php

namespace CatRegistry\applications\services;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\applications\forms\SearchCatForm;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\groups\CatFilterListGroup;
use CatRegistry\applications\groups\CatListGroup;
use CatRegistry\applications\groups\CatSelectItemGroup;
use CatRegistry\applications\groups\SearchCatListGroup;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use Yii;
use DateTimeImmutable;

final class CatRegistryServices
{
    public function __construct(
        private readonly CatRegistryRepositoryInterface $catRegistryRepository
    ) {

    }
    public function item(IdCatForm $form): array
    {
        return [
            new CatSelectItemGroup(
                $this->catRegistryRepository->findId($form->id)
            )->toArray(),
        ];
    }

    public function list(FilterCatForm $form): array
    {
        $cats = new CatFilterListGroup(
            $this->catRegistryRepository->findAll($form)
        )->toArray();

        $total = $this->catRegistryRepository->countForFilter($form);

        return [
            'items' => $cats,
            'total' => $total ?? 0,
            'page' => $form->page,
            'limit' => $form->limit,
        ];
    }

    public function search(SearchCatForm $form): array
    {
        return [
            'items' => new SearchCatListGroup(
                $this->catRegistryRepository->findByNameAndGender(
                    id: $form->exclude_cat_id,
                    name: $form->name,
                    gender: $form->gender,
                )
            )->toArray(),
        ];
    }

    public function insert(CreateCatForm $form): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $catRegistry = new CatRegistry();
            $catRegistry->name = $form->name;
            $catRegistry->age = $form->age;
            $catRegistry->gender = $form->gender;
            $catRegistry->mother_id = $form->mother_id ?? null;
            $catRegistry->created_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $this->catRegistryRepository->create($catRegistry);
            if (!empty($form->father_ids) && is_array($form->father_ids)) {
                $fathers = CatRegistry::find()
                    ->where(['id' => $form->father_ids])
                    ->all();

                foreach ($fathers as $father) {
                    $catRegistry->link('fathers', $father);
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function update(UpdateCatForm $form): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $catRegistry = $this->catRegistryRepository->findId($form->id);
            $catRegistry->name = $form->name;
            $catRegistry->age = $form->age;
            $catRegistry->gender = $form->gender;
            $catRegistry->mother_id = $form->mother_id ?? null;
            $catRegistry->updated_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $this->catRegistryRepository->update($catRegistry);

            $catRegistry->unlinkAll('fathers', true);
            if (!empty($form->father_ids) && is_array($form->father_ids)) {
                $fathers = CatRegistry::find()
                    ->where(['id' => $form->father_ids])
                    ->all();

                foreach ($fathers as $father) {
                    $catRegistry->link('fathers', $father);
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function delete(IdCatForm $form): void
    {

    }
}
