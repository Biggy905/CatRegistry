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

    /**
     * @param IdCatForm $form
     * @return array<int|string, mixed>
     * @throws NotFoundHttpException
     */
    public function item(IdCatForm $form): array
    {
        /** @var int $id */
        $id = $form->id;
        $catRegistry = $this->catRegistryRepository->findId($id);
        if (empty($catRegistry)) {
            throw new NotFoundHttpException('Запись не найдена');
        }

        return new CatSelectItemGroup($catRegistry)->toArray();
    }

    /**
     * @param FilterCatForm $form
     * @return array<string, mixed>
     */
    public function list(FilterCatForm $form): array
    {
        $list = $this->catRegistryRepository->findAll($form);
        $cats = new CatFilterListGroup(
            $list
        )->toArray();

        $total = $this->catRegistryRepository->countForFilter($form);

        return [
            'total' => $total ?? 0,
            'page' => $form->page,
            'limit' => $form->limit,
            'items' => $cats,
        ];
    }

    /**
     * @param SearchCatForm $form
     * @return array<string, mixed>
     */
    public function search(SearchCatForm $form): array
    {
        /** @var int $excludedCatId */
        $excludedCatId = $form->exclude_cat_id;
        /** @var string $name */
        $name = $form->name;
        /** @var string $gender */
        $gender = $form->gender;
        return [
            'items' => new SearchCatListGroup(
                $this->catRegistryRepository->findByNameAndGender(
                    id: $excludedCatId,
                    name: $name,
                    gender: $gender,
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
            /** @var string $name */
            $name = $form->name;
            /** @var int $age */
            $age = $form->age;
            /** @var string $gender */
            $gender = $form->gender;
            /** @var ?int $motherId */
            $motherId = $form->mother_id ?? null;
            /** @var array<int> $fatherIds */
            $fatherIds = $form->father_ids ?? [];

            $this->catKinshipPolicy->ensureCanAssignParents(
                catId: null,
                motherId: $motherId,
                fatherIds: $fatherIds
            );

            $catRegistry = new CatRegistry();
            $catRegistry->name = $name;
            $catRegistry->age = $age;
            $catRegistry->gender = $gender;
            $catRegistry->mother_id = $motherId;
            $catRegistry->created_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $this->catRegistryRepository->create($catRegistry);
            if (!empty($fatherIds)) {
                /** @var CatRegistry[] $fathers */
                $fathers = CatRegistry::find()
                    ->where(['id' => $fatherIds])
                    ->all();

                foreach ($fathers as $father) {
                    /** @var CatRegistry $father */
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
            /** @var int $catId */
            $catId = $form->id;
            /** @var string $name */
            $name = $form->name;
            /** @var int $age */
            $age = $form->age;
            /** @var string $gender */
            $gender = $form->gender;
            /** @var ?int $motherId */
            $motherId = $form->mother_id ?? null;
            /** @var array<int> $fatherIds */
            $fatherIds = $form->father_ids ?? [];

            $this->catKinshipPolicy->ensureCanAssignParents(
                catId: $catId,
                motherId: $motherId,
                fatherIds: $fatherIds
            );

            $catRegistry = $this->catRegistryRepository->findId($catId);
            if ($catRegistry === null) {
                throw new NotFoundHttpException('Запись не найдена');
            }

            $catRegistry->name = $name;
            $catRegistry->age = $age;
            $catRegistry->gender = $gender;
            $catRegistry->mother_id = $motherId;
            $catRegistry->updated_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $this->catRegistryRepository->update($catRegistry);

            $catRegistry->unlinkAll('fathers', true);
            if (!empty($fatherIds)) {
                /** @var CatRegistry[] $fathers */
                $fathers = CatRegistry::find()
                    ->where(['id' => $fatherIds])
                    ->all();

                foreach ($fathers as $father) {
                    /** @var CatRegistry $father */
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
            /** @var int $catId */
            $catId = $form->id;

            $catRegistry = $this->catRegistryRepository->findId($catId);
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
