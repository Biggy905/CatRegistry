<?php

namespace CatRegistry\applications\controllers;

use CatRegistry\applications\components\RestController;

final class IndexController extends RestController
{
    public function actionIndex(): array
    {
        return $this->response(
            [
                'message' => 'Добро пожаловать!'
            ]
        );
    }
}
