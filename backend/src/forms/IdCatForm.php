<?php

namespace CatRegistry\applications\forms;

use CatRegistry\applications\components\AbstractForm;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use yii\web\NotFoundHttpException;

final class IdCatForm extends AbstractForm
{
    public $id;

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
                'id',
                'integer',
            ],
            [
                'id',
                'required',
            ],
            [
                'id',
                'validateId',
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
}
