<?php

namespace CatRegistry\applications\controllers\console;

use yii\console\Controller;

final class WelcomeController extends Controller
{
    public function actionIndex()
    {
        echo "Добро пожаловать в CLI приложения\n";
    }
}
