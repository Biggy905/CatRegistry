<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\enums\CatGenderEnums;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\NotFoundHttpException;

final class CreateCatForm extends AbstractForm
{
    public $name;
    public $gender;
    public $age;
    public $mother_id;
    public $father_ids;

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
                ['name', 'gender', 'age'],
                'required',
                'message' => 'Поле «{attribute}» обязательно для заполнения.',
            ],
            [
                'age',
                'integer',
                'min' => 1,
                'max' => 30,
                'message'   => 'Возраст должен быть целым числом.',
                'tooSmall'  => 'Возраст не может быть меньше {min}.',
                'tooBig'    => 'Возраст не может быть больше {max}.',
            ],
            [
                'name',
                'string',
                'min' => 3,
                'max' => 15,
                'message'  => 'Кличка должна быть строкой.',
                'tooShort' => 'Кличка должна содержать не менее {min} символов.',
                'tooLong'  => 'Кличка должна содержать не более {max} символов.',
            ],
            [
                'gender',
                'in',
                'range' => CatGenderEnums::toArray(),
                'message' => 'Пол должен быть одним из: мужского или женского.',
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

    public function validateMotherId(): void
    {
        if (!empty($this->mother_id) && is_int($this->mother_id)) {
            $exists = $this->catRegistryRepository->existsId($this->mother_id);
            if (!$exists) {
                throw new NotFoundHttpException('Запись не найдена');
            }

            if (is_int($this->mother_id)) {
                $motherCat = $this->catRegistryRepository->findId($this->mother_id);
                if ($this->age > $motherCat->age) {
                    $this->addError('age', 'Возраст матери должен быть больше возраста котёнка');
                }
            }
        } else {
            $this->addError('mother_id', 'Неверное значение атрибута');
        }
    }

    public function validateFatherIds(): void
    {
        if (is_array($this->father_ids)) {
            foreach ($this->father_ids as $id) {
                $exists = $this->catRegistryRepository->existsId($id);
                if (!$exists) {
                    throw new NotFoundHttpException('Запись не найдена');
                }
            }
        }
    }
}
