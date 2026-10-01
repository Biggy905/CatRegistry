<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\enums\CatGenderEnums;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\NotFoundHttpException;

final class UpdateCatForm extends AbstractForm
{
    public $id;
    public $name;
    public $gender;
    public $age;
    public $id_mother;
    public $id_fathers;

    public function __construct(
        private readonly CatRegistryRepositoryInterface $catRegistryRepository,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [
                ['id', 'name', 'gender', 'age'],
                'required',
            ],
            [
                'id',
                'validateId',
            ],
            [
                'age',
                'integer',
                'min' => 1,
                'max' => 30,
            ],
            [
                'name',
                'string',
                'min' => 3,
                'max' => 15,
            ],
            [
                'gender',
                'in',
                'range' => CatGenderEnums::toArray(),
            ],
            [
                'mother_id',
                'validateMotherId',
            ],
            [
                'father_ids',
                'validateFatherIds',
            ],
        ];
    }

    public function validateId(): void
    {
        $exists = $this->catRegistryRepository->existsId($this->id);
        if (!$exists) {
            throw new NotFoundHttpException('Запись не найдена');
        }
    }

    public function validateMotherId(): void
    {
        $exists = $this->catRegistryRepository->existsId($this->mother_id);
        if (!$exists) {
            throw new NotFoundHttpException('Запись не найдена');
        }
    }

    public function validateFatherIds(): void
    {
        foreach ($this->father_ids as $id) {
            $exists = $this->catRegistryRepository->existsId($id);
            if (!$exists) {
                throw new NotFoundHttpException('Запись не найдена');
            }
        }
    }
}
