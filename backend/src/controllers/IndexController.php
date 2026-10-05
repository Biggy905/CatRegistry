<?php

namespace CatRegistry\applications\controllers;

use CatRegistry\applications\components\RestController;

final class IndexController extends RestController
{
    /**
     * @return array<mixed>
     */
    public function actionIndex(): array
    {
        return $this->responseOK(
            [
                'message' => 'Добро пожаловать!'
            ]
        );
    }
}
