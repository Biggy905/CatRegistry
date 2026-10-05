<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\enums\CatGenderEnums;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\NotFoundHttpException;

final class SearchCatForm extends AbstractForm
{
    /** @var ?mixed $exclude_cat_id */
    public $exclude_cat_id;
    /** @var ?mixed $name */
    public $name;
    /** @var ?mixed $gender */
    public $gender;

    public function rules(): array
    {
        return [
            [
                'exclude_cat_id',
                'integer',
            ],
            [
                ['exclude_cat_id', 'name', 'gender'],
                'required',
                'message' => 'Поле «{attribute}» обязательно для заполнения.',
            ],
            [
                'name',
                'string',
                'min' => 1,
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
        ];
    }
}
