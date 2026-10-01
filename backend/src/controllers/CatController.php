<?php

namespace CatRegistry\applications\controllers;

use CatRegistry\applications\components\RestController;
use CatRegistry\applications\exceptions\BadRequestHttpException;
use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\services\CatRegistryServices;

final class CatController extends RestController
{
    public function __construct(
        $id,
        $module,
        private readonly IdCatForm $idCatForm,
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
        $payload = $this->getPayload();
        $form = $this->idCatForm;
        if (!$form->runValidate($payload)) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->response(
            $this->services->item($form),
        );
    }

    public function actionList(): array
    {
        $payload = $this->getPayload();
        $form = $this->filterCatForm;
        if (!$form->runValidate($payload)) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->response(
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

        return $this->response(
            $this->services->insert($form),
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

        return $this->response(
            $this->services->update($form),
        );
    }

    public function actionDelete(int $id): array
    {
        $form = $this->idCatForm;
        if (!$form->runValidate(['id' => $id])) {
            throw new BadRequestHttpException(null, $form->getErrors());
        }

        return $this->response(
            $this->services->delete($form),
        );
    }
}
