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

    }
}
