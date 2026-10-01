<?php

namespace CatRegistry\applications\components;

use yii\web\Response;
use Yii;

abstract class RestController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function response(array $data = []): array
    {
        $this->response->format = Response::FORMAT_JSON;

        return [
            'code' => 200,
            'data' => $data,
        ];
    }

    protected function getPayload(): array
    {
        return json_decode(Yii::$app->request->getRawBody(), true) ?? [];
    }
}
