<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\NotFoundHttpException;

final class IdCatForm extends AbstractForm
{
    /** @var ?mixed $id */
    public $id;

    public function rules(): array
    {
        return [
            [
                'id',
                'integer',
            ],
            [
                'id',
                'required',
            ],
        ];
    }
}
