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
use yii\web\Application as WebApplication;
use yii\console\Application as ConsoleApplication;
use Yii;
use yii\web\Request;

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

    /**
     * @param int $id
     * @return array<string, mixed>
     * @throws BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionItem(int $id): array
    {
        $form = $this->idCatForm;
        if (!$form->runValidate(['id' => $id])) {
            throw new BadRequestHttpException(data: $form->getDataErrors());
        }

        return $this->responseOK(
            $this->services->item($form),
        );
    }

    /**
     * @param int $exclude_cat_id
     * @param string $name
     * @param string $gender
     * @return array<string, mixed>
     * @throws BadRequestHttpException
     */
    public function actionSearch(int $exclude_cat_id, string $name, string $gender): array
    {
        $form = $this->searchCatForm;
        if (!$form->runValidate(
            ['exclude_cat_id' => $exclude_cat_id,'name' => $name, 'gender' => $gender])
        ) {
            throw new BadRequestHttpException(data: $form->getDataErrors());
        }

        return $this->responseOK(
            $this->services->search($form),
        );
    }

    /**
     * @return array<string, mixed>
     * @throws BadRequestHttpException
     */
    public function actionList(): array
    {
        /** @var ConsoleApplication|WebApplication $app */
        $app = Yii::$app;
        /** @var Request $request */
        $request = $app->request;

        $form = $this->filterCatForm;
        if (!$form->runValidate(
            [
                'page' => $request->get('page'),
                'limit' => $request->get('limit'),
                'age' => $request->get('age'),
                'gender' => $request->get('gender'),
            ]
        )) {
            throw new BadRequestHttpException(data: $form->getDataErrors());
        }

        return $this->responseOK(
            $this->services->list($form),
        );
    }

    /**
     * @return array<string, mixed>
     * @throws BadRequestHttpException
     * @throws \DateMalformedStringException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate(): array
    {
        $payload = $this->getPayload();
        $form = $this->createCatForm;
        if (!$form->runValidate($payload)) {
            throw new BadRequestHttpException(data: $form->getDataErrors());
        }

        $cat = $this->services->insert($form);

        return $this->responseCreated(
            $cat->toArray(),
            Url::to(['cat/item', 'id' => $cat->catRegistry->id]),
        );
    }

    /**
     * @param int $id
     * @return array<string, mixed>
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate(int $id): array
    {
        /** @var array<mixed> $payload */
        $payload = $this->getPayload();
        $form = $this->updateCatForm;
        if (!$form->runValidate(
            array_merge($payload, ['id' => $id])
        )) {
            throw new BadRequestHttpException(data: $form->getDataErrors());
        }

        return $this->responseOK(
            $this->services->update($form)->toArray(),
        );
    }

    /**
     * @param int $id
     * @return void
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete(int $id): void
    {
        $form = $this->idCatForm;
        if (!$form->runValidate(['id' => $id])) {
            throw new BadRequestHttpException(data: $form->getDataErrors());
        }

        $this->services->delete($form);

        $this->responseNoContent();
    }
}
