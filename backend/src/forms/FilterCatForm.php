<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\enums\CatGenderEnums;

final class FilterCatForm extends AbstractForm
{
    public $page;
    public $limit;
    public $age;
    public $gender;

    public function rules(): array
    {
        return [
            [
                ['page', 'limit'],
                'integer',
            ],
            [
                'age',
                'validateAge',
            ],
            [
                'gender',
                'trim',
            ],
            [
                'gender',
                'in',
                'range' => CatGenderEnums::toArray(),
            ],
        ];
    }

    public function validateAge()
    {
        if ($this->age === null || $this->age === '') {
            return;
        }

        if (!is_array($this->age)) {
            $this->addError('age', 'Должны быть минимальные и максимальные числа.');
            return;
        }

        if (is_array($this->age)) {
            $count = count($this->age);
            if ($count !== 2) {
                $this->addError('age', 'Должны быть минимальные и максимальные числа.');
                return;
            }

            foreach ($this->age as $value) {
                if (!is_numeric($value) || (int) $value != $value) {
                    $this->addError('age', 'Должны быть минимальные и максимальные числа.');
                    return;
                }
            }
        }
    }
}
