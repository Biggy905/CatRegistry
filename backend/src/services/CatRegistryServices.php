<?php

namespace CatRegistry\applications\services;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\applications\forms\SearchCatForm;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\groups\CatFilterListGroup;
use CatRegistry\applications\groups\CatSelectItemGroup;
use CatRegistry\applications\groups\SearchCatListGroup;
use CatRegistry\applications\policies\CatKinshipPolicy;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use Yii;
use DateTimeImmutable;
use Throwable;

final readonly class CatRegistryServices
{
    public function __construct(
        private CatRegistryRepositoryInterface $catRegistryRepository,
        private CatKinshipPolicy $catKinshipPolicy,
    ) {

    }

    public function item(IdCatForm $form): array
    {
        $catRegistry = $this->catRegistryRepository->findId($form->id);
        if ($catRegistry === null) {
            throw new NotFoundHttpException('Запись не найдена');
        }

        return new CatSelectItemGroup($catRegistry)->toArray();
    }

    public function list(FilterCatForm $form): array
    {
        $cats = new CatFilterListGroup(
            $this->catRegistryRepository->findAll($form)
        )->toArray();

        $total = $this->catRegistryRepository->countForFilter($form);

        return [
            'total' => $total ?? 0,
            'page' => $form->page,
            'limit' => $form->limit,
            'items' => $cats,
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

    /**
     * @throws \DateMalformedStringException
     * @throws Throwable
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function insert(CreateCatForm $form): CatSelectItemGroup
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->catKinshipPolicy->ensureCanAssignParents(
                catId: null,
                motherId: $form->mother_id,
                fatherIds: $form->father_ids ?? []
            );

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
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return new CatSelectItemGroup($catRegistry);
    }

    public function update(UpdateCatForm $form): CatSelectItemGroup
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->catKinshipPolicy->ensureCanAssignParents(
                catId: $form->id,
                motherId: $form->mother_id,
                fatherIds: $form->father_ids ?? [],
            );

            $catRegistry = $this->catRegistryRepository->findId($form->id);
            if ($catRegistry === null) {
                throw new NotFoundHttpException('Запись не найдена');
            }

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
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return new CatSelectItemGroup($catRegistry);
    }

    public function delete(IdCatForm $form): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $catRegistry = $this->catRegistryRepository->findId($form->id);
            if ($catRegistry === null) {
                throw new NotFoundHttpException('Запись не найдена');
            }

            $this->catRegistryRepository->delete($catRegistry);

            $catRegistry->unlinkAll('fathers', true);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
