<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\enums\CatGenderEnums;

final class FilterCatForm extends AbstractForm
{
    public $age;
    public $gender;

    public function rules(): array
    {
        return [
            [
                'age',
                'integer',
            ],
            [
                'gender',
                'trim',
            ],
            [
                'gender',
                'in',
                'range' => CatGenderEnums::cases(),
            ],
        ];
    }
}
