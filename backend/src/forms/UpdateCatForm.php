<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\enums\CatGenderEnums;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\NotFoundHttpException;

final class UpdateCatForm extends AbstractForm
{
    /** @var ?mixed $id */
    public $id;
    /** @var ?mixed $name */
    public $name;
    /** @var ?mixed $gender */
    public $gender;
    /** @var ?mixed $age */
    public $age;
    /** @var ?mixed $mother_id */
    public $mother_id;
    /** @var mixed $father_ids */
    public $father_ids;

    /**
     * @param CatRegistryRepositoryInterface $catRegistryRepository
     * @param array<mixed> $attributes
     * @param array<mixed> $config
     */
    public function __construct(
        private readonly CatRegistryRepositoryInterface $catRegistryRepository,
        array $attributes = [],
        $config = [],
    ) {
        parent::__construct($attributes, $config);
    }

    public function rules(): array
    {
        return [
            [
                ['name', 'gender', 'age'],
                'required',
            ],
            [
                'id',
                'required',
            ],
            [
                'id',
                'integer',
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

    public function validateId(): void
    {
        if (is_int($this->id)) {
            $exists = $this->catRegistryRepository->existsId($this->id);
            if (!$exists) {
                throw new NotFoundHttpException('Запись не найдена');
            }
        }

        if (!is_int($this->id)) {
            $this->addError('id', 'Идентификатор должен быть целым числом.');
        }
    }

    public function validateMotherId(): void
    {
        if (!empty($this->mother_id) && is_int($this->mother_id)) {
            $exists = $this->catRegistryRepository->existsId($this->mother_id);
            if (!$exists) {
                throw new NotFoundHttpException('Запись не найдена');
            }

            $motherCat = $this->catRegistryRepository->findId($this->mother_id);
            if ($motherCat === null) {
                throw new NotFoundHttpException('Запись не найдена');
            }

            if ($this->age > $motherCat->age) {
                $this->addError('age', 'Возраст матери должен быть больше возраста котёнка');
            }
        } else {
            $this->addError('mother_id', 'Неверное значение атрибута');
        }
    }

    public function validateFatherIds(): void
    {
        if (is_array($this->father_ids)) {
            foreach ($this->father_ids as $id) {
                if (!is_int($id)) {
                    $this->addError('father_ids', 'Каждый отец должен быть целым числом.');
                    return;
                }

                $exists = $this->catRegistryRepository->existsId($id);
                if (!$exists) {
                    throw new NotFoundHttpException('Запись не найдена');
                }
            }
        }
    }
}
