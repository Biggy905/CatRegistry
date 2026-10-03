<?php

namespace CatRegistry\applications\controllers;

use CatRegistry\applications\components\RestController;
use CatRegistry\applications\exceptions\BadRequestHttpException;
use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\applications\forms\SearchCatForm;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\services\CatRegistryServices;
use yii\helpers\Url;
use Yii;

final class CatController extends RestController
{
    public function __construct(
        $id,
        $module,
        private readonly IdCatForm $idCatForm,
        private readonly SearchCatForm $searchCatForm,
        private readonly FilterCatForm $filterCatForm,
        private readonly CreateCatForm $createCatForm,
        private readonly UpdateCatForm $updateCatForm,
        private readonly CatRegistryServices $services,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionItem(int $id): array
    {
        $form = $this->idCatForm;
        if (!$form->runValidate(['id' => $id])) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->responseOK(
            $this->services->item($form),
        );
    }

    public function actionSearch(int $exclude_cat_id, string $name, string $gender): array
    {
        $form = $this->searchCatForm;
        if (!$form->runValidate(
            ['exclude_cat_id' => $exclude_cat_id,'name' => $name, 'gender' => $gender])
        ) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->responseOK(
            $this->services->search($form),
        );
    }

    public function actionList(): array
    {
        $form = $this->filterCatForm;
        if (!$form->runValidate(
            [
                'page' => Yii::$app->request->get('page'),
                'limit' => Yii::$app->request->get('limit'),
                'age' => Yii::$app->request->get('age'),
                'gender' => Yii::$app->request->get('gender'),
            ]
        )) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->responseOK(
            $this->services->list($form),
        );
    }

    public function actionCreate(): array
    {
        $payload = $this->getPayload();
        $form = $this->createCatForm;
        if (!$form->runValidate($payload)) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        $cat = $this->services->insert($form);

        return $this->responseCreated(
            $cat->toArray(),
            Url::to(['cat/item', 'id' => $cat->catRegistry->id]),
        );
    }

    public function actionUpdate(int $id): array
    {
        $payload = $this->getPayload();
        $form = $this->updateCatForm;
        if (!$form->runValidate(
            array_merge($payload, ['id' => $id])
        )) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->responseOK(
            $this->services->update($form)->toArray(),
        );
    }

    public function actionDelete(int $id): void
    {
        $form = $this->idCatForm;
        if (!$form->runValidate(['id' => $id])) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        $this->services->delete($form);

        $this->responseNoContent();
    }
}
